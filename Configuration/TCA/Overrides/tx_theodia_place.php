<?php
defined('TYPO3') || die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
    'theodia',
    'tx_theodia_place',
    'categories',
    [
        'position' => 'replace:categories',
    ]
);
