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
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class EventController extends ActionController
{
    protected const CACHE_LIFETIME = 14400; /* 4 hours */

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

    public function listAction()
    {
        if ((bool)$this->settings['iframe']) {
            return $this->generateIframeSnippet();
        }
        $events = $this->service->getEventsByCalendars(
            GeneralUtility::intExplode(',', $this->settings['calendars']),
            100,
            static::CACHE_LIFETIME
        );

        if (!empty($this->settings['filter'])) {
            $filteredEvents = [];
            foreach ($events as $event) {
                if (preg_match($this->settings['filter'], $event['name'])) {
                    $filteredEvents[] = $event;
                }
            }
            $events = count($filteredEvents) > (int)$this->settings['numberOfEvents']
                ? array_slice($filteredEvents, 0, (int)$this->settings['numberOfEvents'])
                : $filteredEvents;
        } else {
            if (count($events) > (int)$this->settings['numberOfEvents']) {
                $events = array_slice($events, 0, (int)$this->settings['numberOfEvents']);
            }
        }

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

        // Mark the cache for this content element (well the whole page...) as valid for 4 hours
        // TODO: is there a trick to mark only this specific content element as to be cached for 4 hours?
        $frontendController = $this->getTypoScriptFrontendController();
        $cacheLifetime = $frontendController->page['cache_timeout'] ?: static::CACHE_LIFETIME;
        $frontendController->page['cache_timeout'] = min($cacheLifetime, static::CACHE_LIFETIME);

        $this->view->assignMultiple([
            'events' => $events,
            'eventsGroupedByDay' => $eventsGroupedByDay,
            // Raw data for the plugin
            'plugin' => $this->configurationManager->getContentObject()->data,
        ]);

        if ((new Typo3Version())->getMajorVersion() >= 11) {
            return $this->htmlResponse();
        }
    }

    /**
     * @return string
     */
    protected function generateIframeSnippet(): string
    {
        $baseUrl = 'https://theodia.org/widget/v1/events';
        $parameters = [
            'calendars' => $this->settings['calendars'],
            'dateFormat' => 'EEEE d MMMM yyyy',
            'language' => 'fr',
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
       return $html;
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}