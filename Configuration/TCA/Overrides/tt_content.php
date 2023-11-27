<?php
defined('TYPO3') || die();

// TODO: Migrate to ExtensionUtility::addPlugin() to have a custom CType instead of a "list_type"
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'theodia',
    'Event',
    'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:plugins.event.title'
);

$pluginSignature = 'theodia_event';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:theodia/Configuration/FlexForms/flexform_event.xml');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'theodia',
    'Place',
    'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:plugins.place.title'
);

$pluginSignature = 'theodia_place';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:theodia/Configuration/FlexForms/flexform_place.xml');

/**
 * Configure a custom preview renderer for the plugins
 * @see https://docs.typo3.org/m/typo3/reference-coreapi/11.5/en-us/ApiOverview/ContentElements/CustomBackendPreview.html
 */
$GLOBALS['TCA']['tt_content']['types']['list']['previewRenderer']['theodia_event']
    = \Causal\Theodia\Preview\EventPreviewRenderer::class;
$GLOBALS['TCA']['tt_content']['types']['list']['previewRenderer']['theodia_place']
    = \Causal\Theodia\Preview\PlacePreviewRenderer::class;