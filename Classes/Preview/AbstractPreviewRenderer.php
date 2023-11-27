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

use TYPO3\CMS\Backend\Preview\PreviewRendererInterface;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

abstract class AbstractPreviewRenderer implements PreviewRendererInterface
{
    const PLUGIN_TITLE = 'plugins.unknown';

    public function renderPageModulePreviewHeader(GridColumnItem $item): string
    {
        return $item->getRecord()['header'];
    }

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $out = [];
        $out[] = '<strong>' . htmlspecialchars($this->sL(static::PLUGIN_TITLE)) . '</strong>';

        return implode(LF, $out);
    }

    public function renderPageModulePreviewFooter(GridColumnItem $item): string
    {
        $labels = $item->getContext()->getItemLabels();
        $record = $item->getRecord();
        $out = [];

        if (!empty($record['space_before_class'] ?? '')) {
            $out[] = $labels['space_before_class'] . ': ' . $record['space_before_class'];
        }
        if (!empty($record['space_after_class'] ?? '')) {
            $out[] = $labels['space_after_class'] . ': ' . $record['space_after_class'];
        }

        return implode('<br>' . LF, $out);
    }

    public function wrapPageModulePreview(string $previewHeader, string $previewContent, GridColumnItem $item): string
    {
        $out = [];
        $out[] = '<strong>' . htmlspecialchars($previewHeader) . '</strong>';
        $out[] = '<br>';
        $out[] = $previewContent;

        return implode(LF, $out);
    }

    protected function sL(string $key): string
    {
        $fullKey = 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:' . $key;
        return LocalizationUtility::translate($fullKey) ?: 'Unknown key: ' . $key;
    }
}
