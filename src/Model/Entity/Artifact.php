<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\Table\ArtifactMetadataTable;
use App\Model\Table\ArtifactsTable;
use arajcany\ToolBox\Utility\TextFormatter;
use Cake\Core\Configure;
use Cake\I18n\DateTime;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use League\CLImate\TerminalObject\Basic\Tab;

/**
 * Artifact Entity
 *
 * @property int $id
 * @property DateTime|null $created
 * @property DateTime|null $modified
 * @property string|null $name
 * @property string|null $description
 * @property int|null $size
 * @property string|null $mime_type
 * @property DateTime|null $activation
 * @property DateTime|null $expiration
 * @property bool|null $auto_delete
 * @property string|null $token
 * @property string|null $url
 * @property string|null $unc
 * @property string|null $hash_sum
 * @property string|null $grouping
 *
 * Alias properties
 * @property string|null $full_url
 * @property string|null $full_unc
 * @property string|null $sample_url_icon
 * @property string|null $sample_url_thumbnail
 * @property string|null $sample_url_preview
 * @property string|null $sample_url_lr
 * @property string|null $sample_url_mr
 * @property string|null $sample_url_hr
 * @property string|null $sample_unc_icon
 * @property string|null $sample_unc_thumbnail
 * @property string|null $sample_unc_preview
 * @property string|null $sample_unc_lr
 * @property string|null $sample_unc_mr
 * @property string|null $sample_unc_hr
 * @property array|null $light_table_urls array of where to get light-table images
 * @property array|null $light_table_uncs array of where to get light-table images via UNC
 * @property array|null $sample_urls array of where to get sample images
 *
 * @property \App\Model\Entity\ArtifactMetadata $artifact_metadata
 */
class Artifact extends Entity
{
    /*
     * Along with the uploaded Artifact there are sample sizes that
     * are auto-generated based on the Settings:
     *      repo_size_icon
     *      repo_size_thumbnail
     *      repo_size_preview
     *      repo_size_lr
     *      repo_size_mr
     *      repo_size_hr
     *
     * You can access the URLs / UNCs via the functions:
     *      full_url (the uploaded Artifact)
     *      full_unc (the uploaded Artifact)
     *
     *      sample_url_icon
     *      sample_url_thumbnail
     *      sample_url_preview
     *      sample_url_lr
     *      sample_url_mr
     *      sample_url_hr
     *
     *      sample_unc_icon
     *      sample_unc_thumbnail
     *      sample_unc_preview
     *      sample_unc_lr
     *      sample_unc_mr
     *      sample_unc_hr
     */

    private string $repoUrl;
    private string $repoUnc;
    private string $repoMode;

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'created' => true,
        'modified' => true,
        'name' => true,
        'description' => true,
        'size' => true,
        'mime_type' => true,
        'activation' => true,
        'expiration' => true,
        'auto_delete' => true,
        'token' => true,
        'url' => true,
        'unc' => true,
        'hash_sum' => true,
        'grouping' => true,
        'artifact_metadata' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array<string>
     */
    protected array $_hidden = [
        'token',
    ];

    private array|null $lightTableUrlsCache = null;
    private array|null $sampleUrlsCache = null;

    private array|null $lightTableUncsCache = null;

    private bool|null $doAllSampleImagesExist = null;
    private bool|null $doAllLightTableImagesExist = null;

    /**
     * Need this because __constructor() method causes an error
     * Call this manually with each method
     *
     * @return void
     */
    protected function initializeProperties(): void
    {
        if (empty($this->repoUnc)) {
            $this->repoUnc = Configure::read('Settings.repo_unc');
        }
        if (empty($this->repoUrl)) {
            $this->repoUrl = Configure::read('Settings.repo_url');
        }
        if (empty($this->repoMode)) {
            $this->repoMode = Configure::read('Settings.repo_mode');
        }
    }


    public function getRepoMode(): string
    {
        return $this->repoMode;
    }


    public function setRepoModeAsDynamic()
    {
        $this->repoMode = 'dynamic';
    }


