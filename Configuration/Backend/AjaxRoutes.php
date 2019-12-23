<?php

return [
    'tax_suggest_tags' => [
        'path' => '/tag/suggest',
        'target' => B13\Tax\Controller\SuggestReceiver::class . '::findSuitableTags'
    ],
];