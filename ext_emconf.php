<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Secret Resolver',
    'description' => 'Runtime secret resolution for TYPO3 site configuration — cascading lookup from secret files, /run/secrets/ mounts, and environment variables via %secret(KEY)% placeholder syntax.',
    'category' => 'misc',
    'author' => 'Moselwal Digitalagentur',
    'author_email' => 'info@moselwal.de',
    'state' => 'alpha',
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.0.0-14.99.99',
            'php' => '8.3.0-8.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
