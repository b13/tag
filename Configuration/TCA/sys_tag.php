<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:tag/Resources/Private/Language/locallang_tca.xlf:sys_tag',
        'label' => 'name',
        'tstamp' => 'updatedon',
        'crdate' => 'createdon',
        'cruser_id' => 'createdby',
        'delete' => 'deleted',
        'default_sortby' => 'name',
        'rootLevel' => -1,
        'searchFields' => 'name',
        'typeicon_classes' => [
            'default' => 'mimetypes-x-sys_category',
        ],
        'security' => [
            'ignoreRootLevelRestriction' => true,
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, name, items',
        ],
    ],
    'palettes' => [
    ],
    'columns' => [
        'name' => [
            'label' => 'LLL:EXT:tag/Resources/Private/Language/locallang_tca.xlf:sys_tag.name',
            'config' => [
                'type' => 'input',
                'width' => 200,
                'eval' => 'trim,required',
            ],
        ],
        'items' => [
            'label' => 'LLL:EXT:tag/Resources/Private/Language/locallang_tca.xlf:sys_tag.items',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => '*',
                'MM' => 'sys_tag_mm',
                'MM_oppositeUsage' => [],
                'size' => 10,
                'fieldWizard' => [
                    'recordsOverview' => [
                        'disabled' => true,
                    ],
                ],
            ],
        ],
    ],
];
