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

class PlaceController extends ActionController
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
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_theodia_place');
        $place = $queryBuilder
            ->select('place.*', 'r.uid_local AS photo_file_uid')
            ->from('tx_theodia_place', 'place')
            ->leftJoin('place',
                'sys_file_reference',
                'r',
                $queryBuilder->expr()->eq('r.uid_foreign', $queryBuilder->quoteIdentifier('place.uid'))
            )
            ->where(
                $queryBuilder->expr()->eq('place.uid', $queryBuilder->createNamedParameter((int)$this->settings['place'], \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('r.tablenames', $queryBuilder->quote('tx_theodia_place')),
                $queryBuilder->expr()->eq('r.fieldname', $queryBuilder->quote('photo'))
            )
            ->execute()
            ->fetchAssociative();

        $this->view->assignMultiple([
            'place' => $place,
            'jsonLd' => json_encode($this->getJsonLdLocation($place)),
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

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}