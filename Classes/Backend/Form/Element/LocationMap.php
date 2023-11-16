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

namespace Causal\Theodia\Backend\Form\Element;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LocationMap extends \TYPO3\CMS\Backend\Form\Element\AbstractFormElement
{

    public function render()
    {
        $resultArray = $this->initializeResultArray();
        return $this->renderMap($resultArray);
    }

    protected function renderMap(array $resultArray): array
    {
        /** @var PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addRequireJsConfiguration(['paths' => [
            'TYPO3/CMS/Theodia/Leaflet' => rtrim($this->getRelativeFilePath(
                'EXT:theodia/Resources/Public/JavaScript/leaflet.js'
            ), '.js/')
        ]]);

        $row = $this->data['databaseRow'];
        $parameters = $this->data['parameterArray']['fieldConf']['config']['parameters'];
        $fieldLatitude = $parameters['latitude'];
        $fieldLongitude = $parameters['longitude'];
        $sourceAddressFields = [];
        if (!empty($parameters['street'])) {
            $sourceAddressFields[] = $parameters['street'];
        }
        if (!empty($parameters['zip'])) {
            $sourceAddressFields[] = $parameters['zip'];
        }
        if (!empty($parameters['city'])) {
            $sourceAddressFields[] = $parameters['city'];
        }
        if (!empty($parameters['country'])) {
            $sourceAddressFields[] = $parameters['country'];
        }

        $latitude = (float)$row[$fieldLatitude];
        $longitude = (float)$row[$fieldLongitude];
        if (abs($latitude) < 0.001 && abs($longitude) < 0.001) {
            // Default to Marly
            $latitude = 46.77657;
            $longitude = 7.16079;
        }
        $resultArray['stylesheetFiles'][] = rtrim($this->getRelativeFilePath(
            'EXT:theodia/Resources/Public/css/leaflet.css'
        ), '/');
        $resultArray['requireJsModules']['locationMap'] = [
            'TYPO3/CMS/Theodia/FormEngine/Element/LocationMap' => 'function(LocationMap) {
                new LocationMap({
                    uid: \'' . $row['uid'] . '\',
                    table: \'' . $this->data['tableName'] . '\',
                    fieldLatitude: \'' . $fieldLatitude . '\',
                    fieldLongitude: \'' . $fieldLongitude . '\',
                    latitude: \'' . $latitude . '\',
                    longitude: \'' . $longitude . '\',
                    addressFields: [' .
                        (!empty($sourceAddressFields)
                        ? '\'' . implode('\',\'', $sourceAddressFields) . '\''
                        : '') . ']
                });
            }'
        ];

        $addressLabel = htmlspecialchars($this->translate('tx_theodia_place.map.address'));
        $showLabel = htmlspecialchars($this->translate('tx_theodia_place.map.show'));

        $buttonAddress = '';
        if (!empty($sourceAddressFields)) {
            $buttonAddress = '<button id="fetch-address" class="btn btn-default">↩</button>';
        }

        $resultArray['html'] = <<<HTML
            <table style="width: 100%; margin-bottom: 1em;">
                <tr>
                    <td>
                        <label for="map-address" style="font-size:80%">$addressLabel</label>
                        <input type="text" id="map-address" class="form-control" autocomplete="off"/>
                    </td>
                    <td style="vertical-align: bottom; padding-left: 1em;">
                        <button id="geocode" class="btn btn-primary">$showLabel</button>
                        $buttonAddress
                    </td>
                </tr>
            </table>
            <div id="map" style="height: 300px; width: 100%;"></div>
HTML;
        return $resultArray;
    }

    protected function getRelativeFilePath(string $filePath): string
    {
        return \TYPO3\CMS\Core\Utility\PathUtility::getRelativePath(
            Environment::getPublicPath() . '/typo3/',
            GeneralUtility::getFileAbsFileName($filePath)
        );
    }

    protected function translate(string $key): string
    {
        $label = $GLOBALS['LANG']->sL('LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:' . $key);
        return $label ?: $key;
    }

}