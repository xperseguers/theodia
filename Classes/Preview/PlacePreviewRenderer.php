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

namespace Causal\Theodia\Preview;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PlacePreviewRenderer extends AbstractFlexFormPreviewRenderer
{
    const PLUGIN_NAME = 'Place';

    protected function renderFlexFormPreviewContent(array $record, array &$out): void
    {
        $languageService = $this->getLanguageService();

        $label = $languageService->sL($this->labelPrefix . 'settings.place');
        $placeId = (int)$this->getFieldFromFlexForm('settings.place');
        $suffixLabel = '';
        if (empty($placeId)) {
            // Dynamically find the place pointing to this page
            $placeId = (int)GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable('tx_theodia_place')
                ->select(
                    ['uid'],
                    'tx_theodia_place',
                    [
                        'page_uid' => $record['pid'] ?? -1,
                    ]
                )
                ->fetchOne();
            $suffixLabel = ' ' . $languageService->sL($this->labelPrefix . 'settings.place.dynamic');
        }
        $place = $this->getPlaceName($placeId);
        if (empty($place)) {
            $error = $languageService->sL($this->labelPrefix . 'settings.place.errorEmpty');
            $description = $this->showError(htmlspecialchars($error));
        } else {
            $description = htmlspecialchars($place . $suffixLabel);
        }
        $out[] = $this->addTableRow($label, $description);
    }

    protected function getPlaceName(int $placeUid): string
    {
        $row = BackendUtility::getRecord('tx_theodia_place', $placeUid);
        return $row['name'] ?? '';
    }
}