    public function setRepoModeAsStatic()
    {
        $this->repoMode = 'static';
    }


    /**
     * Wrapper function to create sample images for next time
     * @return void
     */
    public function createSampleSizesErrand(): void
    {
        /** @var ArtifactsTable $Artifacts */
        $Artifacts = TableRegistry::getTableLocator()->get('Artifacts');
        $Artifacts->createSampleSizesErrand($this->id);
    }


    /**
     * Wrapper function to create sample images for next time
     * @return void
     */
    public function createLightTableImagesErrand(): void
    {
        /** @var ArtifactsTable $Artifacts */
        $Artifacts = TableRegistry::getTableLocator()->get('Artifacts');
        $Artifacts->createLightTableImagesErrand($this->id);
    }

    public function doAllSampleImagesExist(): ?bool
    {
        if ($this->doAllSampleImagesExist === null) {
            $this->_getSampleUrls();
        }

        return $this->doAllSampleImagesExist;
    }


    public function doAllLightTableImagesExist(): ?bool
    {
        if ($this->doAllLightTableImagesExist === null) {
            $this->_getLightTableUrls();
        }

        return $this->doAllLightTableImagesExist;
    }


    /*
     * *******************************************************
     * The functions below generate URL paths to the Artifacts
     * *******************************************************
     */

    /**
     * Get the Full URL
     *
     * @return string
     */
    protected function _getFullUrl()
    {
        $this->initializeProperties();

        //auto switching between static and dynamic url based on Setting
        if ($this->repoMode === 'static') {
            $str = TextFormatter::makeEndsWith($this->repoUrl, "/") . $this->url . $this->name;
        } else {
            $url = ['prefix' => false, 'controller' => 'ConnectorArtifacts', 'action' => 'fetch', $this->token, $this->name];
            $str = Router::url($url, true);
        }

        return trim($str);
    }


    /*
     * ****************************************************************************
     * The function below generates URL paths to the Light Table & Sample Artifacts
     * ****************************************************************************
     */

    /**
     * Array of URLS that are validated.
     * Simple caching mechanism to save on DB hits and file path checking
     *
     * @return array
     */
    protected function _getLightTableUrls(): array
    {
        $this->initializeProperties();

        if (isset($this->lightTableUrlsCache[$this->repoMode]) && is_array($this->lightTableUrlsCache[$this->repoMode])) {
            return $this->lightTableUrlsCache[$this->repoMode];
        }

        /** @var ArtifactMetadataTable $ArtifactMetadata */
        $ArtifactMetadata = TableRegistry::getTableLocator()->get('ArtifactMetadata');

        /** @var ArtifactMetadata $am */
        $am = $ArtifactMetadata->find('all')->where(['artifact_id' => $this->id])->first();
        if (empty($am)) {
            return [];
        }

        $pp = $am->exif['pages']['length'];
        if (empty($pp)) {
            return [];
        }
        $pp = intval($pp);

        $pageRange = range(1, $pp);

        $urls = [];
        $uncCounter = 0;
        foreach ($pageRange as $pageNumber) {
            $name = pathinfo($this->name);
            $name = "{$name['filename']}_{$pageNumber}.png";

            //unc check if file exists
            $uncPath = TextFormatter::makeDirectoryTrailingSmartSlash($this->repoUnc)
                . TextFormatter::makeDirectoryTrailingSmartSlash("{$this->unc}light-table")
                . $name;

            if (is_file($uncPath)) {
                //auto switching between static and dynamic url based on Setting
                if ($this->repoMode === 'static') {
                    $urls[$pageNumber] = TextFormatter::makeEndsWith($this->repoUrl, "/") . $this->url . "light-table/" . $name;
                } else {
                    $url = ['prefix' => false, 'controller' => 'ConnectorArtifacts', 'action' => 'light-table', $this->token, $pageNumber, $name];
                    $urls[$pageNumber] = Router::url($url, true);;
                }

                $uncCounter++;
            }
        }

        if ($uncCounter === $pp) {
            $this->doAllLightTableImagesExist = true;
        } else {
            $this->doAllLightTableImagesExist = false;
        }

        $this->lightTableUrlsCache[$this->repoMode] = $urls;

        return $urls;
    }

