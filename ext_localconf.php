<?php
defined('TYPO3') || die();

(static function (string $_EXTKEY) {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        $_EXTKEY,
        'Event',
        [
            \Causal\Theodia\Controller\EventController::class => 'list',
        ],
        [],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        $_EXTKEY,
        'Place',
        [
            \Causal\Theodia\Controller\PlaceController::class => 'show',
        ],
        [],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1551542901] = [
        'nodeName' => 'locationMap',
        'priority' => '70',
        'class' => \Causal\Theodia\Backend\Form\Element\LocationMap::class,
    ];

    $versionInformation = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class);
    if ($versionInformation->getMajorVersion() === 10) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\Form\FormDataProvider\SiteDatabaseEditRow::class] = [
            'className' => \Causal\Theodia\Xclass\V10\Backend\Form\FormDataProvider\SiteDatabaseEditRow::class,
        ];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\Form\FormDataProvider\SiteTcaInline::class] = [
            'className' => \Causal\Theodia\Xclass\V10\Backend\Form\FormDataProvider\SiteTcaInline::class,
        ];
    } else {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\Form\FormDataProvider\SiteDatabaseEditRow::class] = [
            'className' => \Causal\Theodia\Xclass\V11\Backend\Form\FormDataProvider\SiteDatabaseEditRow::class,
        ];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\Form\FormDataProvider\SiteTcaInline::class] = [
            'className' => \Causal\Theodia\Xclass\V11\Backend\Form\FormDataProvider\SiteTcaInline::class,
        ];
    }

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['TxTheodiaPlugins']
        = \Causal\Theodia\Updates\PluginsUpdater::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['TxTheodiaSiteConfiguration']
        = \Causal\Theodia\Updates\SiteConfigurationUpdater::class;
})('theodia');
