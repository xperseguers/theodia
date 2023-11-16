<?php
defined('TYPO3') || die();

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
