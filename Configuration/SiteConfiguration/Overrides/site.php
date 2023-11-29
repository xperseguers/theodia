<?php
$GLOBALS['SiteConfiguration']['site']['columns']['tx_theodia_calendars'] = [
    'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:site.tx_theodia_calendars',
    'config' => [
        'type' => 'text',
        'rows' => 10,
        'default' => implode(LF, [
            '148, Église Sts Pierre-et-Paul, Marly',
            '150, Église de Praroman, Le Mouret',
        ]),
    ],
];
$GLOBALS['SiteConfiguration']['site']['columns']['tx_theodia_calendars2'] = [
    'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:site.tx_theodia_calendars',
    'config' => [
        'type' => 'inline',
        'foreign_table' => 'site_theodia_calendar',
        'appearance' => [
            'collapseAll' => true,
            'enabledControls' => [
                'info' => false,
            ],
        ],
    ]
];

$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] .= ',
    --div--;LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tabs.theodia,
        tx_theodia_calendars, tx_theodia_calendars2';
