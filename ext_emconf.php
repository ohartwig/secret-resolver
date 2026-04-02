<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Secret Resolver',
    'description' => 'Runtime secret resolution for TYPO3 site configuration — cascading lookup from secret files, /run/secrets/ mounts, and environment variables via %secret(KEY)% placeholder syntax.',
    'category' => 'misc',
    'author' => 'Moselwal Digitalagentur',
    'author_email' => 'info@moselwal.de',
    'state' => 'beta',
    'version' => '0.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.0.0-14.99.99',
            'php' => '8.3.0-8.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
