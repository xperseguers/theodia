<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'theodia',
    'description' => 'Official integration of theodia (theodia.org) for TYPO3.',
    'category' => 'plugin',
    'author' => 'Xavier Perseguers',
    'author_email' => 'xavier@causal.ch',
    'author_company' => 'Causal Sàrl',
    'state' => 'beta',
    'version' => '0.1.0-dev',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-12.4.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
