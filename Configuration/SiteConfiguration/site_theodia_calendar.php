<?php
return [
    'ctrl' => [
        'label' => 'name',
        'title' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:site_theodia_calendar',
        'typeicon_classes' => [
            'default' => 'actions-calendar-alternative',
        ],
    ],
    'columns' => [
        'id' => [
            'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:site_theodia_calendar.id',
            'config' => [
                'type' => 'input',
                'size' => 6,
                'range' => [
                    'lower' => 0,
                ],
                'eval' => 'required, int',
                'placeholder' => '148',
            ],
        ],
        'name' => [
            'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:site_theodia_calendar.name',
            'config' => [
                'type' => 'input',
                'eval' => 'required, trim',
                'placeholder' => 'Église Sts Pierre-et-Paul, Marly',
            ],
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '--palette--;;general',
        ],
    ],
    'palettes' => [
        'general' => [
            'showitem' => 'id, name',
        ],
    ],
];
