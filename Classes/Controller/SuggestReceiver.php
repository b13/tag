<?php

declare(strict_types=1);

namespace B13\Tag\Controller;

/*
 * This file is part of TYPO3 CMS-based extension "tag" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Tag\Domain\Repository\TagRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;

class SuggestReceiver
{
    protected TagRepository $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
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
