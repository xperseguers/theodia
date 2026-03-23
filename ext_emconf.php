<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'THEODIA',
    'description' => 'Fetch data from theodia.org to display the mass schedule for the various places of worship in a parish or pastoral unit.',
    'category' => 'plugin',
    'author' => 'Xavier Perseguers',
    'author_email' => 'xavier@causal.ch',
    'author_company' => 'Causal Sàrl',
    'state' => 'stable',
    'version' => '3.2.0-dev',
    'constraints' => [
        'depends' => [
            'php' => '8.1.0-8.5.99',
            'typo3' => '12.4.0-14.3.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
