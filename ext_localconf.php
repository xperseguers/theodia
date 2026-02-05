<?php
defined('TYPO3') || die();

(static function (string $_EXTKEY) {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        $_EXTKEY,
        'Event',
        [
            \Causal\Theodia\Controller\EventController::class => 'list, showMore',
        ],
        [
            \Causal\Theodia\Controller\EventController::class => 'showMore',
        ],
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

    $typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class)->getMajorVersion();

    if ($typo3Version >= 13) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\Form\FormDataProvider\SiteDatabaseEditRow::class] = [
            'className' => \Causal\Theodia\Xclass\V13\Backend\Form\FormDataProvider\SiteDatabaseEditRow::class,
        ];
    } else {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\Form\FormDataProvider\SiteDatabaseEditRow::class] = [
            'className' => \Causal\Theodia\Xclass\V11\Backend\Form\FormDataProvider\SiteDatabaseEditRow::class,
        ];
    }

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\Form\FormDataProvider\SiteTcaInline::class] = [
        'className' => \Causal\Theodia\Xclass\V11\Backend\Form\FormDataProvider\SiteTcaInline::class,
    ];

    if ($typo3Version < 13) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
            @import \'EXT:theodia/Configuration/TSconfig/ContentElementWizard.tsconfig\'
        ');
    }
})('theodia');
