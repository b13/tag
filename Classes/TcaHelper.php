<?php
declare(strict_types=1);
namespace B13\Tax;

/*
 * This file is part of TYPO3 CMS-based extension "tax" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

/**
 * Helper functionality to quickly work with tags without having to configure TCA, this also allows for "tax"
 * to change implementation without having users to modify their code.
 */
class TcaHelper
{
    public function buildFieldConfiguration(string $table, string $fieldName, array $fieldConfigurationOverride = null): array
    {
        $fieldConfiguration = [
            'type' => 'select',
            'renderType' => 'tagList',
            'minitems' => 0,
            'maxitems' => 100,
            'multiple' => true,
            'items' => [],
            'foreign_table' => 'sys_tag',
            'MM' => 'sys_tag_mm',
            'MM_hasUidField' => true,
            'MM_opposite_field' => 'items',
            'MM_match_fields' => [
                'tablenames' => $table,
                'fieldname' => $fieldName,
            ],
        ];
        // Merge changes to TCA configuration
        if (!empty($fieldConfigurationOverride)) {
            array_replace_recursive(
                $fieldConfiguration,
                $fieldConfigurationOverride
            );
        }

        // Register opposite references for the foreign side of a relation
        if (empty($GLOBALS['TCA']['sys_tag']['columns']['items']['config']['MM_oppositeUsage'][$table])) {
            $GLOBALS['TCA']['sys_tag']['columns']['items']['config']['MM_oppositeUsage'][$table] = [];
        }
        if (!in_array($fieldName, $GLOBALS['TCA']['sys_tag']['columns']['items']['config']['MM_oppositeUsage'][$table], true)) {
            $GLOBALS['TCA']['sys_tag']['columns']['items']['config']['MM_oppositeUsage'][$table][] = $fieldName;
        }

        return $fieldConfiguration;
    }

    /**
     * Shorthand function to identify all fields that have tags based on the foreign_table field.
     *
     * @param string $table
     * @return array
     */
    public function findTagFieldsForTable(string $table): array
    {
        $tagFieldNames = [];
        foreach ($GLOBALS['TCA'][$table]['columns'] as $column => $columnDetails) {
            if ($columnDetails['config']['foreign_table'] ?? '' === 'sys_tag') {
                $tagFieldNames[] = $column;
            }
        }
        return $tagFieldNames;
    }
}