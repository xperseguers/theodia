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

use Causal\Theodia\Service\Theodia;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class EventController extends ActionController
{
    protected const CACHE_LIFETIME = 14400; /* 4 hours */

    /**
     * @var Theodia
     */
    protected $service;

    /**
     * TheodiaController constructor.
     *
     * @param Theodia $service
     */
    public function __construct(Theodia $service)
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
            'jsonLd' => json_encode($this->getJsonLdEvents($events)),
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
        $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $cssLink = $contentObject->typoLink_URL([
            'parameter.' => [
                'data' => 'path:EXT:up_sainte_claire/Resources/Public/css/theodia.css'
            ],
            'forceAbsoluteUrl' => 1,
        ]);

        $baseUrl = 'https://theodia.org/widget/v1/events';
        $parameters = [
            'calendars' => $this->settings['calendars'],
            'css' => $cssLink,
            'dateFormat' => 'EEEE d MMMM yyyy',
            'language' => 'fr',
            'quantity' => (int)$this->settings['numberOfEvents'],
            'showMore' => 'false',
            'showPlace' => 'false',
            'timeFormat' => 'HH:mm'
        ];
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
     * @param array $events
     * @return array
     */
    protected function getJsonLdEvents(array $events): array
    {
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        $baseUrl = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');

        $jsonEvents = [];
        foreach ($events as $event) {
            $data = [
                '@context' => 'http://schema.org',
                '@type' => 'Event',
                'name' => $event['name'],
                'description' => ($event['description'] ?? '') ?: 'Rite romain ordinaire',
                'startDate' => $event['start']->format('Y-m-d\TH:i'),
                'endDate' => $event['end']->format('Y-m-d\TH:i'),
                'location' => $this->getJsonLdLocation($event['place']),
                'eventStatus' => $event['cancelled']
                    ? 'http://schema.org/EventCancelled'
                    : 'http://schema.org/EventScheduled',
                'organizer' => $this->getJsonLdOrganizer($event),
                'eventAttendanceMode' => 'http://schema.org/OfflineEventAttendanceMode',
            ];

            if (!empty($event['place']['page_uid'])) {
                $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
                $data['url'] = $contentObject->typoLink_URL([
                    'parameter' => $event['place']['page_uid'],
                    'forceAbsoluteUrl' => 1,
                ]);
            }

            $imageUid = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable('sys_file_reference')
                ->select(
                    ['uid_local'],
                    'sys_file_reference',
                    [
                        'tablenames' => 'tx_theodia_place',
                        'fieldname' => 'photo',
                        'uid_foreign' => $event['place']['uid'],
                    ]
                )
                ->fetchOne();
            if (!empty($imageUid)) {
                $file = $fileRepository->findByUid($imageUid);
                if ($file !== null) {
                    $data['image'] = $baseUrl . $file->getPublicUrl();
                }
            }

            $jsonEvents[] = $data;
        }

        return $jsonEvents;
    }

    /**
     * @param array $place
     * @return array
     */
    protected function getJsonLdLocation(array $place): array
    {
        $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);

        $data = [
            '@context' => 'http://schema.org',
            '@type' => 'CatholicChurch',
            'name' => $place['name'],
            'address' => [
                '@context' => 'http://schema.org',
                '@type' => 'PostalAddress',
                'addressCountry' => $place['country'],
                'addressRegion' => $place['region'],
                'addressLocality' => $place['city'],
                'postalCode' => $place['postal_code'],
                'streetAddress' => $place['address'],
            ],
        ];

        if (!empty($place['latitude'])) {
            $data['geo'] = [
                '@context' => 'http://schema.org',
                '@type' => 'GeoCoordinates',
                'latitude' => $place['latitude'],
                'longitude' => $place['longitude'],
            ];
        }

        if (!empty($place['seats'])) {
            $data['maximumAttendeeCapacity'] = $place['seats'];
        }

        if (!empty($place['page_uid'])) {
            $data['url'] = $contentObject->typoLink_URL([
                'parameter' => $place['page_uid'],
                'forceAbsoluteUrl' => 1,
            ]);
        }

        if (!empty($place['photo_file_uid'])) {
            $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
            $imageFile = $fileRepository->findByUid($place['photo_file_uid']);
            if ($imageFile !== null) {
                $baseUrl = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
                $image = $contentObject->getImgResource($imageFile, [
                    'maxW' => '600',
                    'maxH' => '600',
                ])[3];
                $data['photo'] = [
                    '@context' => 'http://schema.org',
                    '@type' => 'Photograph',
                    'image' => $baseUrl . $image,
                ];
            }
        }

        return $data;
    }

    protected function getJsonLdOrganizer(array $event): array
    {
        $data = [
            '@context' => 'http://schema.org',
            '@type' => 'Organization',
            'name' => 'Unité pastorale Sainte-Claire',
            'url' => 'https://www.paroisse.ch',
        ];

        return $data;
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}