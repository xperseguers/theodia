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

namespace Causal\Theodia\Updates;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

class SiteConfigurationUpdater implements UpgradeWizardInterface
{

    public function getIdentifier(): string
    {
        return 'TxTheodiaSiteConfiguration';
    }

    public function getTitle(): string
    {
        return 'EXT:theodia: Migrate site configuration';
    }

    public function getDescription(): string
    {
        return 'Migrates the available theodia calendars in your site configuration files.';
    }

    public function executeUpdate(): bool
    {
        $outdatedSiteIdentifiers = $this->findSiteConfigurationToMigrate();

        foreach ($outdatedSiteIdentifiers as $siteIdentifier) {
            $this->migrateSiteConfiguration($siteIdentifier);
        }

        return true;
    }

    public function updateNecessary(): bool
    {
        return !empty($this->findSiteConfigurationToMigrate());
    }

    public function getPrerequisites(): array
    {
        return [];
    }

    protected function findSiteConfigurationToMigrate(): array
    {
        $outdatedSiteConfigurations = [];

        foreach (GeneralUtility::makeInstance(SiteFinder::class)->getAllSites() as $site) {
            $calendarConfiguration = $site->getConfiguration()['tx_theodia_calendars'] ?? null;
            if (is_string($calendarConfiguration) && !empty($calendarConfiguration)) {
                $outdatedSiteConfigurations[] = $site->getIdentifier();
            }
        }

        return $outdatedSiteConfigurations;
    }

    protected function migrateSiteConfiguration(string $siteIdentifier): void
    {
        $configPath = Environment::getConfigPath() . '/sites';
        $configurationFile = $configPath . '/' . $siteIdentifier . '/config.yaml';
        $configuration = file_get_contents($configurationFile);

        $newConfiguration = preg_replace_callback(
            '/^tx_theodia_calendars:(\\s*["\'].*["\']\\s*)$/m',
            function ($match) {
                $mapping = str_replace(['\r', '\n'], ['', LF], substr(trim($match[1]), 1, -1));
                $calendarsMapping = GeneralUtility::trimExplode(LF, $mapping, true);
                $buffer = [];
                foreach ($calendarsMapping as $calendarMapping) {
                    if (!str_contains($calendarMapping, ',')) {
                        // Invalid configuration
                        continue;
                    }
                    [$id, $title] = GeneralUtility::trimExplode(',', $calendarMapping, true, 2);
                    $id = (int)$id;
                    $title = str_replace("'", "''", $title);
                    $buffer[] = '  -';
                    $buffer[] = "    id: '$id'";
                    $buffer[] = "    name: '$title'";
                }
                $out = 'tx_theodia_calendars:';
                if (!empty($buffer)) {
                    $out .= LF . implode(LF, $buffer);
                }
                return $out;
            },
            $configuration
        );

        GeneralUtility::writeFile($configurationFile, $newConfiguration);
    }
}
