<?php

return [
    'dependencies' => [
        'core',
    ],
    'tags' => [
        'backend.form',
    ],
    'imports' => [
        'typeahead' => 'EXT:tag/Resources/Public/JavaScript/typeahead-esm.js',
        '@b13/tag/tags-input-element.js' => 'EXT:tag/Resources/Public/JavaScript/tags-input-element.js',
    ],
];
