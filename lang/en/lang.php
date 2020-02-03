<?php return [
    'plugin' => [
        'name' => 'Import theme',
        'description' => 'Import or replace OctoberCMS themes'
    ],
    'permissions' => [
        'allow_theme_import' => 'Allow theme import'
    ],
    'backend' => [
        'import' => 'Import theme',
        'theme_zip' => 'Theme ZIP file',
        'theme_zip_hint' => 'Compressed file with October CMS theme',
        'replace_static_pages_content' => 'Replace files generated by Rainlab.Pages plugin',
        'replace_static_pages_content_hint' => 'Turn this on if you want to replace all files generated by Rainlab.Pages plugin, e.g. static pages, static menus, etc.',
        'yes' => 'Yes',
        'no' => 'No',
        'flash' => [
            'success' => 'Theme was successfully imported',
            'invalid_input' => 'Invalid input',
            'invalid_extension' => 'Invalid extension',
            'could_not_open_zip' => 'Could not open ZIP file',
            'invalid_dir_structure' => 'Invalid directory structure'
        ]
    ],
    'button' => [
        'import' => 'Import'
    ]
];
