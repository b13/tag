<?php
defined('TYPO3') || die('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1433089350] = [
    'nodeName' => 'tagList',
    'priority' => 60,
    'class' => \B13\Tag\Form\TagListElement::class,
];
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['b13/tag'] = \B13\Tag\Persistence\PrepareTagItems::class;
