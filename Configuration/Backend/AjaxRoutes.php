<?php

return [
    'tag_suggest_tags' => [
        'path' => '/tag/suggest',
        'target' => B13\Tag\Controller\SuggestReceiver::class . '::findSuitableTags',
    ],
];
