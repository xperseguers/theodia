<?php
defined('TYPO3') || die();

if ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12) {
    $GLOBALS['TCA']['tx_theodia_place']['columns']['categories'] = [
        'config' => [
            'type' => 'category'
        ]
    ];
} else {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
        'theodia',
        'tx_theodia_place',
        'categories',
        [
            'position' => 'replace:categories',
        ]
    );
}
