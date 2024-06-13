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

use Causal\Theodia\Service\TheodiaOrg;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EventPreviewRenderer extends AbstractFlexFormPreviewRenderer
{
    const PLUGIN_NAME = 'Event';

    protected function renderFlexFormPreviewContent(array $record, array &$out): void
    {
        $languageService = $this->getLanguageService();

        $label = $languageService->sL($this->labelPrefix . 'settings.calendars');
        $calendars = GeneralUtility::intExplode(',', $this->getFieldFromFlexForm('settings.calendars'), true);
        if (empty($calendars)) {
            $error = $languageService->sL($this->labelPrefix . 'settings.calendars.errorEmpty');
            $description = $this->showError(htmlspecialchars($error));
        } else {
            $description = $this->getCalendarNames($record['pid'], $calendars);
        }
        $out[] = $this->addTableRow($label, $description);

        $label = $languageService->sL($this->labelPrefix . 'settings.numberOfEvents');
        $numberOfEvents = (int)$this->getFieldFromFlexForm('settings.numberOfEvents');
        $out[] = $this->addTableRow($label, (string)$numberOfEvents);

        $iframe = (bool)$this->getFieldFromFlexForm('settings.iframe');
        if ($iframe) {
            $label = $languageService->sL($this->labelPrefix . 'settings.iframe');
            $out[] = $this->addTableRow($label, $this->describeBoolean());
        } else {
            $label = $languageService->sL($this->labelPrefix . 'settings.showLocation');
            $showLocation = (bool)$this->getFieldFromFlexForm('settings.showLocation');
            $out[] = $this->addTableRow($label, $this->describeBoolean($showLocation));

            $filter = $this->getFieldFromFlexForm('settings.filter');
            if (!empty($filter)) {
                $label = $languageService->sL($this->labelPrefix . 'settings.filter');
                $out[] = $this->addTableRow($label, htmlspecialchars($filter));
            }
        }
    }

    protected function getCalendarNames(int $storage, array $calendars): string
    {
        $theodiaCalendars = TheodiaOrg::getTheodiaCalendars($storage);

        $out = [];
        foreach ($calendars as $calendar) {
            $out[] = htmlspecialchars((string)($theodiaCalendars[$calendar] ?? $calendar));
        }

        // No need to show too many calendars as it clutters the preview
        $maxCalendars = 5;
        $numberOfCalendars = count($out);
        if ($numberOfCalendars > $maxCalendars) {
            // Ensure we hide at least 2 calendars so that label is always in plural form
            if ($numberOfCalendars - $maxCalendars < 2) {
                $maxCalendars--;
            }
            $out = array_slice($out, 0, $maxCalendars);
            $moreLabelPattern = $this->getLanguageService()->sL($this->labelPrefix . 'settings.calendars.more');
            $out[] = '... <small><em>' . sprintf($moreLabelPattern, $numberOfCalendars - $maxCalendars) . '</em></small>';
        }

        $output = '';
        if (count($out) > 1) {
            $output .= '- ';
        }
        $output .= implode('<br>' . LF . '- ', $out);

        return $output;
    }
}
