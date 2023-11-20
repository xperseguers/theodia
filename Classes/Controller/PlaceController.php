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

    public function showAction()
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_theodia_place');
        $place = $queryBuilder
            ->select('*')
            ->from('tx_theodia_place')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter((int)($this->settings['place'] ?? 0), \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchAssociative();

        if (!empty($place)) {
            $place['photo_file_uid'] = (int)$queryBuilder
                ->select('uid_local')
                ->from('sys_file_reference')
                ->where(
                    $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($place['uid'], \PDO::PARAM_INT)),
                    $queryBuilder->expr()->eq('tablenames', $queryBuilder->quote('tx_theodia_place')),
                    $queryBuilder->expr()->eq('fieldname', $queryBuilder->quote('photo'))
                )
                ->execute()
                ->fetchOne();

            $this->view->assignMultiple([
                'place' => $place,
            ]);
        }

        if ((new Typo3Version())->getMajorVersion() >= 11) {
            return $this->htmlResponse();
        }
    }
}