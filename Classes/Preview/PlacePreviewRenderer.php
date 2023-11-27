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

class PlacePreviewRenderer extends AbstractFlexFormPreviewRendereer
{
    const PLUGIN_NAME = 'Place';

    protected function renderFlexFormPreviewContent(array $record, array &$out): void
    {
        $languageService = $this->getLanguageService();

        $label = $languageService->sL($this->labelPrefix . 'settings.place');
        $place = $this->getPlaceName((int)$this->getFieldFromFlexForm('settings.place'));
        if (empty($place)) {
            $error = $languageService->sL($this->labelPrefix . 'settings.place.errorEmpty');
            $description = $this->showError(htmlspecialchars($error));
        } else {
            $description = htmlspecialchars($place);
        }
        $out[] = $this->addTableRow($label, $description);
    }

    protected function getPlaceName(int $placeUid): string
    {
        $row = BackendUtility::getRecord('tx_theodia_place', $placeUid);
        return $row['name'] ?? '';
    }
}
