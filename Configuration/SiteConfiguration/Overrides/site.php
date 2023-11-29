<?php
$GLOBALS['SiteConfiguration']['site']['columns']['tx_theodia_calendars'] = [
    'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:site.tx_theodia_calendars',
    'config' => [
        'type' => 'inline',
        'foreign_table' => 'site_theodia_calendar',
        'foreign_sortby' => 'sorting',  // This field doesn't exist but is required to enable sorting
        'appearance' => [
            'collapseAll' => true,
            'enabledControls' => [
                'info' => false,
            ],
            'useSortable' => true,
        ],
    ]
];

$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] .= ',
    --div--;LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tabs.theodia,
        tx_theodia_calendars';
