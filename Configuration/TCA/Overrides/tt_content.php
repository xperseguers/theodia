<?php
defined('TYPO3') || die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin([
    'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:plugins.event.title',
    'theodia_event',
    'theodia-default',
], 'CType', 'theodia');

//$pluginSignature = 'theodia_event';
//$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,pages,recursive';
//$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:theodia/Configuration/FlexForms/flexform_event.xml',
    'theodia_event'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin([
    'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:plugins.place.title',
    'theodia_place',
    'theodia-default',
], 'CType', 'theodia');

//$pluginSignature = 'theodia_place';
//$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,pages,recursive';
//$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:theodia/Configuration/FlexForms/flexform_place.xml',
    'theodia_place'
);

/**
 * Configure a custom preview renderer for the plugins
 * @see https://docs.typo3.org/m/typo3/reference-coreapi/11.5/en-us/ApiOverview/ContentElements/CustomBackendPreview.html
 */
$GLOBALS['TCA']['tt_content']['types']['theodia_event']['previewRenderer']
    = \Causal\Theodia\Preview\EventPreviewRenderer::class;
$GLOBALS['TCA']['tt_content']['types']['theodia_place']['previewRenderer']
    = \Causal\Theodia\Preview\PlacePreviewRenderer::class;