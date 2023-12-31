<?php
defined('TYPO3') || die();

(static function (string $_EXTKEY) {
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon('theodia-default', \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class, [
        'source' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/Extension.svg',
    ]);
})('theodia');
