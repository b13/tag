<?php

$finder = PhpCsFixer\Finder::create()
    ->in('Classes')
    ->in('Configuration');

$config = \TYPO3\CodingStandards\CsFixerConfig::create();
return $config
    ->setUsingCache(false)
    ->setFinder($finder);
