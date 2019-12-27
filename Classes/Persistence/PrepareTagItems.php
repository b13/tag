<?php
declare(strict_types=1);
namespace B13\Tax\Persistence;

/*
 * This file is part of TYPO3 CMS-based extension "tax" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */


use B13\Tax\Domain\Repository\TagRepository;
use B13\Tax\TcaHelper;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PrepareTagItems
{
    /**
     * @var TagRepository
     */
    protected $tagRepository;

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

        foreach ($relevantFields as $fieldName) {
            if (isset($incomingFieldArray[$fieldName]) && !empty($incomingFieldArray[$fieldName])) {
                if (!is_array($incomingFieldArray[$fieldName])) {
                    $incomingFieldArray[$fieldName] = explode(',', $incomingFieldArray[$fieldName]);
                }
                $incomingFieldArray[$fieldName] = $this->normalizeValuesAndMapToIds(
                    $incomingFieldArray[$fieldName],
                    (int)($incomingFieldArray['pid'] > 0 ? $incomingFieldArray['pid'] : 0)
                );
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
            if (is_numeric($tag) || $tagsInDatabase[$tag]) {
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