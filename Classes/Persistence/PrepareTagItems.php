<?php

declare(strict_types=1);

namespace B13\Tag\Persistence;

/*
 * This file is part of TYPO3 CMS-based extension "tag" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Tag\Domain\Repository\TagRepository;
use B13\Tag\TcaHelper;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class PrepareTagItems
{
    protected TagRepository $tagRepository;

    public function __construct()
    {
        $this->tagRepository = GeneralUtility::makeInstance(TagRepository::class);
    }

    /**
     * DataHandler hook to create tags automatically if they don't exist yet. This way, a clean list of
     * IDs is entered to DataHandler.
     *
     * @param $incomingFieldArray
     * @param $table
     * @param $id
     * @param DataHandler $dataHandler
     */
    public function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, DataHandler $dataHandler)
    {
        $relevantFields = (new TcaHelper())->findTagFieldsForTable($table);
        if (empty($relevantFields)) {
            return;
        }

        $currentPid = 0;
        if (isset($incomingFieldArray['pid'])) {
            $currentPid = (int)$incomingFieldArray['pid'];
        } elseif (MathUtility::canBeInterpretedAsInteger($id)) {
            // Existing record, we know something
            $record = BackendUtility::getRecord($table, $id, 'pid');
            if (is_array($record)) {
                $currentPid = (int)$record['pid'];
            }
        }

        foreach ($relevantFields as $fieldName) {
            if (!isset($incomingFieldArray[$fieldName])) {
                continue;
            }
            $convertToList = false;
            if (!is_array($incomingFieldArray[$fieldName])) {
                $convertToList = true;
                $incomingFieldArray[$fieldName] = explode(',', (string)$incomingFieldArray[$fieldName]);
            }
            // Remove "empty" parts
            $incomingFieldArray[$fieldName] = array_filter($incomingFieldArray[$fieldName] ?? []);
            $incomingFieldArray[$fieldName] = $this->normalizeValuesAndMapToIds(
                $incomingFieldArray[$fieldName],
                $currentPid
            );
            if ($convertToList) {
                $incomingFieldArray[$fieldName] = implode(',', $incomingFieldArray[$fieldName]);
            }
        }
    }

    /**
     * See what tags are already in the database and add missing tags, and map the tag names to the IDs.
     *
     * @param array $tags
     * @param int $pid
     * @return array
     */
    protected function normalizeValuesAndMapToIds(array $tags, int $pid): array
    {
        $unmappedTags = $tags;
        $tagsInDatabase = $this->tagRepository->findRecordsForTagNames($tags);
        foreach ($tags as $k => $tag) {
            if (is_numeric($tag) || isset($tagsInDatabase[$tag])) {
                unset($unmappedTags[$k]);
            }
        }
        // Now add the missing maps, add them to the list fo IDs, and return that.
        foreach ($unmappedTags as $tag) {
            $insertId = $this->tagRepository->add($tag, $pid);
            $tagsInDatabase[$tag] = $insertId;
        }

        // Now loop over the array to keep the ordering
        $tagIds = [];
        foreach ($tags as $tag) {
            if (isset($tagsInDatabase[$tag])) {
                $tagIds[] = $tagsInDatabase[$tag];
            } elseif (is_numeric($tag)) {
                $tagIds[] = $tag;
            }
        }
        return $tagIds;
    }
}
