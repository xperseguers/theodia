<?php
return [
    'ctrl' => [
        'title' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tx_theodia_place',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'versioningWS' => true,
        'origUid' => 't3_origuid',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'default_sortby' => 'ORDER BY name',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:theodia/Resources/Public/Icons/tx_theodia_place.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden,
                    name, parish, patron, description, year, century, seats, url,
                --div--;LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tabs.address,
                    address, postal_code, city, region, country,
                --div--;LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tabs.gps,
                    --palette--;;gps,
                --div--;LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tabs.relations,
                    place_id, page_uid, photo'
        ],
    ],
    'palettes' => [
        'gps' => [
            'showitem' => 'latitude, longitude, --linebreak--, map',
        ],
    ],
    'columns' => [
        't3ver_label' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'max' => '30',
            ],
        ],
        'sys_language_uid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => [
                    ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages', -1],
                    ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.default_value', 0]
                ],
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_theodia_place',
                'foreign_table_where' => 'AND tx_theodia_place.pid=###CURRENT_PID### AND tx_theodia_place.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => '0',
            ],
        ],
        'name' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tx_theodia_place.name',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'max' => '255',
                'eval' => 'required,trim',
            ],
        ],
        'parish' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tx_theodia_place.parish',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_theodia_parish',
                'foreign_table_where' => 'AND tx_theodia_parish.pid=###CURRENT_PID### AND tx_theodia_parish.sys_language_uid IN (0, -1) ORDER BY tx_theodia_parish.name',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ]
        ],
        'place_id' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tx_theodia_place.place_id',
            'l10n_mode' => 'exclude',
            'config' => [
                'type' => 'input',
                'size' => '6',
                'eval' => 'int',
            ],
        ],
        'latitude' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tx_theodia_place.latitude',
            'l10n_mode' => 'exclude',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'max' => 10,
                'eval' => 'trim',
                'checkbox' => 0,
                'default' => '0.00',
            ],
        ],
        'longitude' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tx_theodia_place.longitude',
            'l10n_mode' => 'exclude',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'max' => 10,
                'eval' => 'trim',
                'checkbox' => 0,
                'default' => '0.00',
            ],
        ],
        'patron' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tx_theodia_place.patron',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'max' => '100',
                'eval' => 'trim',
            ],
        ],
        'description' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tx_theodia_place.description',
            'config' => [
                'type' => 'text',
                'enableRichtext' => 1,
                'richtextConfiguration' => 'default',
                'cols' => 30,
                'rows' => 5,
                'softref' => 'typolink_tag,images,email[subst],url',
            ],
        ],
        'year' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tx_theodia_place.year',
            'l10n_mode' => 'exclude',
            'config' => [
                'type' => 'input',
                'size' => '6',
                'eval' => 'int',
            ],
        ],
        'century' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tx_theodia_place.century',
            'l10n_mode' => 'exclude',
            'config' => [
                'type' => 'input',
                'size' => '5',
                'max' => '5',
                'eval' => 'upper,is_in',
                'is_in' => 'IVX',
            ],
        ],
        'seats' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tx_theodia_place.seats',
            'l10n_mode' => 'exclude',
            'config' => [
                'type' => 'input',
                'size' => '6',
                'eval' => 'int',
            ],
        ],
        'address' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tx_theodia_place.address',
            'l10n_mode' => 'exclude',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'max' => '255',
                'eval' => 'trim',
            ],
        ],
        'postal_code' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tx_theodia_place.postal_code',
            'l10n_mode' => 'exclude',
            'config' => [
                'type' => 'input',
                'size' => '6',
                'max' => '10',
                'eval' => 'trim',
            ],
        ],
        'city' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tx_theodia_place.city',
            'l10n_mode' => 'exclude',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'max' => '100',
                'eval' => 'trim',
            ],
        ],
        'region' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tx_theodia_place.region',
            'l10n_mode' => 'exclude',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'max' => '100',
                'eval' => 'trim',
            ],
        ],
        'country' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tx_theodia_place.country',
            'l10n_mode' => 'exclude',
            'config' => [
                'type' => 'input',
                'size' => '2',
                'max' => '2',
                'eval' => 'trim,upper',
            ],
        ],
        'url' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tx_theodia_place.url',
            'config' => [
                'type' => 'input',
                'size' => '40',
                'max' => '255',
                'eval' => 'nospace',
            ],
        ],
        'page_uid' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tx_theodia_place.page_uid',
            'l10n_mode' => 'exclude',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'multiple' => 0,
            ],
        ],
        'photo' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tx_theodia_place.photo',
            'l10n_mode' => 'exclude',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'photo',
                [
                    'maxitems' => 1,
                    'minitems'=> 0
                ],
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
            )
        ],
        'map' => [
            'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tx_theodia_place.map',
            'l10n_mode' => 'exclude',
            'config' => [
                'type' => 'none',
                'renderType' => 'locationMap',
                'parameters' => [
                    'latitude' => 'latitude',
                    'longitude' => 'longitude',
                    'street' => 'address',
                    'zip' => 'postal_code',
                    'city' => 'city',
                    'country' => 'country',
                ],
            ],
        ],
    ],
];
