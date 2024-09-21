<?php
declare(strict_types=1);

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

namespace Causal\Theodia\Xclass\V13\Backend\Form\FormDataProvider;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Special data provider for the sites configuration module.
 *
 * Fetch "row" data from yml file and set as 'databaseRow'
 */
#[Autoconfigure(public: true)]
readonly class SiteDatabaseEditRow implements FormDataProviderInterface
{
    public function __construct(private SiteConfiguration $siteConfiguration) {}

    /**
     * First level of ['customData']['siteData'] to ['databaseRow']
     *
     * @throws \RuntimeException
     */
    public function addData(array $result): array
    {
        if ($result['command'] !== 'edit' || !empty($result['databaseRow'])) {
            return $result;
        }

        $tableName = $result['tableName'];
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class, $this->siteConfiguration);
        if ($tableName === 'site') {
            $rootPageId = (int)$result['vanillaUid'];
            $rowData = $this->getRawConfigurationForSiteWithRootPageId($siteFinder, $rootPageId);
            $result['databaseRow']['uid'] = $rowData['rootPageId'];
            $result['databaseRow']['identifier'] = $result['customData']['siteIdentifier'];
        // XCLASS: Add site_theodia_calendar to the list of allowed tables:
        } elseif (in_array($tableName, ['site_errorhandling', 'site_language', 'site_route', 'site_base_variant', 'site_theodia_calendar'], true)) {
            $rootPageId = (int)($result['inlineTopMostParentUid'] ?? $result['inlineParentUid']);
            try {
                $rowData = $this->getRawConfigurationForSiteWithRootPageId($siteFinder, $rootPageId);
                $parentFieldName = $result['inlineParentFieldName'];
                if (!isset($rowData[$parentFieldName])) {
                    throw new \RuntimeException('Field "' . $parentFieldName . '" not found', 1520886092);
                }
                $rowData = $rowData[$parentFieldName][$result['vanillaUid']];
                $result['databaseRow']['uid'] = $result['vanillaUid'];
            } catch (SiteNotFoundException $e) {
                $rowData = [];
            }
        } else {
            throw new \RuntimeException('Other tables not implemented', 1520886234);
        }

        foreach ($rowData as $fieldName => $value) {
            // Flat values only - databaseRow has no "tree"
            if (!is_array($value)) {
                $result['databaseRow'][$fieldName] = $value;
            }
        }
        // All "records" are always on pid 0
        $result['databaseRow']['pid'] = 0;
        return $result;
    }

    protected function getRawConfigurationForSiteWithRootPageId(SiteFinder $siteFinder, int $rootPageId): array
    {
        $site = $siteFinder->getSiteByRootPageId($rootPageId);
        // load config as it is stored on disk (without replacements)
        $configuration = $this->siteConfiguration->load($site->getIdentifier());
        // @todo parse pseudo TCA and react on type==select and renderType==selectMultipleSideBySide
        if (is_array($configuration['dependencies'] ?? null)) {
            $configuration['dependencies'] = implode(',', $configuration['dependencies']);
        }
        return $configuration;
    }
}
