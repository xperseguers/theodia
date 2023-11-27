<?php
defined('TYPO3') || die();

(static function (string $_EXTKEY) {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        $_EXTKEY,
        'Event',
        [
            \Causal\Theodia\Controller\EventController::class => 'list',
        ],
        []
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        $_EXTKEY,
        'Place',
        [
            \Causal\Theodia\Controller\PlaceController::class => 'show',
        ],
        []
    );

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1551542901] = [
        'nodeName' => 'locationMap',
        'priority' => '70',
        'class' => \Causal\Theodia\Backend\Form\Element\LocationMap::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['TxTheodiaPlugins']
        = \Causal\Theodia\Updates\PluginsUpdater::class;
})('theodia');
