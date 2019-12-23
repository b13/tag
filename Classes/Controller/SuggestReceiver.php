<?php
declare(strict_types=1);
namespace B13\Tax\Controller;

/*
 * This file is part of TYPO3 CMS-based extension "tax" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Tax\Domain\Repository\TagRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Form Engine helper to find suitable tags to select from.
 */
class SuggestReceiver
{
    /**
     * @var TagRepository
     */
    protected $tagRepository;

    public function __construct()
    {
        $this->tagRepository = GeneralUtility::makeInstance(TagRepository::class);
    }

    public function findSuitableTags(ServerRequestInterface $request): ResponseInterface
    {
        $search = $request->getQueryParams()['q'] ?? '';
        if ($search) {
            $tags = $this->tagRepository->search($search);
        } else {
            $tags = [];
        }
        return new JsonResponse($tags);
    }
}