<?php

namespace Jagu\Import\Controllers;

use BackendMenu;
use File;
use FilesystemIterator;
use Flash;
use Input;
use Lang;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use System\Classes\SettingsManager;
use ZipArchive;

class Import extends \Backend\Classes\Controller
{
    public $requiredPermissions = ['jagu.import.import'];

    private const FILE_NAME = 'theme_zip';
    private const REMOVE_ALL_EXISTING_FILES_SWITCH_NAME = 'remove_all_existing_files';
    private const EXCLUDE_STATIC_CONTENT_SWITCH_NAME = 'replace_static_pages_content';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('Jagu.Import', 'settings');
        $this->pageTitle = Lang::get('jagu.import::lang.plugin.name');
    }

    public function index()
    {
        $this->cleanUpTemp();

        // replace RainLab.Pages files?
        $blacklistFiles = [];
        $blacklistDirs = [];
        if (Input::get(self::EXCLUDE_STATIC_CONTENT_SWITCH_NAME) === null) {
            $blacklistFiles = [
                'meta/static-pages.yaml'
            ];
            $blacklistDirs = [
                'meta/menus',
                'content/static-pages'
            ];
        }

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

            // move uploaded archive to themes/
            Input::file(self::FILE_NAME)->move('themes/', '_tmp.zip');

            $zip = new ZipArchive();

            $extract = $zip->open('themes/_tmp.zip');
            if ($extract !== true) {
                Flash::error(Lang::get('jagu.import::lang.backend.flash.could_not_open_zip'));
                $this->cleanUpTemp();
                return;
            }

            // extract *.zip to themes/_tmp directory
            $zip->extractTo('themes/_tmp');

            $themeDir = array_diff(scandir('themes/_tmp'), ['..', '.']);
            if (\count($themeDir) !== 1) {
                Flash::error(Lang::get('jagu.import::lang.backend.flash.invalid_dir_structure'));
                $this->cleanUpTemp();
                return;
            }
            $themeDir = \array_pop($themeDir);

            // create theme directory if not exists
            if (!file_exists('themes/' . $themeDir)) {
                mkdir('themes/' . $themeDir, 0777, true);
            } else {
                // remove all existing files?
                if (Input::get(self::REMOVE_ALL_EXISTING_FILES_SWITCH_NAME) !== null) {
                    $di = new RecursiveDirectoryIterator('themes/' . $themeDir, FilesystemIterator::SKIP_DOTS);
                    $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
                    foreach ($ri as $file) {
                        $file->isDir() ? rmdir($file) : unlink($file);
                    }
                }
            }

            // copy all files except ones on blacklists
            $directoryIterator = new RecursiveDirectoryIterator('themes/_tmp/' . $themeDir);
            foreach (new RecursiveIteratorIterator($directoryIterator) as $filename => $file) {
                if ($file->getFilename() === '.' || $file->getFilename() === '..') {
                    continue;
                }

                // relative path to file due to theme root directory
                $prefix = 'themes/_tmp/' . $themeDir . '/';
                if (\substr($filename, 0, \strlen($prefix)) == $prefix) {
                    $filename = \substr($filename, \strlen($prefix));
                }

                // is file on blacklist?
                if (in_array($filename, $blacklistFiles)) {
                    continue;
                }

                // is whole dir where file is placed on blacklist?
                foreach ($blacklistDirs as $blacklistDir) {
                    if (\substr($filename, 0, \strlen($blacklistDir)) === $blacklistDir) {
                        continue 2;
                    }
                }

                $oldPath = $file->getRealPath();
                $newPath = 'themes/' . $themeDir . '/' . $filename;

                // copy file
                if (!\is_dir(\dirname($newPath))) {
                    \mkdir(\dirname($newPath), 0777, true);
                }
                File::move($oldPath, $newPath);
            }

            // delete temporary directories
            $this->cleanUpTemp();

            Flash::success(Lang::get('jagu.import::lang.backend.flash.success'));
        }
    }

    private function cleanUpTemp()
    {
        File::delete('themes/_tmp.zip');
        File::deleteDirectory('themes/_tmp');
    }
}
