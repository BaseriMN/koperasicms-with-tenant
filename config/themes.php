<?php

/*
|--------------------------------------------------------------------------
| Palet Tema Korporat
|--------------------------------------------------------------------------
| Setiap palet ada nilai 'light' dan 'dark'. Digunakan oleh master layout
| untuk menetapkan CSS variables (:root) secara dinamik.
|
| Kunci warna mesti sepadan dengan --var dalam master.blade.php:
|   ink, ink-2, panel, bg, bg-2, gold, gold-soft, teal, teal-deep,
|   line, muted, danger, ok
*/

return [

    'default' => 'emerald_gold',

    'palettes' => [

        'emerald_gold' => [
            'label' => 'Emerald Gold',
            'light' => [
                'ink' => '#0c1f1c', 'ink-2' => '#11302b', 'panel' => '#ffffff',
                'bg' => '#f4f1ea', 'bg-2' => '#ece7db', 'gold' => '#c0962c', 'gold-soft' => '#e3c976',
                'teal' => '#1f6f5c', 'teal-deep' => '#0f433a', 'line' => '#e1dccf',
                'muted' => '#7c8783', 'danger' => '#b1402f', 'ok' => '#2f7d54',
            ],
            'dark' => [
                'ink' => '#e9efec', 'ink-2' => '#cdd8d3', 'panel' => '#13211d',
                'bg' => '#0c1714', 'bg-2' => '#16241f', 'gold' => '#d9ab3f', 'gold-soft' => '#e3c976',
                'teal' => '#3a9b82', 'teal-deep' => '#5cbfa3', 'line' => '#23332d',
                'muted' => '#8a988f', 'danger' => '#d4634f', 'ok' => '#4caa75',
            ],
        ],

        'royal_navy' => [
            'label' => 'Royal Navy',
            'light' => [
                'ink' => '#0e1a2b', 'ink-2' => '#16273f', 'panel' => '#ffffff',
                'bg' => '#f1f3f7', 'bg-2' => '#e4e8f0', 'gold' => '#b89653', 'gold-soft' => '#dcc488',
                'teal' => '#2a4a78', 'teal-deep' => '#16314f', 'line' => '#dde2ec',
                'muted' => '#79828f', 'danger' => '#b1402f', 'ok' => '#2f7d54',
            ],
            'dark' => [
                'ink' => '#e8edf5', 'ink-2' => '#c7d2e0', 'panel' => '#15233a',
                'bg' => '#0b1524', 'bg-2' => '#142036', 'gold' => '#cfae6a', 'gold-soft' => '#dcc488',
                'teal' => '#4f7bb5', 'teal-deep' => '#6f97cf', 'line' => '#22324a',
                'muted' => '#8893a3', 'danger' => '#d4634f', 'ok' => '#4caa75',
            ],
        ],

        'burgundy_cream' => [
            'label' => 'Burgundy Cream',
            'light' => [
                'ink' => '#2b1115', 'ink-2' => '#43181f', 'panel' => '#ffffff',
                'bg' => '#f7f1ee', 'bg-2' => '#efe3df', 'gold' => '#b5853f', 'gold-soft' => '#dcbd83',
                'teal' => '#7a2b39', 'teal-deep' => '#581d29', 'line' => '#ecddd7',
                'muted' => '#8a7a76', 'danger' => '#a8342a', 'ok' => '#3f7d4f',
            ],
            'dark' => [
                'ink' => '#f3e7e4', 'ink-2' => '#e0c9c5', 'panel' => '#2a1518', 'bg' => '#1c0d10',
                'bg-2' => '#27161a', 'gold' => '#cda35e', 'gold-soft' => '#dcbd83',
                'teal' => '#b05366', 'teal-deep' => '#c87487', 'line' => '#3a2226',
                'muted' => '#a08c88', 'danger' => '#d4634f', 'ok' => '#56a572',
            ],
        ],

        'slate_teal' => [
            'label' => 'Slate Teal',
            'light' => [
                'ink' => '#16201f', 'ink-2' => '#24302f', 'panel' => '#ffffff',
                'bg' => '#f1f4f3', 'bg-2' => '#e3e9e8', 'gold' => '#3aa6a0', 'gold-soft' => '#7fcec9',
                'teal' => '#2d6f6b', 'teal-deep' => '#1c4a47', 'line' => '#dde4e3',
                'muted' => '#78827f', 'danger' => '#b1402f', 'ok' => '#2f7d54',
            ],
            'dark' => [
                'ink' => '#e8efee', 'ink-2' => '#c8d4d2', 'panel' => '#16201f', 'bg' => '#0d1413',
                'bg-2' => '#16201f', 'gold' => '#4fc2bb', 'gold-soft' => '#7fcec9',
                'teal' => '#479c96', 'teal-deep' => '#63bdb6', 'line' => '#243230',
                'muted' => '#869390', 'danger' => '#d4634f', 'ok' => '#4caa75',
            ],
        ],

        'forest_bronze' => [
            'label' => 'Forest Bronze',
            'light' => [
                'ink' => '#14210f', 'ink-2' => '#1f3318', 'panel' => '#ffffff',
                'bg' => '#f3f4ee', 'bg-2' => '#e6e9dd', 'gold' => '#a9742f', 'gold-soft' => '#d6a866',
                'teal' => '#3a6b32', 'teal-deep' => '#244a1e', 'line' => '#e0e3d4',
                'muted' => '#7c8274', 'danger' => '#b1402f', 'ok' => '#2f7d54',
            ],
            'dark' => [
                'ink' => '#eaf0e4', 'ink-2' => '#cad6c0', 'panel' => '#15210f', 'bg' => '#0c1408',
                'bg-2' => '#162010', 'gold' => '#c79451', 'gold-soft' => '#d6a866',
                'teal' => '#5a9b50', 'teal-deep' => '#77bd6c', 'line' => '#24321d',
                'muted' => '#879382', 'danger' => '#d4634f', 'ok' => '#4caa75',
            ],
        ],

        'plum_rose' => [
            'label' => 'Plum Rose',
            'light' => [
                'ink' => '#241228', 'ink-2' => '#371b3d', 'panel' => '#ffffff',
                'bg' => '#f6f1f5', 'bg-2' => '#ebe0ea', 'gold' => '#b07b9a', 'gold-soft' => '#d8b3cb',
                'teal' => '#6a3d78', 'teal-deep' => '#492554', 'line' => '#ecdde9',
                'muted' => '#86798a', 'danger' => '#b1402f', 'ok' => '#3f7d4f',
            ],
            'dark' => [
                'ink' => '#f1e7f1', 'ink-2' => '#dac9da', 'panel' => '#241228', 'bg' => '#170a1a',
                'bg-2' => '#211024', 'gold' => '#c894b3', 'gold-soft' => '#d8b3cb',
                'teal' => '#9a619f', 'teal-deep' => '#b67fbb', 'line' => '#33203a',
                'muted' => '#9b8c9f', 'danger' => '#d4634f', 'ok' => '#56a572',
            ],
        ],

    ],
];
