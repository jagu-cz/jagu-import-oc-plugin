<?php return [
    'plugin' => [
        'name' => 'Import šablon',
        'description' => 'Import nebo nahrazení OctoberCMS šablon'
    ],
    'permissions' => [
        'allow_theme_import' => 'Povolit import šablon'
    ],
    'backend' => [
        'import' => 'Import šablon',
        'theme_zip' => 'ZIP soubor s šablonou',
        'theme_zip_hint' => 'Zazipovaný soubor s šablonou pro October CMS',
        'replace_static_pages_content' => 'Nahradit soubory, které jsou generovány pomocí Rainlab.Pages pluginu',
        'replace_static_pages_content_hint' => 'Zapněte tuto možnost, pokud si přejete přepsat všechny soubory, které jsou generovány pluginem Rainlab.Pages, jako jsou statické stránky, statické menu, atd.',
        'yes' => 'Ano',
        'no' => 'Ne',
        'flash' => [
            'success' => 'Šablona byla úspěšně importována',
            'invalid_input' => 'nesprávný vstup',
            'invalid_extension' => 'Nepovolená přípona souboru',
            'could_not_open_zip' => 'Nepodařilo se otevřít ZIP soubor',
            'invalid_dir_structure' => 'Neplatná adresářová struktura šablony'
        ]
    ],
    'button' => [
        'import' => 'Importovat'
    ]
];