    /**
     * Array of URLS that are validated.
     * Simple caching mechanism to save on DB hits and file path checking
     *
     * @return array
     */
    protected function _getLightTableUncs(): array
    {
        $this->initializeProperties();

        if (isset($this->lightTableUncsCache) && is_array($this->lightTableUncsCache)) {
            return $this->lightTableUncsCache;
        }

        /** @var ArtifactMetadataTable $ArtifactMetadata */
        $ArtifactMetadata = TableRegistry::getTableLocator()->get('ArtifactMetadata');

        /** @var ArtifactMetadata $am */
        $am = $ArtifactMetadata->find('all')->where(['artifact_id' => $this->id])->first();
        if (empty($am)) {
            return [];
        }

        $pp = $am->exif['pages']['length'];
        if (empty($pp)) {
            return [];
        }
        $pp = intval($pp);

        $pageRange = range(1, $pp);

        $uncs = [];
        $uncCounter = 0;
        foreach ($pageRange as $pageNumber) {
            $name = pathinfo($this->name);
            $name = "{$name['filename']}_{$pageNumber}.png";

            //unc check if file exists
            $uncPath = TextFormatter::makeDirectoryTrailingSmartSlash($this->repoUnc)
                . TextFormatter::makeDirectoryTrailingSmartSlash("{$this->unc}light-table")
                . $name;

            if (is_file($uncPath)) {
                $uncs[$pageNumber] = $uncPath;
                $uncCounter++;
            }
        }

        if ($uncCounter === $pp) {
            $this->doAllLightTableImagesExist = true;
        } else {
            $this->doAllLightTableImagesExist = false;
        }

        $this->lightTableUncsCache = $uncs;

        return $uncs;
    }


    /**
     * Array of URLS that are validated.
     * Simple caching mechanism to save on DB hits and file path checking
     *
     * @return array
     */
    protected function _getSampleUrls(): array
    {
        $this->initializeProperties();

        if (is_array($this->sampleUrlsCache)) {
            return $this->sampleUrlsCache;
        }

        $sizes = [
            'Icon',
            'Thumbnail',
            'Preview',
            'Lr',
            'Mr',
            'Hr',
        ];

        $urls = [];

        $uncCounter = 0;

        foreach ($sizes as $size) {
            $functionNameUnc = "_getSampleUnc$size";
            $functionNameUrl = "_getSampleUrl$size";

            $imgUnc = $this->$functionNameUnc();
            $imgUrl = $this->$functionNameUrl();

            if (is_file($imgUnc)) {
                $urls[] = $imgUrl;
                $uncCounter++;
            }
        }

        if ($uncCounter === count($sizes)) {
            $this->doAllSampleImagesExist = true;
        } else {
            $this->doAllSampleImagesExist = false;
        }

        return $urls;
    }

    /**
     * @param string $sizeName
     * @return string
     */
    private function generateSampleUrl(string $sizeName = 'thumbnail'): string
    {
        $this->initializeProperties();

        $fileNameNoExt = pathinfo($this->name, PATHINFO_FILENAME);
        $fileExt = pathinfo($this->name, PATHINFO_EXTENSION);

        //PDF files will always be converted to PNG
        if (strtolower($fileExt) === 'pdf') {
            $fileExt = 'png';
        }

        //Video files will be converted to jpg
        /** @var ArtifactsTable $Artifacts */
        $Artifacts = TableRegistry::getTableLocator()->get('Artifacts');
        if (in_array($this->mime_type, $Artifacts->getVideoMimeTypes())) {
            $fileExt = 'jpg';
        }

        //auto switching between static and dynamic url based on Setting
        if ($this->repoMode === 'static') {
            $str = TextFormatter::makeEndsWith($this->repoUrl, "/") . $this->url . "samples/" . "{$fileNameNoExt}_{$sizeName}.{$fileExt}";
        } else {
            $url = ['prefix' => false, 'controller' => 'ConnectorArtifacts', 'action' => 'sample', $this->token, $sizeName, $this->name];
            $str = Router::url($url, true);
        }

        return trim($str);
    }

