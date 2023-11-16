<?php
$GLOBALS['SiteConfiguration']['site']['columns']['tx_theodia_calendars'] = [
    'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:site.tx_theodia_calendars',
    'config' => [
        'type' => 'text',
        'rows' => 10,
        'default' => implode(LF, [
            '148, Église Sts Pierre-et-Paul, Marly',
            '149, Église du Saint-Sacrement, Marly',
            '150, Église de Praroman, Le Mouret',
        ]),
    ],
];

$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] .= ',
    --div--;LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tabs.theodia,
        tx_theodia_calendars';
