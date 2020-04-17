<?php
declare(strict_types=1);
namespace B13\Tag\Domain\Repository;

/*
 * This file is part of TYPO3 CMS-based extension "tag" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstraction layer to keep all tag DB queries within this PHP class.
 */
class TagRepository
{
    private $tableName = 'sys_tag';

    public function findRecordsForTagNames(array $tagNames): array
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $stmt = $queryBuilder
            ->select('*')
            ->from($this->tableName)
            ->where(
                $queryBuilder->expr()->in(
                    'name',
                    $queryBuilder->createNamedParameter($tagNames, Connection::PARAM_STR_ARRAY)
                )
            )
            ->execute();

        $mappedItems = [];
        while ($row = $stmt->fetch()) {
            $mappedItems[$row['name']] = $row['uid'];
        }
        return $mappedItems;
    }

    public function add(string $tagName, int $pid = 0)
    {
        $conn = $this->getConnection();
        $conn->insert($this->tableName, ['name' => $tagName, 'pid' => $pid, 'createdon' => $GLOBALS['EXEC_TIME']]);
        return $conn->lastInsertId();
    }

    /**
     * Simple query for looking for tags that contain the search word. No multi-word / and/or search implemented yet.
     *
     * @param string $searchWord
     * @return array
     */
    public function search(string $searchWord): array
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $stmt = $queryBuilder
            ->select('*')
            ->from($this->tableName)
            ->where(
                $queryBuilder->expr()->like(
                    'name',
                    $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards($searchWord) . '%')
                )
            )
            ->execute();

        $items = [];
        while ($row = $stmt->fetch()) {
            $items[] = [
                'value' => (int)$row['uid'],
                'name' => $row['name']
            ];
        }
        return $items;
    }

    private function getConnection(): Connection
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($this->tableName);
    }
}