<?php

$finder = PhpCsFixer\Finder::create()
    ->in('Classes')
    ->in('Configuration');

$config = \TYPO3\CodingStandards\CsFixerConfig::create();
return $config
    ->setUsingCache(false)
    ->addRules([
        'nullable_type_declaration' => [
            'syntax' => 'question_mark',
        ],
        'nullable_type_declaration_for_default_null_value' => true,
    ])
    ->setFinder($finder);
