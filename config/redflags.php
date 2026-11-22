<?php

return [
    // Weight each tag adds to a character's red-flag score.
    'weights' => [
        'yandere' => 40,
        'manipulative' => 30,
        'violent' => 25,
        'stalker' => 30,
        'obsessive' => 20,
        'pathological_liar' => 20,
        'sadistic' => 35,
        'betrayer' => 25,
        'jealous' => 10,
        'reckless' => 10,
    ],

    // Keywords (lowercase) in a character bio that produce a tag.
    'keywords' => [
        'yandere' => ['yandere'],
        'manipulative' => ['manipulat', 'scheming', 'deceiv'],
        'violent' => ['violent', 'murder', 'kills', 'killer', 'brutal'],
        'stalker' => ['stalk'],
        'obsessive' => ['obsess', 'possessive'],
        'pathological_liar' => ['liar', 'lies to'],
        'sadistic' => ['sadis', 'tortur'],
        'betrayer' => ['betray', 'traitor'],
        'jealous' => ['jealous'],
        'reckless' => ['reckless', 'impulsive'],
    ],

    // Minimum score => verdict label.
    'verdicts' => [
        80 => 'Run',
        50 => 'Major red flags',
        20 => 'Some red flags',
        0 => 'Green flag',
    ],

    'characters_per_scan' => 5,
];
