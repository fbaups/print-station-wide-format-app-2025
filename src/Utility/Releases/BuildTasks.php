<?php


namespace App\Utility\Releases;

use App\Utility\Instances\InstanceTasks;
use arajcany\ToolBox\Utility\TextFormatter;
use arajcany\ToolBox\ZipPackager;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Utility\Inflector;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnableToWriteFile;

/**
 * Class BuildTasks
 *
 * Builds a release ZIP file. Requires the location of a build file.
 * This way, the ZIP can be built via CLI or Web request.
 *
 * @property string $buildFile
 * @property ConsoleIo $io
 * @property Arguments $args
 *
 * @package App\Utility\Release
 */
class BuildTasks
{
    private $buildFile = null;
    private $log = [];
    private $args = null;
    private $io = null;

    /**
     * DefaultApplication constructor.
     *
     * @param null $buildFile
     */
    public function __construct($buildFile = null)
    {
        $this->setBuildFile($buildFile);
        $this->io = new ConsoleIo();
    }

    /**
     * @param mixed $args
     */
    public function setArgs(Arguments $args)
    {
        $this->args = $args;
    }

    /**
     * @param mixed $io
     */
    public function setIo(ConsoleIo $io)
    {
        $this->io = $io;
    }

    /**
     * @return string
     */
    public function getBuildFile(): string
    {
        return $this->buildFile;
    }

    /**
     * @param $buildFile
     */
    public function setBuildFile($buildFile)
    {
        $this->buildFile = $buildFile;
    }

    /**
     * @return array
     */
    public function getLog(): array
    {
        return $this->log;
    }

    /**
     * Write to the log variable
     *
     * @param $data
     * @param string $ioOutput
     */
    public function writeToLog($data, $ioOutput = 'out')
    {
        if (is_object($data)) {
            $data = json_decode(json_encode($data));
        }

        if (PHP_SAPI === 'cli') {
            if ($this->io) {
                $this->io->$ioOutput($data);
            }
        }

        $this->log[] = $data;
    }

    /**
     * @return int
     */
    public function debugOff()
    {
        $contents = file_get_contents(CONFIG . 'app_local.php');
        $contents = str_replace("filter_var(env('DEBUG', true)", "filter_var(env('DEBUG', false)", $contents);
        $result = file_put_contents(CONFIG . 'app_local.php', $contents);
        if ($result) {
            $this->writeToLog("Debugging turned off.");
            return 0;
        } else {
            $this->writeToLog("Failed to turn Debugging turned off.");
            return 1;
        }
    }

    /**
     * @return int
     */
    public function debugOn()
    {
        $contents = file_get_contents(CONFIG . 'app_local.php');
        $contents = str_replace("filter_var(env('DEBUG', false)", "filter_var(env('DEBUG', true)", $contents);
        $result = file_put_contents(CONFIG . 'app_local.php', $contents);
        if ($result) {
            $this->writeToLog("Debugging turned on.");
            return 0;
        } else {
            $this->writeToLog("Failed to turn Debugging turned on.");
            return 1;
        }
    }

