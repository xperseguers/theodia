<?php
declare(strict_types = 1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Causal\Theodia\Controller;

use Causal\Theodia\Service\TheodiaOrg;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class EventController extends ActionController
{
    protected const CACHE_LIFETIME = 14400; /* 4 hours */
    protected const MAX_EVENTS = 1000;

    /**
     * @var TheodiaOrg
     */
    protected $service;

    /**
     * TheodiaController constructor.
     *
     * @param TheodiaOrg $service
     */
    public function __construct(TheodiaOrg $service)
    {
        $this->service = $service;
    }

    public function listAction(): ResponseInterface
    {
        if ((bool)$this->settings['iframe']) {
            return $this->generateIframeSnippet();
        }
        $events = $this->service->getEventsByCalendars(
            GeneralUtility::intExplode(',', $this->settings['calendars']),
            static::MAX_EVENTS,
            static::CACHE_LIFETIME
        );

        // Keep only future events
        $now = new \DateTime();
        $events = array_filter($events, static function ($event) use ($now) {
            return $event['start'] >= $now || (($event['end'] ?? null) instanceof \DateTime && $event['end'] >= $now);
        });

        if (!empty($this->settings['filter'])) {
            $filteredEvents = [];
            foreach ($events as $event) {
                if (preg_match($this->settings['filter'], $event['name'])) {
                    $filteredEvents[] = $event;
                }
            }
            $events = $filteredEvents;
        }

        $limitNumberOfEvents = (int)$this->settings['numberOfEvents'];
        $isPartial = count($events) > $limitNumberOfEvents;
        if ($isPartial) {
            $events = array_slice($events, 0, $limitNumberOfEvents);
        }
        $eventsGroupedByDay = $this->groupEventsByDay($events);

        $nextStart = null;
        foreach ($events as $event) {
            if ($event['start'] > $now) {
                $nextStart = $event['start'];
                break;
            }
        }

        if ($nextStart !== null) {
            $cacheLifetime = min(60, $nextStart->diff($now)->s);
        } else {
            $cacheLifetime = static::CACHE_LIFETIME;
        }
        // Mark the cache for this content element (well the whole page...)
        // TODO: is there a trick to mark only this specific content element as to be cached for a given amount of time?
        $frontendController = $this->getTypoScriptFrontendController();
        $frontendController->page['cache_timeout'] = min($cacheLifetime, static::CACHE_LIFETIME);

        $this->view->assignMultiple([
            'events' => $events,
            'eventsGroupedByDay' => $eventsGroupedByDay,
            'isPartial' => true, //$isPartial,
            'numberEvents' => count($events),
            // Raw data for the plugin
            'plugin' => $this->getContentObject()->data,
        ]);

        return $this->htmlResponse();
    }

    public function showMoreAction(): ResponseInterface
    {
        $pluginUid = (int)($this->request->getParsedBody()['plugin'] ?? 0);
        $offset = (int)($this->request->getParsedBody()['offset'] ?? 0);

        $flexForm = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content')
            ->select(
                ['pi_flexform'],
                'tt_content',
                ['uid' => $pluginUid]
            )
            ->fetchOne();

        $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
        $flexFormSettings = $flexFormService->convertFlexFormContentToArray($flexForm ?? '')['settings'] ?? [];
        ArrayUtility::mergeRecursiveWithOverrule($this->settings, $flexFormSettings);

        $calendars = GeneralUtility::intExplode(',', $this->settings['calendars'] ?? '', true);
        if (empty($calendars)) {
            // Early return
            return new JsonResponse([], 400);
        }

        $events = $this->service->getEventsByCalendars(
            $calendars,
            static::MAX_EVENTS,
            static::CACHE_LIFETIME
        );

        $limitNumberOfEvents = $offset + 5;   // Arbitrary number of additional events to show
        $isPartial = count($events) > $limitNumberOfEvents;
        if ($isPartial) {
            // TODO: It would possibly be interesting to get a complete "another day"
            // when grouping by day (below)
            $events = array_slice($events, 0, $limitNumberOfEvents);
        }
        $eventsGroupedByDay = $this->groupEventsByDay($events);

        $this->view->assignMultiple([
            'settings' => $this->settings,
            'events' => $events,
            'eventsGroupedByDay' => $eventsGroupedByDay,
        ]);
        $html = $this->view->render();

        return new JsonResponse([
            'hasMore' => $isPartial,
            'numberEvents' => count($events),
            'html' => trim($html),
        ]);
    }

    protected function groupEventsByDay(array $events): array
    {
        $eventsGroupedByDay = [];
        foreach ($events as $event) {
            $day = $event['start']->format('Y-m-d');
            if (!isset($eventsGroupedByDay[$day])) {
                $eventsGroupedByDay[$day] = [
                    'date' => new \DateTime($event['start']->format('Y-m-d')),
                    'events' => [],
                ];
            }
            $eventsGroupedByDay[$day]['events'][] = $event;
        }

        return $eventsGroupedByDay;
    }

    /**
     * @return string
     */
    protected function generateIframeSnippet(): ResponseInterface
    {
        /** @var SiteLanguage $siteLanguage */
        $siteLanguage = $this->request->getAttribute('language');
        $typo3Version = (new Typo3Version())->getMajorVersion();
        if ($typo3Version >= 12) {
            $languageCode = $siteLanguage->getLocale()->getLanguageCode();
        } else {
            $languageCode = $siteLanguage->getTwoLetterIsoCode();
        }

        $baseUrl = 'https://theodia.org/widget/v1/events';
        $parameters = [
            'calendars' => $this->settings['calendars'],
            'dateFormat' => 'EEEE d MMMM yyyy',
            'language' => $languageCode,
            'quantity' => (int)$this->settings['numberOfEvents'],
            'showMore' => 'false',
            'showPlace' => 'false',
            'timeFormat' => 'HH:mm'
        ];
        if (!empty($this->settings['cssIframe'])) {
            $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $parameters['css'] = $contentObject->typoLink_URL([
                'parameter.' => [
                    'data' => 'path:' . trim($this->settings['cssIframe']),
                ],
                'forceAbsoluteUrl' => 1,
            ]);
        }
        $iframeSrc =  $baseUrl . '?' . str_replace('&', '&amp;', http_build_query($parameters));

        $html = <<<HTML
<iframe title="Horaire des messes theodia" src="$iframeSrc" style="width:100%;height:auto;padding:0;border:0;"></iframe>
<script>
   (function() {
       var d = document, s = d.createElement('script');
       s.src = "https://theodia.org/widget/v1/embed.js";
       s.setAttribute('data-timestamp', +new Date());
       (d.head || d.body).appendChild(s);
   })();
</script>
HTML;
       return $this->htmlResponse($html);
    }

    protected function getContentObject(): ContentObjectRenderer
    {
        $typo3Version = (new Typo3Version())->getMajorVersion();
        if ($typo3Version >= 12) {
            return $this->request->getAttribute('currentContentObject');
        } else {
            return $this->configurationManager->getContentObject();
        }
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
