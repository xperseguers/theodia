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
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class PlaceController extends ActionController
{
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

    public function showAction(): ResponseInterface
    {
        // Raw data for the plugin
        $contentObjectData = $this->getContentObject()->data;
        $this->view->assign('plugin', $contentObjectData);

        $placeId = (int)($this->settings['place'] ?? 0);
        if (empty($placeId)) {
            // Dynamically find the place pointing to this page
            $pageId = $contentObjectData['pid'];
            $placeId = (int)GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable('tx_theodia_place')
                ->select(
                    ['uid'],
                    'tx_theodia_place',
                    [
                        'page_uid' => $pageId,
                    ]
                )
                ->fetchOne();
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_theodia_place');
        $place = $queryBuilder
            ->select('*')
            ->from('tx_theodia_place')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($placeId, Connection::PARAM_INT))
            )
            ->executeQuery()
            ->fetchAssociative();

        if (!empty($place)) {
            unset($place['photo']);
            $place['photos'] = [];
            $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);

            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('sys_file_reference');
            $references = $queryBuilder
                ->select('*')
                ->from('sys_file_reference')
                ->where(
                    $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($place['uid'], Connection::PARAM_INT)),
                    $queryBuilder->expr()->eq('tablenames', $queryBuilder->quote('tx_theodia_place')),
                    $queryBuilder->expr()->eq('fieldname', $queryBuilder->quote('photo'))
                )
                ->orderBy('sorting_foreign')
                ->executeQuery()
                ->fetchAllAssociative();

            foreach ($references as $reference) {
                $fileReference = $resourceFactory->getFileReferenceObject($reference['uid'], $reference);
                if ($fileReference !== null) {
                    $place['photos'][] = $fileReference;
                }
            }

            $this->view->assignMultiple([
                'place' => $place,
            ]);
        }

        return $this->htmlResponse();
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
}