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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

class PluginsUpdater implements UpgradeWizardInterface
{
    public function getIdentifier(): string
    {
        return 'TxTheodiaPlugins';
    }

    public function getTitle(): string
    {
        return 'EXT:theodia: Migrate plugins';
    }

    public function getDescription(): string
    {
        return 'Migrates the theodia plugins to a dedicated CType.';
    }

    public function executeUpdate(): bool
    {
        $queryBuilder = $this->getQueryBuilder();
        $tableConnection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content');

        $rows = $queryBuilder
            ->select('*')
            ->execute()
            ->fetchAllAssociative();

        foreach ($rows as $row) {
            $tableConnection->update(
                'tt_content',
                [
                    'CType' => $row['list_type'],
                    'list_type' => '',
                ],
                [
                    'uid' => $row['uid'],
                ]
            );
        }

        return true;
    }

    public function updateNecessary(): bool
    {
        $queryBuilder = $this->getQueryBuilder();

        return $queryBuilder
            ->count('*')
            ->execute()
            ->fetchOne() > 0;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(new DeletedRestriction());

        $queryBuilder
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('CType', $queryBuilder->quote('list')),
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('list_type', $queryBuilder->quote('theodia_event')),
                    $queryBuilder->expr()->eq('list_type', $queryBuilder->quote('theodia_place'))
                )
            );

        return $queryBuilder;
    }
}
