<?php
$typo3Branch = (new \TYPO3\CMS\Core\Information\Typo3Version())->getBranch();
$v11AndUp = version_compare($typo3Branch, '11.5', '>=');
$v12AndUp = version_compare($typo3Branch, '12.4', '>=');

$tca = [
    'ctrl' => [
        'title' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tx_theodia_parish',
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
        'iconfile' => 'EXT:theodia/Resources/Public/Icons/tx_theodia_parish.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden,
                    name'
        ],
    ],
    'palettes' => [],
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
            'config' => $v11AndUp
                ? [
                    'type' => 'language',
                ]
                : [
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
                'items' => $v12AndUp
                    ? [
                        [
                            'label' => '',
                            'value' => 0,
                        ],
                    ]
                    : [
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
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        0 => '',
                        1 => '',
                    ]
                ],
            ],
        ],
        'name' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:theodia/Resources/Private/Language/locallang_db.xlf:tx_theodia_parish.name',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'max' => '255',
                'eval' => $v12AndUp ? 'trim' : 'required,trim',
                'required' => true,
            ],
        ],
    ],
];

if ($v12AndUp) {
    unset($tca['ctrl']['cruser_id']);
}

return $tca;
