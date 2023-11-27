<?php
defined('TYPO3') || die();

$plugins = [
    'theodia_event' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:plugins.event.title',
    'theodia_place' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:plugins.place.title',
];
foreach ($plugins as $CType => $title) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin([
        $title,
        $CType,
        'theodia-default',
    ], 'CType', 'theodia');
}

$flexForms = [
    'theodia_event' => 'FILE:EXT:theodia/Configuration/FlexForms/flexform_event.xml',
    'theodia_place' => 'FILE:EXT:theodia/Configuration/FlexForms/flexform_place.xml',
];
foreach ($flexForms as $CType => $flexForm) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        '*',
        $flexForm,
        $CType
    );

    $GLOBALS['TCA']['tt_content']['types'][$CType]['showitem'] = '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;headers,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.plugin,
            pi_flexform,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
            --palette--;;frames,
            --palette--;;appearanceLinks,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
            --palette--;;language,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
            categories,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
            rowDescription,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
    ';
}

/**
 * Configure a custom preview renderer for the plugins
 * @see https://docs.typo3.org/m/typo3/reference-coreapi/11.5/en-us/ApiOverview/ContentElements/CustomBackendPreview.html
 */
$GLOBALS['TCA']['tt_content']['types']['theodia_event']['previewRenderer']
    = \Causal\Theodia\Preview\EventPreviewRenderer::class;
$GLOBALS['TCA']['tt_content']['types']['theodia_place']['previewRenderer']
    = \Causal\Theodia\Preview\PlacePreviewRenderer::class;