    /**
     * Builds the release ZIP file according to the parameters specified in $this->buildFile
     *
     * @param array $options
     * @return bool
     */
    public function build($options = [])
    {
        //check connection to the Remote Update Server
        $RemoteUpdateServer = new RemoteUpdateServer();

        if (empty($RemoteUpdateServer->remote_update_url)) {
            $this->writeToLog(__('Empty value for the Remote Update URL. Have you configured the CONFIG/remote_update.json file?'));
            $this->writeToLog(__('Exiting!'));
            return false;
        }

        $remoteFilesystem = $RemoteUpdateServer->getRemoteUpdateServer();
        if (!$remoteFilesystem) {
            $this->writeToLog(__('Remote Update Server Unavailable. I will not be able to upload this release for people to upgrade.'));
            $proceed = $this->io->askChoice('Do you wish to proceed?', ['Yes', 'No',], 'No');
            if (strtolower($proceed) == 'no') {
                return false;
            }
        }

        $app_name = ucwords(Inflector::variable(APP_NAME));

        $VC = new VersionControl();
        $versionHistoryData = $VC->getVersionHistoryJson();
        $newVersionData = $VC->getDefaultVersionJson();
        $currentTag = $VC->getCurrentVersionTag();

        $sampleMajor = $VC->incrementVersion($currentTag, 'major');
        $sampleMinor = $VC->incrementVersion($currentTag, 'minor');
        $samplePatch = $VC->incrementVersion($currentTag, 'patch');

        $this->writeToLog(__('Major Build => {0}', $sampleMajor));
        $this->writeToLog(__('Minor Build => {0}', $sampleMinor));
        $this->writeToLog(__('Patch Build => {0}', $samplePatch));

        $tagUpgrade = $this->io->askChoice('Is this a Major, Minor or Patch Build?', ['Major', 'Minor', 'Patch'], 'Patch');

        $desc = false;
        while (!$desc) {
            $desc = $this->io->ask('Please type out a description for this release.');
            if (strlen($desc) == 0) {
                $desc = false;
            }
        }

        $codename = $this->io->ask('Please type out a codename for this release (optional).');

        $newVersionData['tag'] = $VC->incrementVersion($currentTag, strtolower($tagUpgrade));
        $newVersionData['desc'] = $desc;
        $newVersionData['codename'] = $codename;

        $VC->putVersionJson($newVersionData);

        $this->writeToLog(__("Building {0} version {1}.", APP_NAME, $newVersionData['tag']));

        $drive = explode(DS, ROOT);
        array_pop($drive);
        $drive = implode(DS, $drive);
        $drive = TextFormatter::makeEndsWith($drive, DS);

        $zipPackager = new ZipPackager();
        $zipPackager->setVerbose(true);

        //----create a file list to zip---------------------------------
        $baseDir = ROOT;

        $GT = new GitTasks();
        //ignore files and folders relative to the ROOT
        $ignoreFilesFolders = [
            ".git\\",
            ".github\\",
            ".idea\\",
            "bin\\ReleaseBuilder.bat",
            "bin\\ComposerCommands.txt",
            "bin\\installer\\",
            "config\\app_datasources.php",
            "config\\app_local.php",
            "config\\Migrations\\notes.txt",
            "config\\Migrations\\schema-dump-default.lock",
            "config\\Stub_DB.sqlite",
            "config\\config_local.php",
            "config\\internal.sqlite",
            "config\\remote_update.json",
            "config\\version_history.json",
            "config\\unitTest.sqlite",
            "logs\\",
            "src\\Command\\DevelopersCommand.php",
            "src\\Command\\PingPongCommand.php",
            "src\\Command\\ReleasesCommand.php",
            "src\\Controller\\Administrators\\DevelopersController.php",
            "src\\Controller\\Administrators\\ReleaseBuilderController.php",
            "src\\Utility\\Releases\\BuildTasks.php",
            "src\\Utility\\Releases\\GitTasks.php",
            "src\\Utility\\Releases\\RemoteUpdateServer.php",
            "src\\Utility\\Releases\\TokenizerTasks.php",
            "templates\\Administrators\\Developers\\",
            "templates\\Administrators\\ReleaseBuilder\\",
            "templates\\Administrators\\element\\sidenav_developer.php",
            "templates\\Administrators\\element\\sidenav_release_builder.php",
            "templates\\Administrators\\plugin\\bake\\",
            "tests\\",
            "tmp\\",

            //Composer files
            "src\\Console\\Installer.php",

            //Remove Code Watcher files
            "config\\Migrations\\20241212032218_CreateCodeWatcher.php",
            "src\\Controller\\Administrators\\CodeWatcherProjectsController.php",
            "tests\\Fixture\\CodeWatcherFilesFixture.php",
            "tests\\Fixture\\CodeWatcherFoldersFixture.php",
            "tests\\Fixture\\CodeWatcherProjectsFixture.php",
            "tests\\TestCase\\Controller\\CodeWatcherProjectsControllerTest.php",
            "tests\\TestCase\\Model\\Table\\CodeWatcherFilesTableTest.php",
            "tests\\TestCase\\Model\\Table\\CodeWatcherFoldersTableTest.php",
            "tests\\TestCase\\Model\\Table\\CodeWatcherProjectsTableTest.php",
            "templates\\Administrators\\CodeWatcherProjects\\",

            //Remove the Foo MVC used to check how masses of data looks in the GUI
            "config\\Migrations\\20220910120050_CreateFooAuthorsRecipes.php",
            "config\\Migrations\\20230725002517_CreateFooRatings.php",
            "src\\Controller\\Administrators\\FooAuthorsController.php",
            "src\\Controller\\Administrators\\FooIngredientsController.php",
            "src\\Controller\\Administrators\\FooMethodsController.php",
            "src\\Controller\\Administrators\\FooRecipesController.php",
            "src\\Controller\\Administrators\\FooTagsController.php",
            "src\\Controller\\Administrators\\FooRatingsController.php",
            "src\\Model\\Entity\\FooAuthor.php",
            "src\\Model\\Entity\\FooIngredient.php",
            "src\\Model\\Entity\\FooMethod.php",
            "src\\Model\\Entity\\FooRecipe.php",
            "src\\Model\\Entity\\FooTag.php",
            "src\\Model\\Table\\FooAuthorsTable.php",
            "src\\Model\\Table\\FooIngredientsTable.php",
            "src\\Model\\Table\\FooMethodsTable.php",
            "src\\Model\\Table\\FooRecipesTable.php",
            "src\\Model\\Table\\FooTagsTable.php",
            "src\\Model\\Table\\FooRatingsTable.php",
            "templates\\Administrators\\FooAuthors\\add.php",
            "templates\\Administrators\\FooAuthors\\edit.php",
            "templates\\Administrators\\FooAuthors\\index.php",
            "templates\\Administrators\\FooAuthors\\view.php",
            "templates\\Administrators\\FooIngredients\\add.php",
            "templates\\Administrators\\FooIngredients\\edit.php",
            "templates\\Administrators\\FooIngredients\\index.php",
            "templates\\Administrators\\FooIngredients\\view.php",
            "templates\\Administrators\\FooMethods\\add.php",
            "templates\\Administrators\\FooMethods\\edit.php",
            "templates\\Administrators\\FooMethods\\index.php",
            "templates\\Administrators\\FooMethods\\view.php",
            "templates\\Administrators\\FooRecipes\\add.php",
            "templates\\Administrators\\FooRecipes\\edit.php",
            "templates\\Administrators\\FooRecipes\\index.php",
            "templates\\Administrators\\FooRecipes\\view.php",
            "templates\\Administrators\\FooTags\\add.php",
            "templates\\Administrators\\FooTags\\edit.php",
            "templates\\Administrators\\FooTags\\index.php",
            "templates\\Administrators\\FooTags\\view.php",
            "templates\\Administrators\\FooRatings\\add.php",
            "templates\\Administrators\\FooRatings\\edit.php",
            "templates\\Administrators\\FooRatings\\index.php",
            "templates\\Administrators\\FooRatings\\view.php",
            "tests\\Fixture\\FooAuthorsFixture.php",
            "tests\\Fixture\\FooIngredientsFixture.php",
            "tests\\Fixture\\FooMethodsFixture.php",
            "tests\\Fixture\\FooRecipesFixture.php",
            "tests\\Fixture\\FooTagsFixture.php",
            "tests\\Fixture\\FooRatingsFixture.php",
            "tests\\TestCase\\Controller\\FooAuthorsControllerTest.php",
            "tests\\TestCase\\Controller\\FooIngredientsControllerTest.php",
            "tests\\TestCase\\Controller\\FooMethodsControllerTest.php",
            "tests\\TestCase\\Controller\\FooRecipesControllerTest.php",
            "tests\\TestCase\\Controller\\FooTagsControllerTest.php",
            "tests\\TestCase\\Controller\\FooRatingsControllerTest.php",
            "tests\\TestCase\\Model\\Table\\FooAuthorsTableTest.php",
            "tests\\TestCase\\Model\\Table\\FooIngredientsTableTest.php",
            "tests\\TestCase\\Model\\Table\\FooMethodsTableTest.php",
            "tests\\TestCase\\Model\\Table\\FooRecipesTableTest.php",
            "tests\\TestCase\\Model\\Table\\FooTagsTableTest.php",
            "tests\\TestCase\\Model\\Table\\FooRatingsTableTest.php",
        ];

        $gitIgnored = $GT->getIgnoredFiles();
        if ($gitIgnored) {
            $ignoreFilesFolders = array_merge($ignoreFilesFolders, $gitIgnored);
        }

        $rawFileList = $zipPackager->rawFileList($baseDir);
        $rawFileList = $zipPackager->filterOutFoldersAndFiles($rawFileList, $ignoreFilesFolders);
        $rawFileList = $zipPackager->filterOutVendorExtras($rawFileList);

        //filter out generic file names
        $genericFileName = [
            ".dockerignore",
            ".editorconfig",
            ".env.example",
            ".gitattributes",
            ".gitignore",
            ".gitkeep",
            ".MD",
            ".md",
            ".phpunit.result.cache",
            ".travis.yml",
            "composer.json",
            "composer.lock",
            "docker-compose.yml",
            "Dockerfile",
            "docs.Dockerfile",
            "empty",
            "LICENSE",
            "LICENSE.txt",
            "phpcs.xml",
            "phpstan-baseline.neon",
            "phpstan.neon",
            "phpstan.neon.dist",
            "phpunit.xml",
            "phpunit.xml.dist",
            "psalm-baseline.xml",
            "psalm.xml",
            "README",
            "README.md",
            "TODO",
            "TODO.md",
            "VERSION",
            "VERSION.txt",
        ];
        $rawFileList = $zipPackager->filterOutByFileName($rawFileList, $genericFileName);


        $zipList = $zipPackager->convertRawFileListToZipList($rawFileList, $baseDir, $app_name);

        $zipList[] = [
            'external' => CONFIG . "internal.sqlite",
            'internal' => "$app_name/config/internal.sqlite"
        ];
        $zipList[] = [
            'external' => CONFIG . "version.json",
            'internal' => "$app_name/config/version.json"
        ];
        $zipList[] = [
            'external' => CONFIG . "empty",
            'internal' => "$app_name/logs/empty"
        ];
        $zipList[] = [
            'external' => CONFIG . "empty",
            'internal' => "$app_name/tmp/empty"
        ];
        $zipList[] = [
            'external' => CONFIG . "empty",
            'internal' => "$app_name/tmp/sessions/empty"
        ];
        $zipList[] = [
            'external' => CONFIG . "empty",
            'internal' => "$app_name/tmp/cache/empty"
        ];
        $zipList[] = [
            'external' => CONFIG . "empty",
            'internal' => "$app_name/tmp/configure.txt"
        ];
        $zipList[] = [
            'external' => CONFIG . "empty",
            'internal' => "$app_name/tmp/cache/clear_all.txt"
        ];
        $zipList[] = [
            'external' => ROOT . "/vendor/cakephp/cakephp/VERSION.txt",
            'internal' => "$app_name/vendor/cakephp/cakephp/VERSION.txt"
        ];
        //------------------------------------------------------------------------

        //----update remote server url--------------------------------------------
        $remoteUpdateUrl = TextFormatter::makeDirectoryTrailingForwardSlash($RemoteUpdateServer->remote_update_url);
        if ($remoteUpdateUrl) {
            foreach ($zipList as $file) {
                if (TextFormatter::endsWith($file['external'], "_SeedSettingsRemoteUpdate.php")) {
                    $seederFile = $file['external'];
                    $seederContentsOriginal = file_get_contents($seederFile);
                    $seederIn = "'property_value' => 'http://localhost/update/'";
                    $seederOut = "'property_value' => '{$remoteUpdateUrl}'";
                    $seederContentsNew = str_replace($seederIn, $seederOut, $seederContentsOriginal);
                    file_put_contents($seederFile, $seederContentsNew);
                }
            }
        }
        //------------------------------------------------------------------------

        //----apply code replacements---------------------------------------------
        $this->applyCodeReplacements();
        //------------------------------------------------------------------------

        //----strip comments------------------------------------------------------
        $TokenizerTasks = new TokenizerTasks();
        $tmpDir = TMP . "_" . mt_rand(11111, 99999) . "_tokenizer/";
        @mkdir($tmpDir, 0777, true);
        $zipList = $TokenizerTasks->removeCommentsFromZipList($zipList, $tmpDir);
        //------------------------------------------------------------------------

        //----roll back update remote server url-----------------------------------
        if ($remoteUpdateUrl) {
            if (isset($seederFile) && isset($seederContentsOriginal)) {
                file_put_contents($seederFile, $seederContentsOriginal);
            }
        }
        //------------------------------------------------------------------------

        //----create the required zip files---------------------------------
        $this->writeToLog(__('Zipping files to {0}', $drive));
        $date = date('Ymd_His');
        $zipFileName = str_replace(" ", "_", "{$date}_{$app_name}_v{$newVersionData['tag']}.zip");
        $zipFullPath = "{$drive}{$zipFileName}";
        $zipResult = $zipPackager->makeZipFromZipList($zipFullPath, $zipList);
        if ($zipResult) {
            $this->writeToLog(__('Created {0}', $zipFullPath));
            $return = true;
        } else {
            $this->writeToLog(__('Could not create {0}', $zipFullPath));
            $return = false;
        }
        //------------------------------------------------------------------------

        //----remove tokenized files----------------------------------------------
        $adapter = new LocalFilesystemAdapter($tmpDir);
        $tmpFileSystem = new Filesystem($adapter);
        $path = '';
        try {
            $tmpFileSystem->deleteDirectory($path);
            $this->writeToLog(__('Deleted TMP dir {0}', $tmpDir));
        } catch (\Throwable $exception) {
            $this->writeToLog(__('Unable to delete TMP dir {0}', $tmpDir));
        }
        //------------------------------------------------------------------------

        //----save version history---------------------------------
        if ($remoteFilesystem) {
            $newVersionData['installer_url'] = $RemoteUpdateServer->remote_update_url . $zipFileName;
            $newVersionData['release_date'] = $date;
            $versionHistoryData[] = $newVersionData;
            $VC->putVersionHistoryJson($versionHistoryData);
            $versionHistoryHashData = $VC->getVersionHistoryHashtxt();

            $this->writeToLog(__('Uploading Version History Hash to the Remote Update Server.'));
            try {
                $remoteFilesystem->write("version_history_hash.txt", $versionHistoryHashData);
                $this->writeToLog("Version History Hash successfully uploaded to the Remote Update Server.");
            } catch (FilesystemException|UnableToWriteFile $exception) {
                $this->writeToLog("Could not upload the Version History Hash to the Remote Update Server.");
            }
        } else {
            $this->writeToLog(__("No automatic uploading of Version History Hash to the Remote Update Server."));
        }
        //------------------------------------------------------------------------

        //----automatic upload to remote update site---------------------------------
        if ($remoteFilesystem) {
            $this->writeToLog(__('Uploading {0} to the Remote Update Server.', $zipFileName));
            try {
                $remoteFilesystem->write($zipFileName, file_get_contents($zipFullPath));
                $isWritten = true;
                $this->writeToLog("Zip file successfully uploaded to the Remote Update Server.");
            } catch (FilesystemException|UnableToWriteFile $exception) {
                $isWritten = false;
                $this->writeToLog("Could not upload the Zip file to the Remote Update Server.");
            }

            if ($isWritten) {
                $deleteSource = $this->io->askChoice('Delete source Zip?', ['Yes', 'No',], 'No');
                if (strtolower($deleteSource) == 'yes') {
                    unlink($zipFullPath);
                }
            }

            //push the links file
            $this->pushDownloadLinksFile();

        } else {
            $this->writeToLog(__("No automatic uploading of Zip to the Remote Update Server."));
        }
        //------------------------------------------------------------------------

        return $return;
    }

