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
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractFlexFormPreviewRenderer extends StandardContentPreviewRenderer
{
    // Self-referential 'abstract' declaration
    const PLUGIN_NAME = self::PLUGIN_NAME;

    protected int $typo3Version;

    /** @var array|\ArrayAccess */
    protected $flexFormData;

    protected $labelPrefix;

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $out = [];
        $languageService = $this->getLanguageService();
        $this->labelPrefix = 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:plugins.' . strtolower(static::PLUGIN_NAME) . '.';

        $pluginTitle = $languageService->sL($this->labelPrefix . 'title');
        $out[] = '<strong>' . htmlspecialchars($pluginTitle) . '</strong>';

        $this->typo3Version = (new Typo3Version())->getMajorVersion();
        if ($this->typo3Version >= 14) {
            $record = $item->getRecord()->toArray();
            $this->flexFormData = $record['pi_flexform'];
        } else {
            $record = $item->getRecord();
            $this->flexFormData = GeneralUtility::xml2array($record['pi_flexform']);
        }

        if (is_array($this->flexFormData) || $this->flexFormData instanceof \ArrayAccess) {
            $out[] = '<table class="table table-sm mt-3 mb-0">';
            $this->renderFlexFormPreviewContent($record, $out);
            $out[] = '</table>';
        }

        return implode(LF, $out);
    }

    protected abstract function renderFlexFormPreviewContent(array $record, array &$out): void;

    protected function showError(string $text): string
    {
        $errorPattern = '<span class="badge badge-danger">%s</span>';
        return sprintf($errorPattern, $text);
    }

    protected function getFieldFromFlexForm(string $key, string $sheet = 'sDEF'): ?string
    {
        if ($this->typo3Version >= 14) {
            $sheets = $this->flexFormData->getSheets();
            $keyParts = explode('.', $key);
            $value = null;
            if (isset($sheets[$sheet])) {
                $current = $sheets[$sheet];
                foreach ($keyParts as $part) {
                    if (isset($current[$part])) {
                        $current = $current[$part];
                    } else {
                        return null;
                    }
                }
                return is_array($current) ? implode(',', $current) : (string)$current;
            }
        } else {
            $flexForm = $this->flexFormData;
            if (isset($flexForm['data'])) {
                $flexForm = $flexForm['data'];
                return $flexForm[$sheet]['lDEF'][$key]['vDEF'] ?? null;
            }
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
}
