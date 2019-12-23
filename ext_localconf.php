<?php
defined('TYPO3_MODE') || die('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1433089350] = [
    'nodeName' => 'tagList',
    'priority' => 60,
    'class' => \B13\Tax\Form\TagListElement::class,
];
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['Tax'] = \B13\Tax\Persistence\PrepareTagItems::class;