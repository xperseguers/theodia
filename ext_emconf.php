<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'theodia',
    'description' => 'Fetch data from theodia.org to display the mass schedule for the various places of worship in a parish or pastoral unit.',
    'category' => 'plugin',
    'author' => 'Xavier Perseguers',
    'author_email' => 'xavier@causal.ch',
    'author_company' => 'Causal Sàrl',
    'state' => 'stable',
    'version' => '2.3.0',
    'constraints' => [
        'depends' => [
            'php' => '7.4.0-8.4.99',
            'typo3' => '11.5.0-13.4.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
