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

use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EventPreviewRenderer extends StandardContentPreviewRenderer
{
    protected $flexFormData;

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $out = [];
        $languageService = $this->getLanguageService();
        $labelPrefix = 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:plugins.event.';

        $pluginTitle = $languageService->sL($labelPrefix . 'title');
        $out[] = '<strong>' . htmlspecialchars($pluginTitle) . '</strong>';

        $this->flexFormData = GeneralUtility::xml2array($item->getRecord()['pi_flexform']);
        if (is_array($this->flexFormData)) {
            $out[] = '<table class="table table-sm mt-3 mb-0">';

            $errorPattern = '<span class="badge badge-danger">%s</span>';

            $label = $languageService->sL($labelPrefix . 'settings.calendars');
            $calendars = GeneralUtility::intExplode(',', $this->getFieldFromFlexForm('settings.calendars'), true);
            if (empty($calendars)) {
                $error = $languageService->sL($labelPrefix . 'settings.calendars.errorEmpty');
                $description = sprintf($errorPattern, htmlspecialchars($error));
            } else {
                $description = $this->getCalendarNames($item->getRecord()['pid'], $calendars);
            }
            $out[] = $this->addTableRow($label, $description);

            $label = $languageService->sL($labelPrefix . 'settings.numberOfEvents');
            $numberOfEvents = (int)$this->getFieldFromFlexForm('settings.numberOfEvents');
            $out[] = $this->addTableRow($label, (string)$numberOfEvents);

            $iframe = (bool)$this->getFieldFromFlexForm('settings.iframe');
            if ($iframe) {
                $label = $languageService->sL($labelPrefix . 'settings.iframe');
                $out[] = $this->addTableRow($label, $this->describeBoolean());
            } else {
                $label = $languageService->sL($labelPrefix . 'settings.showLocation');
                $showLocation = (bool)$this->getFieldFromFlexForm('settings.showLocation');
                $out[] = $this->addTableRow($label, $this->describeBoolean($showLocation));

                $filter = $this->getFieldFromFlexForm('settings.filter');
                if (!empty($filter)) {
                    $label = $languageService->sL($labelPrefix . 'settings.filter');
                    $out[] = $this->addTableRow($label, htmlspecialchars($filter));
                }
            }

            $out[] = '</table>';
        }

        return implode(LF, $out);
    }

    protected function getFieldFromFlexForm(string $key, string $sheet = 'sDEF'): ?string
    {
        $flexForm = $this->flexFormData;
        if (isset($flexForm['data'])) {
            $flexForm = $flexForm['data'];
            return $flexForm[$sheet]['lDEF'][$key]['vDEF'] ?? null;
        }

        return null;
    }

    protected function addTableRow(string $label, string $content): string
    {
        $out[] = '<tr>';
        $out[] = '<td class="align-top">' . htmlspecialchars($label) . '</td>';
        $out[] = '<td class="align-top" style="font-weight: bold">' . $content . '</td>';
        $out[] = '</tr>';

        return implode(LF, $out);
    }

    protected function describeBoolean(bool $value = true): string
    {
        $key = 'LLL:EXT:beuser/Resources/Private/Language/locallang.xlf:';
        $key .= $value ? 'yes' : 'no';

        return htmlspecialchars($this->getLanguageService()->sL($key));
    }

    protected function getCalendarNames(int $storage, array $calendars): string
    {
        $theodiaCalendars = \Causal\Theodia\Service\TheodiaOrg::getTheodiaCalendars($storage);

        $out = [];
        foreach ($calendars as $calendar) {
            $out[] = '- ' . htmlspecialchars($theodiaCalendars[$calendar] ?? $calendar);
        }

        return implode('<br>' . LF, $out);
    }
}