    protected function _getSampleUrlIcon(): string
    {
        return $this->generateSampleUrl('icon');
    }

    protected function _getSampleUrlThumbnail(): string
    {
        return $this->generateSampleUrl('thumbnail');
    }

    protected function _getSampleUrlPreview(): string
    {
        return $this->generateSampleUrl('preview');
    }

    protected function _getSampleUrlLr(): string
    {
        return $this->generateSampleUrl('lr');
    }

    protected function _getSampleUrlMr(): string
    {
        return $this->generateSampleUrl('mr');
    }

    protected function _getSampleUrlHr(): string
    {
        return $this->generateSampleUrl('hr');
    }




    /*
     * *******************************************************
     * The functions below generate UNC paths to the Artifacts
     * *******************************************************
     */

    /**
     * Get the Full UNC
     *
     * @return string
     */
    protected function _getFullUnc()
    {
        $this->initializeProperties();

        $str = TextFormatter::makeDirectoryTrailingSmartSlash($this->repoUnc) . $this->unc . $this->name;
        return trim($str);
    }

    /**
     * @param string $sizeName
     * @return string
     */
    private function generateSampleUnc(string $sizeName = 'thumbnail'): string
    {
        $this->initializeProperties();

        $fileNameNoExt = pathinfo($this->name, PATHINFO_FILENAME);
        $fileExt = pathinfo($this->name, PATHINFO_EXTENSION);

        //PDF files will always be converted to PNG
        if (strtolower($fileExt) === 'pdf') {
            $fileExt = 'png';
        }

        //Video files will be converted to jpg
        /** @var ArtifactsTable $Artifacts */
        $Artifacts = TableRegistry::getTableLocator()->get('Artifacts');
        if (in_array($this->mime_type, $Artifacts->getVideoMimeTypes())) {
            $fileExt = 'jpg';
        }

        $str = TextFormatter::makeDirectoryTrailingSmartSlash($this->repoUnc)
            . TextFormatter::makeDirectoryTrailingSmartSlash("{$this->unc}samples")
            . "{$fileNameNoExt}_{$sizeName}.{$fileExt}";
        return trim($str);
    }

    protected function _getSampleUncIcon(): string
    {
        return $this->generateSampleUnc('icon');
    }

    protected function _getSampleUncThumbnail(): string
    {
        return $this->generateSampleUnc('thumbnail');
    }

    protected function _getSampleUncPreview(): string
    {
        return $this->generateSampleUnc('preview');
    }

    protected function _getSampleUncLr(): string
    {
        return $this->generateSampleUnc('lr');
    }

    protected function _getSampleUncMr(): string
    {
        return $this->generateSampleUnc('mr');
    }

    protected function _getSampleUncHr(): string
    {
        return $this->generateSampleUnc('hr');
    }


    public function isPdf(): bool
    {
        /** @var ArtifactsTable $Artifacts */
        $Artifacts = TableRegistry::getTableLocator()->get('Artifacts');

        return $Artifacts->isPdf($this);
    }

    public function isImage(): bool
    {
        /** @var ArtifactsTable $Artifacts */
        $Artifacts = TableRegistry::getTableLocator()->get('Artifacts');

        return $Artifacts->isImage($this);
    }

    public function isVideo(): bool
    {
        /** @var ArtifactsTable $Artifacts */
        $Artifacts = TableRegistry::getTableLocator()->get('Artifacts');

        return $Artifacts->isVideo($this);
    }

    public function isAudio(): bool
    {
        /** @var ArtifactsTable $Artifacts */
        $Artifacts = TableRegistry::getTableLocator()->get('Artifacts');

        return $Artifacts->isAudio($this);
    }

}
