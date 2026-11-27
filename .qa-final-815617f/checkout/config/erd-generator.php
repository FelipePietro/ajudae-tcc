<?php

/**
 * Ajudaê — configuração visual do DER completo.
 * Compatível com beyondcode/laravel-er-diagram-generator 6.0.0.
 * Salve como config/erd-generator.php.
 */

return [
    // Modelos que serão analisados.
    'directories' => [
        base_path('app' . DIRECTORY_SEPARATOR . 'Models'),
    ],
    'ignore' => [],
    'whitelist' => [],
    'aliases' => [],
    'recursive' => true,

    // Mostrar todos os atributos do banco, incluindo os tipos de dados.
    'use_db_schema' => true,
    'use_column_types' => true,
    'ignore_columns' => [],

    // Aparência das tabelas.
    'table' => [
        'header_background_color' => '#17324D',
        'header_font_color' => '#FFFFFF',
        'row_background_color' => '#FFFFFF',
        'row_font_color' => '#263445',
    ],

    // Organização geral (Graphviz / dot).
    'graph' => [
        'bgcolor' => '#FFFFFF',
        'fontname' => 'Arial',
        'fontsize' => 12,
        'labelloc' => 't',
        'rankdir' => 'LR',
        'splines' => 'polyline',
        'overlap' => 'false',
        'nodesep' => 0.95,
        'ranksep' => 2.2,
        'pad' => 0.5,
        'concentrate' => true,
        'outputorder' => 'edgesfirst',
    ],

    // Aparência das entidades; os rótulos com atributos são gerados pelo pacote.
    'node' => [
        'shape' => 'plain',
        'margin' => 0,
        'fontname' => 'Arial',
        'fontsize' => 11,
    ],

    // Conexões entre entidades.
    'edge' => [
        'color' => '#94A3B8',
        'penwidth' => 1.0,
        'fontname' => 'Arial',
        'fontsize' => 9,
        'fontcolor' => '#64748B',
        'arrowsize' => 0.7,
    ],

    // Diferenciação visual dos tipos de relacionamento.
        'relations' => [
            'HasOne' => [
                'dir' => 'both',
                'color' => '#2563EB',
                'penwidth' => 2.0,
                'arrowhead' => 'tee',
                'arrowtail' => 'none',
            ],

            'HasMany' => [
                'dir' => 'both',
                'color' => '#16A34A',
                'penwidth' => 2.0,
                'arrowhead' => 'crow',
                'arrowtail' => 'none',
            ],

            'BelongsTo' => [
                'dir' => 'both',
                'color' => '#EA580C',
                'penwidth' => 2.0,
                'arrowhead' => 'tee',
                'arrowtail' => 'crow',
            ],
        ],
];
