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

namespace Causal\Theodia\ViewHelpers;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class JsonLdViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    protected $escapeOutput = false;

    public function initializeArguments()
    {
        $this->registerArgument('events', 'array', 'Events to render', false);
        $this->registerArgument('place', 'array', 'Place to render', false);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $jsonObjects = [];
        if (!empty($arguments['events'])) {
            $jsonObjects = static::getJsonLdEvents($arguments['events']);
        }
        if (!empty($arguments['place'])) {
            $jsonObjects[] = static::getJsonLdLocation($arguments['place']);
        }

        if (count($jsonObjects) === 1) {
            $jsonObjects = $jsonObjects[0];
        };

        $out = [];
        if (!empty($jsonObjects)) {
            $out[] = '<script type="application/ld+json">';
            $out[] = json_encode($jsonObjects);
            $out[] = '</script>';
        }

        return implode(LF, $out);
    }

    /**
     * @param array $events
     * @return array
     */
    protected static function getJsonLdEvents(array $events): array
    {
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        $baseUrl = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');

        $jsonEvents = [];
        foreach ($events as $event) {
            $startDate = $event['start'];
            $endDate = $event['end'] ?? (clone $startDate)->modify('+1 hour');
            $data = [
                '@context' => 'http://schema.org',
                '@type' => 'Event',
                'name' => $event['name'],
                'description' => ($event['description'] ?? '') ?: 'Rite romain ordinaire',
                'startDate' => $startDate->format('Y-m-d\TH:i'),
                'endDate' => $endDate->format('Y-m-d\TH:i'),
                'location' => static::getJsonLdLocation($event['place']),
                'eventStatus' => $event['cancelled']
                    ? 'http://schema.org/EventCancelled'
                    : 'http://schema.org/EventScheduled',
                'organizer' => static::getJsonLdOrganizer($event),
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
    protected static function getJsonLdLocation(array $place): array
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

    protected static function getJsonLdOrganizer(array $event): array
    {
        $rootUrl = (string)$GLOBALS['TYPO3_REQUEST']->getAttribute('site')->getBase();
        if (strpos($rootUrl, '://') === false) {
            $rootUrl = $GLOBALS['TYPO3_REQUEST']->getAttribute('normalizedParams')->getRequestHost();
        }

        $data = [
            '@context' => 'http://schema.org',
            '@type' => 'Organization',
            'name' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'],
            'url' => $rootUrl,
        ];

        return $data;
    }
}