    /**
     * Last resort function to fix Vendor code known issues
     */
    function applyCodeReplacements(): void
    {
        $file = ROOT . "\\path\\to\\file.php";
        if (is_file($file)) {
            $contents = file_get_contents($file);
            $contents = str_replace('input', 'output', $contents);
            file_put_contents($file, $contents);
        }
    }

    /**
     * @return bool
     */
    public function makeReleaseBuilderBatFile(): bool
    {
        $InstanceTasks = new InstanceTasks();
        $resultPhpBinary = $InstanceTasks->getPhpBinary();
        $resultComposerBinary = $InstanceTasks->getComposerBinary();

        $batTemplatePath = ROOT . DS . "bin\\ReleaseBuilder.bat.txt";
        $batFinalPath = ROOT . DS . "bin\\ReleaseBuilder.bat";

        $batTemplateContents = @file_get_contents($batTemplatePath);
        $batFinalContents = @file_get_contents($batFinalPath);

        if ($resultPhpBinary && $resultComposerBinary) {
            $batTemplateContents = str_replace("\r\n", "\n", $batTemplateContents);
            $batTemplateContents = str_replace("\r", "\n", $batTemplateContents);
            $batTemplateContents = explode("\n", $batTemplateContents);

            foreach ($batTemplateContents as $i => $line) {
                if (str_starts_with($line, "SET php_path=")) {
                    $batTemplateContents[$i] = "SET php_path=\"$resultPhpBinary\"";
                }
                if (str_starts_with($line, "SET composer_path=")) {
                    $batTemplateContents[$i] = "SET composer_path=\"$resultComposerBinary\"";
                }
                if (str_starts_with($line, ":: ::")) {
                    unset($batTemplateContents[$i]);
                }
            }
            $batTemplateContents = implode("\n", $batTemplateContents);

            if ($batTemplateContents !== $batFinalContents) {
                file_put_contents($batFinalPath, $batTemplateContents);
            }
        }

        return is_file($batFinalPath);
    }


