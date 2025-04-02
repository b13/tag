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

/**
 * Abstraction layer to keep all tag DB queries within this PHP class.
 */
class TagRepository
{
    private const TABLE_NAME = 'sys_tag';

    private ConnectionPool $connectionPool;

    public function __construct(ConnectionPool $connectionPool)
    {
        $this->connectionPool = $connectionPool;
    }

    public function findRecordsForTagNames(array $tagNames): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        $stmt = $queryBuilder
            ->select('*')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->in('name', $queryBuilder->createNamedParameter($tagNames, Connection::PARAM_STR_ARRAY))
            )
            ->executeQuery();

        $mappedItems = [];
        while ($row = $stmt->fetchAssociative()) {
            $mappedItems[$row['name']] = $row['uid'];
        }

        return $mappedItems;
    }

    public function add(string $tagName, int $pid = 0): string
    {
        $conn = $this->connectionPool->getConnectionForTable(self::TABLE_NAME);
        $conn->insert(
            self::TABLE_NAME,
            [
                'name' => $tagName,
                'pid' => $pid,
                'createdon' => $GLOBALS['EXEC_TIME'],
            ]
        );

        return $conn->lastInsertId();
    }

    /**
     * Simple query for looking for tags that contain the search word. No multi-word / and/or search implemented yet.
     */
    public function search(string $searchWord): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        $stmt = $queryBuilder
            ->select('*')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->like('name', $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards($searchWord) . '%'))
            )
            ->executeQuery();

        $items = [];
        while ($row = $stmt->fetchAssociative()) {
            $items[] = [
                'value' => (int)$row['uid'],
                'name' => $row['name'],
            ];
        }
        return $items;
    }
}
