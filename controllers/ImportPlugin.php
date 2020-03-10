<?php

namespace Jagu\Import\Controllers;

use BackendMenu;
use File;
use FilesystemIterator;
use Flash;
use Input;
use Lang;
use Artisan;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use System\Classes\SettingsManager;
use ZipArchive;

class ImportPlugin extends \Backend\Classes\Controller
{
    public $requiredPermissions = ['jagu.import.import'];

    private const FILE_NAME = 'plugin_zip';

    /**
     * ImportPlugin constructor.
     */
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('October.System', 'system', 'import_plugin');
        SettingsManager::setContext('Jagu.Import', 'import_plugin');
        $this->pageTitle = Lang::get('jagu.import::lang.plugin.import_plugin');
    }

    public function index()
    {
        $this->cleanUpTemp();

        if (Input::hasFile(self::FILE_NAME)) {
            if (!Input::file(self::FILE_NAME)->isValid()) {
                Flash::error(Lang::get('jagu.import::lang.backend.flash.invalid_input'));
                $this->cleanUpTemp();
                return;
            }

            $extension = Input::file(self::FILE_NAME)->getClientOriginalExtension();

            if ($extension !== 'zip') {
                Flash::error(Lang::get('jagu.import::lang.backend.flash.invalid_extension'));
                $this->cleanUpTemp();
                return;
            }

            // move uploaded archive to /plugins
            Input::file(self::FILE_NAME)->move('plugins/', '_tmp.zip');

            $zip = new ZipArchive();

            $extract = $zip->open('plugins/_tmp.zip');
            if ($extract !== true) {
                Flash::error(Lang::get('jagu.import::lang.backend.flash.could_not_open_zip'));
                $this->cleanUpTemp();
                return;
            }

            // extract *.zip to plugins/_tmp directory
            $zip->extractTo('plugins/_tmp');

            $authorDir = array_diff(scandir('plugins/_tmp'), ['..', '.']);
            if (count($authorDir) !== 1) {
                Flash::error(Lang::get('jagu.import::lang.backend.flash.invalid_dir_structure'));
                $this->cleanUpTemp();
                return;
            }
            $authorDir = array_pop($authorDir);

            $pluginDir = \array_diff(scandir('plugins/_tmp/' . $authorDir), ['..', '.']);
            if (count($pluginDir) !== 1) {
                Flash::error(Lang::get('jagu.import::lang.backend.flash.invalid_dir_structure'));
                $this->cleanUpTemp();
                return;
            }

            $pluginDir = array_pop($pluginDir);

            debug($pluginDir);

            // remove all existing plugin files
            try {
                $di = new RecursiveDirectoryIterator('plugins/' . $authorDir . '/' . $pluginDir, FilesystemIterator::SKIP_DOTS);
                $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
                foreach ($ri as $file) {
                    $file->isDir() ? rmdir($file) : unlink($file);
                }
            } catch (\Throwable $exception) {
                // do nothing, exception indicates that plugin is imported for the first time
            }

            // move uploaded files to original location
            File::move('plugins/_tmp/' . $authorDir . '/' . $pluginDir, 'plugins/' . $authorDir . '/' . $pluginDir);

            // run artisan command to set up plugin database
            Artisan::call('october:up');

            // delete temporary directories
            $this->cleanUpTemp();

            Flash::success(Lang::get('jagu.import::lang.backend.flash.plugin_success'));
        }
    }

    private function cleanUpTemp()
    {
        File::delete('plugins/_tmp.zip');
        File::delete('plugins/_tmp');
    }
}