    /**
     * @return bool
     */
    function pushDownloadLinksFile(): bool
    {
        $RemoteUpdateServer = new RemoteUpdateServer();

        $downloadLinksFile = ROOT . "/bin/ReleaseBuilderDownload.html";
        if (!is_file($downloadLinksFile)) {
            $this->writeToLog('Base HTML links template file is missing!');
            $this->writeToLog('Exiting!');
            return false;
        }

        $versionHistory = json_decode(file_get_contents(CONFIG . 'version_history.json'), JSON_OBJECT_AS_ARRAY);
        $currentVersion = array_pop($versionHistory);

        $replacementInput = [
            '{{APP_NAME}}',
            '{{INSTALLER_URL}}',
        ];
        $replacementOutput = [
            APP_NAME,
            $currentVersion['installer_url'],
        ];

        if (empty($RemoteUpdateServer->remote_update_url)) {
            $this->writeToLog('Empty value for the Remote Update URL. Have you configured the CONFIG/remote_update.json file?');
            $this->writeToLog('Exiting!');
            return false;
        }

        $remoteFilesystem = $RemoteUpdateServer->getRemoteUpdateServer();
        if (!$remoteFilesystem) {
            $this->writeToLog('Remote Update Server Unavailable. I will not be able to push the HTML links file.');
            $this->writeToLog('Exiting!');
            return false;
        }

        $this->writeToLog('Pushing the HTML links file to the Remote Update Server.');
        try {
            $fileContents = file_get_contents($downloadLinksFile);
            $fileContents = str_replace($replacementInput, $replacementOutput, $fileContents);
            $fileName = "download.html";
            $fileSubPath = "/{$fileName}";
            $remoteFilesystem->write($fileSubPath, $fileContents);
            $this->writeToLog("HTML links file successfully uploaded to the Remote Update Server.");
            $this->writeToLog("{$RemoteUpdateServer->remote_update_url}{$fileName}");
        } catch (\Throwable $exception) {
            $this->writeToLog("Could not upload the HTML links file to the Remote Update Server.");
            $this->writeToLog($exception->getMessage());
        }

        return true;
    }

}
