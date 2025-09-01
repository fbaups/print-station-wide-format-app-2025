<?php

$images = [
    [
        'hashtag' => 'a7e87329b5eab8578f4f1098a152d6f4',
        'title' => 'Flower',
        'order' => 3,

    ],
    [
        'hashtag' => 'b24ce0cd392a5b0b8dedc66c25213594',
        'title' => 'Free',
        'order' => 2,

    ],
    [
        'hashtag' => 'e7d31fc0602fb2ede144d18cdffd816b',
        'title' => 'Ready',
        'order' => 1,
    ],
];

uasort($images, function ($a, $b) {
    return $a['order'] <=> $b['order'];
});

print_r($images);