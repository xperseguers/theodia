<?php
return [
    // Unsure about the need for those dependencies at this point:
    'dependencies' => [
        'backend',
        'core',
    ],
    'imports' => [
        '@causal/theodia/' => 'EXT:theodia/Resources/Public/ECMAScript6/',
        'leaflet' => 'EXT:theodia/Resources/Public/JavaScript/Contrib/leaflet.js',
    ],
];
