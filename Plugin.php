<?php namespace Jagu\Import;

use Backend;
use Lang;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name' => Lang::get('jagu.import::lang.plugin.name'),
            'description' => Lang::get('jagu.import::lang.plugin.description'),
            'author' => 'Jagu s.r.o.',
            'icon' => 'icon-upload'
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => Lang::get('jagu.import::lang.plugin.name'),
                'description' => Lang::get('jagu.import::lang.plugin.description'),
                'category' => SettingsManager::CATEGORY_SYSTEM,
                'icon' => 'icon-upload',
                'url' => Backend::url('jagu/import/import'),
                'order' => 500,
                'keywords' => 'import',
                'permissions' => ['jagu.import.import']
            ]
        ];
    }

    public function registerPermissions()
    {
        return [
            'jagu.import.import' => [
                'label' => Lang::get('jagu.import::lang.permissions.allow_theme_import'),
                'tab' => Lang::get('jagu.import::lang.plugin.name')
            ]
        ];
    }
}
