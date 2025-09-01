<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Artifact;
use App\Model\Entity\ArtifactMetadata;
use App\Model\Entity\Errand;
use arajcany\PrePressTricks\Graphics\Common\GetCommands;
use arajcany\ToolBox\Flysystem\Adapters\LocalFilesystemAdapter;
use arajcany\ToolBox\I18n\TimeMaker;
use arajcany\ToolBox\Utility\Security\Security;
use arajcany\ToolBox\Utility\TextFormatter;
use Cake\Core\Configure;
use Cake\Database\Driver\Sqlite;
use Cake\Datasource\EntityInterface;
use Cake\I18n\DateTime;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use League\Flysystem\Filesystem;
use League\Flysystem\StorageAttributes;
use League\MimeTypeDetection\FinfoMimeTypeDetector;

/**
 * Artifacts Model
 *
 * @property \App\Model\Table\ArtifactMetadataTable&\Cake\ORM\Association\HasMany $ArtifactMetadata
 *
 * @method \App\Model\Entity\Artifact newEmptyEntity()
 * @method \App\Model\Entity\Artifact newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Artifact[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Artifact get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Artifact findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Artifact patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Artifact[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Artifact|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Artifact saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Artifact[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Artifact[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Artifact[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Artifact[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ArtifactsTable extends AppTable
{
    private string $repoUnc;
    private string $repoUncTmpInput;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('artifacts');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasOne('ArtifactMetadata', [
            'foreignKey' => 'artifact_id',
        ]);

        $this->initializeSchemaJsonFields($this->getJsonFields());

        $this->repoUnc = TextFormatter::makeDirectoryTrailingSmartSlash(Configure::read('Settings.repo_unc'));
        $this->repoUncTmpInput = TextFormatter::makeDirectoryTrailingSmartSlash($this->repoUnc . "_InputTemp");
        if (is_dir($this->repoUnc) && !is_dir($this->repoUncTmpInput)) {
            mkdir($this->repoUncTmpInput);
        }
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->allowEmptyString('name');

        $validator
            ->scalar('description')
            ->maxLength('description', 1024)
            ->allowEmptyString('description');

        $validator
            ->integer('size')
            ->allowEmptyString('size');

        $validator
            ->scalar('mime_type')
            ->maxLength('mime_type', 50)
            ->allowEmptyString('mime_type');

        $validator
            ->dateTime('activation')
            ->allowEmptyDateTime('activation');

        $validator
            ->dateTime('expiration')
            ->allowEmptyDateTime('expiration');

        $validator
            ->boolean('auto_delete')
            ->allowEmptyString('auto_delete');

        $validator
            ->scalar('token')
            ->maxLength('token', 50)
            ->allowEmptyString('token');

        $validator
            ->scalar('url')
            ->maxLength('url', 2048)
            ->allowEmptyString('url');

        $validator
            ->scalar('unc')
            ->maxLength('unc', 2048)
            ->allowEmptyString('unc');

        $validator
            ->scalar('hash_sum')
            ->maxLength('hash_sum', 50)
            ->allowEmptyString('hash_sum');

        $validator
            ->scalar('grouping')
            ->maxLength('grouping', 50)
            ->allowEmptyString('grouping');

        return $validator;
    }

    /**
     * List of properties that can be JSON encoded
     *
     * @return array
     */
    public function getJsonFields(): array
    {
        $jsonFields = [];

        return $jsonFields;
    }

    /**
     * @return string
     */
    public function getRepoUnc(): string
    {
        return $this->repoUnc;
    }

    /**
     * @return string
     */
    public function getRepoUncTmpInput(): string
    {
        return $this->repoUncTmpInput;
    }

    /**
     * Workhorse function to create an Artifact.
     *
     * $data follows the entity format of Artifact
     * $data['tmp_name'] can hold a file path, WARNING the file will be moved from tmp_name to the final UNC path
     * $data['blob'] blob data e.g. file_get_contents()
     *
     * Some basic precedence rules
     *  1) 'tmp_name' data overrides 'blob' data
     *  2) 'mime_type' is always determined from the contents, we never trust passed in data
     *  3) 'size' is always determined from the contents, we never trust passed in data
     *
     * @param array $data
     * @return false|Artifact
     */
    public function createArtifact(array $data): false|Artifact
    {
        $artifactIsNew = false;

        //check if Artifact already exists
        if (!empty($data['token'])) {
            $artifact = $this->find('all')->where(['token' => $data['token']])->first();
            if (!$artifact) {
                $artifact = $this->newEmptyEntity();
                $artifactIsNew = true;
            }
        } else {
            $artifact = $this->newEmptyEntity();
            $artifactIsNew = true;
        }

        $defaultData = $this->getDefaultData();
        $data = array_merge($defaultData, $data);

        //fix up activation
        if (is_array($data['activation'])) {
            $data['activation'] = TimeMaker::makeFrozenTimeFromUnknown($data['activation'], LCL_TZ, 'UTC');
        }

        //fix up expiration
        if (is_array($data['expiration'])) {
            $data['expiration'] = TimeMaker::makeFrozenTimeFromUnknown($data['expiration'], LCL_TZ, 'UTC');
        }

        //fix up token - unc and url are based on token
        if ($artifactIsNew) {
            if (is_string($data['token']) && strlen($data['token']) >= 40) {
                $token = $data['token'];
            } elseif (is_string($data['token']) && strlen($data['token']) < 40) {
                $token = sha1($data['token'] . Security::randomBytes(1600));
            } else {
                $token = sha1(Security::randomBytes(1600));
            }
            $chunks = $this->str_split_random($token, 2, 3);
            $url = implode('/', $chunks) . '/';
            $unc = implode('\\', $chunks) . '\\';

            $data['token'] = $token;
            $data['url'] = $url;
            $data['unc'] = $unc;
        }

        //create a directory for the Artifact
        $dir = $this->repoUnc . $data['unc'];
        if (!is_dir($dir)) {
            $this->addInfoAlerts("Creating a directory for the Artifact.");
            if (mkdir($dir, 0777, true)) {
                $this->addInfoAlerts("Creating a directory for the Artifact.");
                $createDirectoryResult = true;
            } else {
                $this->addDangerAlerts("Failed to create the directory for the Artifact.");
                $createDirectoryResult = false;
            }
        } else {
            $this->addInfoAlerts("Directory for the Artifact already exists.");
            $createDirectoryResult = true;
        }

        if (!$createDirectoryResult) {
            return false;
        }

        //make sure the file name has been sanitized
        $data['name'] = $this->sanitizeFilename($data['name']);

        //save the data contained in either $data['blob'] or $data['tmp_name']
        if (isset($data['tmp_name']) && !empty($data['tmp_name'])) {
            $src = $data['tmp_name'];
            $dest = $dir . $data['name'];
            $saveDataResult = rename($src, $dest);
            if ($saveDataResult) {
                $this->addInfoAlerts("The file was moved to the destination folder.");
            } else {
                $this->addDangerAlerts("Failed to move the file to the destination folder.");
            }
        } elseif (isset($data['blob']) && !empty($data['blob'])) {
            $src = $data['blob'];
            $dest = $dir . $data['name'];
            $saveDataResult = file_put_contents($dest, $src);
            if ($saveDataResult) {
                $this->addInfoAlerts("The file was saved to the destination folder.");
            } else {
                $this->addDangerAlerts("Failed to save the file to the destination folder.");
            }
        } else {
            //no blob data to save
            $this->addDangerAlerts("No blob data to save.");
            $saveDataResult = false;
            $dest = null;
        }

        if (!$saveDataResult) {
            //todo delete folder structure we created
            return false;
        }

        //get the hash sum
        $hashSumFile = $this->sha1LargeFiles($dest);
        $data['hash_sum'] = $hashSumFile;

        //get the mime type amd size
        if (is_file($dest)) {
            $detector = new FinfoMimeTypeDetector();
            $data['mime_type'] = $detector->detectMimeTypeFromFile($dest);
            $data['size'] = filesize($dest);
        }

        //save the Entity, only if blob was saved
        $data = array_filter($data);
        $artifact = $this->patchEntity($artifact, $data);
        $saveEntityResult = $this->save($artifact);
        if ($saveEntityResult) {
            $this->addInfoAlerts("Artifact entity saved.");
        } else {
            //todo delete folder structure we created
            $this->addDangerAlerts("Artifact entity could not be saved.");
            return false;
        }

        //save ArtifactMetadata
        $artifactMetadata = $this->ArtifactMetadata->newEmptyEntity();
        $artifactMetadata->artifact_id = $artifact->id;

        $imageMimeTypes = $this->getImageMimeTypes();
        if (in_array($artifact->mime_type, $imageMimeTypes)) {
            $exif = $this->imageExif($dest);
            $artifactMetadata->exif = $exif['exif'];
            $artifactMetadata->width = $exif['width'];
            $artifactMetadata->height = $exif['width'];
        }

        $pdfMimeTypes = $this->getPdfMimeTypes();
        if (in_array($artifact->mime_type, $pdfMimeTypes)) {
            $report = $this->pdfReport($dest);
            $artifactMetadata->exif = $report;
            $artifactMetadata->width = 0;
            $artifactMetadata->height = 0;
        }

        $saveMetadataEntityResult = $this->ArtifactMetadata->save($artifactMetadata);
        if ($saveMetadataEntityResult) {
            $this->addInfoAlerts("Artifact metadata entity saved.");
        } else {
            $this->addDangerAlerts("Artifact metadata entity could not be saved.");
            return false;
        }

        //generate sample images
        $this->createSampleSizesErrand($artifact->id);

        //generate light table images
        $this->createLightTableImagesErrand($artifact->id);

        //return the Artifact
        return $artifact;
    }

    /**
     * General sanitizer for URL and FSO file names
     *
     * @param mixed $dirty
     * @param string $replacement
     * @return array|mixed|string|string[]
     */
    public function sanitizeFilename(mixed $dirty, string $replacement = ' '): mixed
    {
        if ($dirty === null || is_bool($dirty)) {
            return '';
        }

        if (!is_string($dirty)) {
            return $dirty;
        }

        $clean = urldecode_multi($dirty);

        //windows chars that cannot be used as filenames
        $invalidFilenameCharacters = array(
            '\\',
            '/',
            ':',
            '*',
            '?',
            '"',
            '<',
            '>',
            '|'
        );
        $clean = str_replace($invalidFilenameCharacters, $replacement, $clean);

        //url chars that should not be used as filenames
        $specialUrlCharacters = array(
            '!',    // Exclamation mark
            '#',    // Hash
            '$',    // Dollar sign
            '&',    // Ampersand
            '\'',   // Single quote
            '(',    // Open parenthesis
            ')',    // Close parenthesis
            '+',    // Plus sign
            ',',    // Comma
            ';',    // Semicolon
            '=',    // Equals sign
            '@',    // At symbol
            '[',    // Open bracket
            ']',    // Close bracket
            '%',    // Percent (used for encoding)
            ':',    // Colon (used in scheme and port)
            '?',    // Question mark (used to denote query parameters)
            '/',    // Slash (used to denote paths)
            '\\',   // Backslash (used in paths)
            '{',    // Open curly brace
            '}',    // Close curly brace
            '|',    // Pipe
            '^',    // Caret
            '~',    // Tilde
            '`',    // Grave accent
        );
        $clean = str_replace($specialUrlCharacters, $replacement, $clean);

        return ($clean);
    }

    /**
     * General sanitizer for FSO file names
     *
     * @param mixed $dirty
     * @param string $replacement
     * @return array|mixed|string|string[]
     */
    public function sanitizeFsoFilename(mixed $dirty, string $replacement = ' '): mixed
    {
        if ($dirty === null || is_bool($dirty)) {
            return '';
        }

        if (!is_string($dirty)) {
            return $dirty;
        }

        $clean = urldecode_multi($dirty);

        //windows chars that cannot be used as filenames
        $invalidFilenameCharacters = array(
            '\\',
            '/',
            ':',
            '*',
            '?',
            '"',
            '<',
            '>',
            '|'
        );
        $clean = str_replace($invalidFilenameCharacters, $replacement, $clean);

        return ($clean);
    }

    public function imageExif($path): array
    {
        $exif = @$this->getCleanExifData($path);
        if (empty($exif)) {
            $exif = @getimagesize($path);
        }

        if (empty($exif)) {
            $exif = [];
        }

        $artifactExif = [
            'width' => 0,
            'height' => 0,
            'exif' => $exif,
        ];

        if (isset($exif['COMPUTED'])) {
            if (isset($exif['COMPUTED']['Width'])) {
                $artifactExif['width'] = $exif['COMPUTED']['Width'];
            }
            if (isset($exif['COMPUTED']['Height'])) {
                $artifactExif['height'] = $exif['COMPUTED']['Height'];
            }
        } elseif (isset($exif[0]) && isset($exif[1])) {
            $artifactExif['width'] = $exif[0];
            $artifactExif['height'] = $exif[1];
        }

        return $artifactExif;
    }

    public function pdfReport($path): array
    {
        $PrePressCommands = GetCommands::getPrepressCommands();
        if (!$PrePressCommands) {
            $this->addDangerAlerts("PrePress Commands are unavailable.");
            return [];
        }

        $report = $PrePressCommands->getQuickCheckReport($path);

        unset($report['aggregated']['file']['path'], $report['aggregated']['file']['filepath']);

        foreach ($report['aggregated']['pages']['page'] as $k => $v) {
            unset(
                $report['aggregated']['pages']['page'][$k]['geometry_eff'],
                $report['aggregated']['pages']['page'][$k]['geometry_applied_0'],
                $report['aggregated']['pages']['page'][$k]['geometry_applied_90'],
                $report['aggregated']['pages']['page'][$k]['geometry_applied_180'],
                $report['aggregated']['pages']['page'][$k]['geometry_applied_270'],
            );
        }

        $meta['doc'] = $report['aggregated']['doc'];
        $meta['file'] = $report['aggregated']['file'];
        $meta['pages'] = $report['aggregated']['pages'];

        return $meta;
    }

    /**
     * @param int|string|Artifact $artifactOrIdOrToken
     * @return bool
     */
    public function populateEmptyArtifactMetadataExif(int|string|Artifact $artifactOrIdOrToken,): bool
    {
        if ($artifactOrIdOrToken instanceof Artifact) {
            if (isset($artifactOrIdOrToken->artifact_metadata)) {
                if ($artifactOrIdOrToken->artifact_metadata instanceof ArtifactMetadata) {
                    if (!empty($artifactOrIdOrToken->artifact_metadata->exif)) {
                        return true;
                    }
                }
            }
        }

        /** @var false|Artifact $artifact */
        $artifact = $this->asEntity($artifactOrIdOrToken);
        if (!$artifact) {
            $this->addDangerAlerts("Could not find Artifact.");
            return false;
        }

        /** @var ArtifactMetadata $artifactMetadata */
        $artifactMetadata = $this->ArtifactMetadata->find()->where(['artifact_id' => $artifact->id])->first();
        if (!$artifactMetadata) {
            $this->addDangerAlerts("Could not find ArtifactMetadata.");
            return false;
        }

        if (empty($artifactMetadata->exif)) {
            $dest = $artifact->full_unc;
            $imageMimeTypes = $this->getImageMimeTypes();
            if (in_array($artifact->mime_type, $imageMimeTypes)) {
                $exif = $this->imageExif($dest);
                $artifactMetadata->exif = $exif['exif'];
                $artifactMetadata->width = $exif['width'];
                $artifactMetadata->height = $exif['width'];
            }

            $pdfMimeTypes = $this->getPdfMimeTypes();
            if (in_array($artifact->mime_type, $pdfMimeTypes)) {
                $report = $this->pdfReport($dest);
                $artifactMetadata->exif = $report;
                $artifactMetadata->width = 0;
                $artifactMetadata->height = 0;
            }

            if ($this->ArtifactMetadata->save($artifactMetadata)) {
                $artifact->artifact_metadata = $artifactMetadata;
                return true;
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Convenience function to create an Artifact from a URL
     *
     * @param mixed $name
     * @param $url
     * @param null $grouping
     * @return false|Artifact
     */
    public function createArtifactFromUrl(mixed $name, $url, $grouping = null): false|Artifact
    {
        try {
            $blob = file_get_contents_guzzle($url);
        } catch (\Throwable $exception) {
            $this->addDangerAlerts("Error creating Artifact from URL. {$exception->getMessage()}");
            return false;
        }

        $token = sha1(Security::randomBytes(1600));

        $data = $this->getDefaultData();
        $data['name'] = $name;
        $data['token'] = $token;
        $data['blob'] = $blob;
        $data['grouping'] = $grouping;

        return $this->createArtifact($data);
    }

    /**
     * Convenience function to create an Artifact from a name and blob
     *
     * @param mixed $name
     * @param $blob
     * @param null $grouping
     * @return Artifact|false
     */
    public function createArtifactFromBlob(mixed $name, $blob, $grouping = null): false|Artifact
    {
        $token = sha1(Security::randomBytes(1600));

        $data = $this->getDefaultData();
        $data['name'] = $name;
        $data['token'] = $token;
        $data['blob'] = $blob;
        $data['grouping'] = $grouping;

        return $this->createArtifact($data);
    }

    /**
     * Wrapper function to createArtifact() but where the actual image data needs to be magically created.
     * Used when an Artifact is required (e.g. to serve up an image) but the data does not exist yet.
     *
     * @param array $imageSettings
     * @return Artifact|false
     */
    public function createArtifactPlaceholder(array $imageSettings = []): false|Artifact
    {
        $imageSettingsDefault = [
            'width' => 64,
            'height' => 64,
            'background' => '#808080',
            'format' => 'png',
            'quality' => '90',
        ];
        $imageSettings = array_merge($imageSettingsDefault, $imageSettings);

        //create a token (this one is not unique as we can serve an exiting one)
        $token = sha1(json_encode($imageSettings));

        //check if Artifact exists based on $token
        /** @var Artifact $artifact */
        $artifact = $this->find('all')->where(['token' => $token])->first();
        if ($artifact) {
            if (is_file($artifact->full_unc)) {
                return $artifact;
            }
        }

        //generate the placeholder image
        $imageSettings['blob'] = $this->getImageResource($imageSettings)->getEncoded();

        //setup $fullData
        $metadata = [
            'name' => "{$imageSettings['width']}x{$imageSettings['height']}.{$imageSettings['format']}",
            'description' => "Placeholder Image {$imageSettings['width']}px {$imageSettings['height']}px",
            'token' => $token,
        ];
        $fullData = array_merge($metadata, $imageSettings);

        return $this->createArtifact($fullData);
    }

    /**
     * Convenience function to create an Artifact from an image resource
     *
     * @param mixed $name
     * @param Image $imageResource
     * @return Artifact|false
     */
    public function createArtifactFromImageResource(mixed $name, Image $imageResource): false|Artifact
    {
        if (!is_string($name)) {
            $name = $this->serialiseName($name);
        }

        $token = sha1(Security::randomBytes(1600));

        $data = $this->getDefaultData();
        $data['name'] = $name;
        $data['token'] = $token;
        $data['blob'] = $imageResource->stream();

        return $this->createArtifact($data);
    }

    /**
     * Return an Intervention Image resource based on the settings
     *
     * @param array $imageSettings
     * @return Image
     */
    public function getImageResource(array $imageSettings = []): Image
    {
        $imageSettingsDefault = [
            'width' => 64,
            'height' => 64,
            'background' => '#808080',
            'format' => 'png',
            'quality' => '90',
        ];
        $imageSettings = array_merge($imageSettingsDefault, $imageSettings);

        //mime type overrides the format
        if (isset($imageSettings['type'])) {
            $imageSettings['format'] = $this->getExtensionFromMimeType($imageSettings['type']);
        }

        $manager = new ImageManager();
        $imageResource = $manager
            ->canvas($imageSettings['width'], $imageSettings['height'], $imageSettings['background'])
            ->encode($imageSettings['format'], $imageSettings['quality']);

        return $imageResource;
    }

    /**
     * Convenience function to create an Errand that will generate sample image sizes
     *
     * @param $artifactOrIdOrToken
     * @param array $params
     * @param bool $preventDuplicateCreation
     * @return false|Errand
     */
    public function createSampleSizesErrand($artifactOrIdOrToken, array $params = [], bool $preventDuplicateCreation = true): false|Errand
    {
        /** @var false|Artifact $artifact */
        $artifact = $this->asEntity($artifactOrIdOrToken);
        if (!$artifact) {
            $this->addDangerAlerts("Could not find Artifact.");
            return false;
        }

        /** @var ErrandsTable $Errands */
        $Errands = TableRegistry::getTableLocator()->get('Errands');
        $defaultActivation = new DateTime();
        $defaultExpiration = (clone $defaultActivation)->addMinutes(2);

        $errandOptions = [
            'activation' => $defaultActivation,
            'expiration' => $defaultExpiration,
            'name' => substr("Create sample images for Artifact ID:{$artifact->id} Name:{$artifact->name}", 0, 255),
            'class' => 'ArtifactsTable',
            'method' => 'createSampleSizes',
            'parameters' => [
                $artifact->id,
                $params
            ],
        ];

        $result = $Errands->createErrand($errandOptions, $preventDuplicateCreation);
        $this->mergeAlerts($Errands->getAllAlertsForMerge());

        return $result;
    }

    /**
     * Create the repo_size_* sample images so that real-time server processing is not required.
     * Format is same as original e.g. png/jpg/etc.
     * PDFs thumbnails will be png format.
     *
     * You can flag which samples to create by setting $params['<size>'] = true.
     * ['icon']
     * ['thumbnail']
     * ['preview']
     * ['lr']
     * ['mr']
     * ['hr']
     *
     * @param $artifactOrIdOrToken
     * @param array $params
     * @return bool
     */
    public function createSampleSizes($artifactOrIdOrToken, array $params = []): bool
    {
        $defaultParams = [
            'quality' => 90,
            'icon' => true,
            'thumbnail' => true,
            'preview' => true,
            'lr' => true,
            'mr' => true,
            'hr' => true,
        ];
        $params = array_merge($defaultParams, $params);

        /** @var false|Artifact $artifact */
        $artifact = $this->asEntity($artifactOrIdOrToken);
        if (!$artifact) {
            $this->addDangerAlerts("Could not find Artifact.");
            return false;
        }

        $pdfMimeTypes = $this->getPdfMimeTypes();

        //check if PDF
        $isPdf = false;
        $detector = new FinfoMimeTypeDetector();
        $mimeType = $detector->detectMimeTypeFromFile($artifact->full_unc);
        if (in_array($mimeType, $pdfMimeTypes)) {
            $isPdf = true;
        }

        //check if Image
        $isImage = false;
        if (exif_imagetype($artifact->full_unc)) {
            $isImage = true;
        }

        //exit if not PDF or Image
        if (!$isPdf && !$isImage) {
            $this->addDangerAlerts("Samples cannot be created because the Artifact is not a valid Image or PDF.");
            return false;
        }

        /** @var SettingsTable $Settings */
        $Settings = TableRegistry::getTableLocator()->get('Settings');
        $sizes = $Settings->getRepoSizes();

        $baseSavePath = pathinfo($artifact->full_unc, PATHINFO_DIRNAME) . DS . "samples" . DS;
        if (!is_dir($baseSavePath)) {
            @mkdir($baseSavePath, 0777, true);
        }

        //process samples for PDF
        if ($isPdf) {
            $result = $this->sampleImagesFromPdf($artifact, $params, $sizes, $baseSavePath);
            if ($result) {
                $this->addSuccessAlerts("Created PDF samples.");
            } else {
                $this->addSuccessAlerts("Failed to create PDF samples.");
            }
        }

        //process samples for Image
        if ($isImage) {
            $result = $this->sampleImagesFromImage($artifact, $params, $sizes, $baseSavePath);
            if ($result) {
                $this->addSuccessAlerts("Created Image samples.");
            } else {
                $this->addSuccessAlerts("Failed to create Image samples.");
            }
        }

        return true;
    }

    /**
     * Convenience function to create an Errand that will generate light table images
     *
     * @param $artifactOrIdOrToken
     * @param bool $preventDuplicateCreation
     * @return false|Errand
     */
    public function createLightTableImagesErrand($artifactOrIdOrToken, bool $preventDuplicateCreation = true): false|Errand
    {
        /** @var false|Artifact $artifact */
        $artifact = $this->asEntity($artifactOrIdOrToken);
        if (!$artifact) {
            $this->addDangerAlerts("Could not find Artifact.");
            return false;
        }

        /** @var ErrandsTable $Errands */
        $Errands = TableRegistry::getTableLocator()->get('Errands');

        $defaultActivation = new DateTime();
        $defaultExpiration = (clone $defaultActivation)->addMinutes(2);

        $errandOptions = [
            'activation' => $defaultActivation,
            'expiration' => $defaultExpiration,
            'name' => substr("Create light table images for Artifact ID:{$artifact->id} Name:{$artifact->name}", 0, 255),
            'class' => 'ArtifactsTable',
            'method' => 'createLightTableImages',
            'parameters' => [
                $artifact->id,
            ],
        ];

        $result = $Errands->createErrand($errandOptions, $preventDuplicateCreation);
        $this->mergeAlerts($Errands->getAllAlertsForMerge());

        return $result;
    }

    /**
     * Create light table images so that real-time server processing is not required.
     * This is for PDFs only and thumbnails will be png format.
     *
     * Image size will be according to repo_size_thumbnail
     *
     * @param $artifactOrIdOrToken
     * @return bool
     */
    public function createLightTableImages($artifactOrIdOrToken): bool
    {
        /** @var false|Artifact $artifact */
        $artifact = $this->asEntity($artifactOrIdOrToken);
        if (!$artifact) {
            $this->addDangerAlerts("Could not find Artifact.");
            return false;
        }

        $pdfMimeTypes = $this->getPdfMimeTypes();

        //check if PDF
        $detector = new FinfoMimeTypeDetector();
        $mimeType = $detector->detectMimeTypeFromFile($artifact->full_unc);
        if (!in_array($mimeType, $pdfMimeTypes)) {
            $this->addDangerAlerts("Light Table images cannot be created because the Artifact is not a valid PDF.");
            return false;
        }

        /** @var SettingsTable $Settings */
        $Settings = TableRegistry::getTableLocator()->get('Settings');
        $size = $Settings->getSetting('repo_size_thumbnail');

        $baseSavePath = pathinfo($artifact->full_unc, PATHINFO_DIRNAME) . DS . "light-table" . DS;
        if (!is_dir($baseSavePath)) {
            @mkdir($baseSavePath, 0777, true);
        }

        $result = $this->lightTableImagesFromPdf($artifact, $size, $baseSavePath);
        if ($result) {
            $this->addSuccessAlerts("Created PDF Light Table images.");
        } else {
            $this->addSuccessAlerts("Failed to create PDF Light Table images.");
        }

        return true;
    }

    private function sampleImagesFromImage(Artifact $artifact, $params, $sizes, $baseSavePath): bool
    {
        //if one image is created, mark as overall success.
        $overallIsSuccess = false;

        foreach ($sizes as $sizeName => $sizeValue) {
            try {
                $sizeName = str_replace("repo_size_", "", $sizeName);
                if (isset($params[$sizeName])) {
                    if (!$params[$sizeName]) {
                        $overallIsSuccess = true;
                        continue;
                    }
                }

                //check if sample already exists
                $fileNameNoExt = pathinfo($artifact->full_unc, PATHINFO_FILENAME);
                $ripFormat = pathinfo($artifact->full_unc, PATHINFO_EXTENSION);
                $fullPath = "{$baseSavePath}{$fileNameNoExt}_{$sizeName}.{$ripFormat}";
                if (is_file($fullPath)) {
                    $this->addSuccessAlerts("Skipping {$sizeName} sample as it already exists.");
                    continue;
                }

                $manager = new ImageManager();
                $image = $manager->make($artifact->full_unc)
                    ->resize($sizeValue, $sizeValue, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->save($fullPath, $params['quality'], $ripFormat);

                if (is_file($fullPath)) {
                    $overallIsSuccess = true;
                    $this->addSuccessAlerts("Created a {$sizeName} sample.");
                } else {
                    $this->addDangerAlerts("Failed to create a {$sizeName} sample.}");
                }


            } catch (\Throwable $exception) {
                $this->addDangerAlerts("Failed to create a {$sizeName} sample. {$exception->getMessage()}");
            }
        }

        return $overallIsSuccess;
    }

    private function sampleImagesFromPdf(Artifact $artifact, $params, $sizes, $baseSavePath): bool
    {
        $PrePressCommands = GetCommands::getPrepressCommands();
        if (!$PrePressCommands) {
            $this->addDangerAlerts("PrePress Commands are unavailable.");
            return false;
        }

        //check if already exists
        if ($artifact->doAllSampleImagesExist()) {
            $this->addSuccessAlerts("Skipping routine as all sample images exist.");
            return true;
        }

        //if one image is created, mark as overall success.
        $overallIsSuccess = false;

        foreach ($sizes as $sizeName => $sizeValue) {
            try {
                $sizeName = str_replace("repo_size_", "", $sizeName);
                if (isset($params[$sizeName])) {
                    if (!$params[$sizeName]) {
                        $overallIsSuccess = true;
                        continue;
                    }
                }

                /*
                 * todo really large dimensioned PDF files cannot rip at 1-dpi so the 'icon' generation fails
                 * may need to rip a large image then make smaller images from that image
                 * would probably be faster than reading a pdf multiple times
                 */

                //setup RIP options
                $ripFormat = 'png'; //PDF files will always be converted to PNG
                $ripPages = [1];
                $ripResolution = "{$sizeValue}x{$sizeValue}";

                //check if sample already exists
                $fileNameNoExt = pathinfo($artifact->full_unc, PATHINFO_FILENAME);
                $fullPath = "{$baseSavePath}{$fileNameNoExt}_{$sizeName}.{$ripFormat}";
                if (is_file($fullPath)) {
                    $this->addSuccessAlerts("Skipping {$sizeName} sample as it already exists.");
                    continue;
                }

                $ripOptions = [
                    'format' => $ripFormat,
                    'colorspace' => 'colour',
                    'resolution' => $ripResolution,
                    'smoothing' => true,
                    'pagebox' => 'MediaBox',
                    'pagelist' => $ripPages,
                    'outputfolder' => $baseSavePath,
                ];
                $pdfRipResults = $PrePressCommands->savePdfAsImages($artifact->full_unc, $ripOptions);

                if (isset($pdfRipResults[0])) {
                    //rename the file as it has _PageNumber as suffix
                    $result = rename($pdfRipResults[0], $fullPath);
                    if ($result) {
                        $overallIsSuccess = true;
                        $this->addSuccessAlerts("Created a {$sizeName} sample.");
                    } else {
                        $this->addDangerAlerts("Could not move {$sizeName} sample to the final location. ");
                    }
                } else {
                    $this->addDangerAlerts("Failed to rip a {$sizeName} sample.}");
                }

            } catch (\Throwable $exception) {
                $this->addDangerAlerts("Failed to create a {$sizeName} sample. {$exception->getMessage()}");
            }
        }

        return $overallIsSuccess;
    }


    public function lightTableImagesFromPdf(Artifact $artifact, $size, $baseSavePath): bool
    {
        $PrePressCommands = GetCommands::getPrepressCommands();
        if (!$PrePressCommands) {
            $this->addDangerAlerts("PrePress Commands are unavailable.");
            return false;
        }

        //check if already exists
        if ($artifact->doAllLightTableImagesExist()) {
            $this->addSuccessAlerts("Skipping routine as all light table images exist.");
            return true;
        }

        try {
            /*
             * todo really large dimensioned PDF files cannot rip at 1-dpi so the 'icon' generation fails
             * may need to rip a large image then make smaller images from that image
             * would probably be faster than reading a pdf multiple times
             */

            //setup RIP options
            $ripOptions = [
                'format' => 'png',
                'quality' => '90',
                'colorspace' => 'colour',
                'resolution' => "{$size}x{$size}",
                'smoothing' => true,
                'pagebox' => 'MediaBox',
                'pagelist' => null,
                'outputfolder' => $baseSavePath,
            ];
            $pdfRipResults = $PrePressCommands->savePdfAsImages($artifact->full_unc, $ripOptions);

        } catch (\Throwable $exception) {
            $this->addDangerAlerts("Failed to create a Light Table images. {$exception->getMessage()}");
            return false;
        }

        return true;
    }

    /**
     * @param $artifactOrIdOrToken
     * @param $params
     * @return Artifact|array|EntityInterface|false|null
     */
    public function recompressImage($artifactOrIdOrToken, $params): bool|array|Artifact|EntityInterface|null
    {
        if ($artifactOrIdOrToken instanceof Artifact) {
            $artifact = $artifactOrIdOrToken;
        } else {
            $query = $this->find('all');
            if (is_numeric($artifactOrIdOrToken)) {
                $query = $query->where(['id' => $artifactOrIdOrToken]);
            } elseif (is_string($artifactOrIdOrToken)) {
                $query = $query->where(['token' => $artifactOrIdOrToken]);
            } else {
                return false;
            }

            $artifact = $query->first();
            if (!$artifact) {
                return false;
            }
        }

        if (!exif_imagetype($artifact->full_unc)) {
            return false;
        }

        $defaultParams = [
            'size' => 640,
            'format' => 'jpg',
            'quality' => 90,
        ];

        $params = array_merge($defaultParams, $params);

        $im = new ImageManager();

        try {
            $image = $im->make($artifact->full_unc);
        } catch (\Throwable $e) {
            $image = $this->getImageResource();
        }

        $practicalScope = 1.1;
        $widthInsideScope = ($image->getWidth()) * $practicalScope;
        $heightInsideScope = ($image->getHeight()) * $practicalScope;

        //abort if no sizing is required
        if ($widthInsideScope < $params['size'] && $heightInsideScope < $params['size']) {
            return $artifact;
        }

        $image
            ->widen($params['size'], function ($constraint) {
                $constraint->upsize();
            })
            ->heighten($params['size'], function ($constraint) {
                $constraint->upsize();
            })
            ->encode($params['format'], $params['quality'])
            ->save();

        return $artifact;
    }

    /**
     * Split a string into random chunks
     *
     * @param string $string
     * @param int $minLength
     * @param int $maxLength
     * @return array
     */
    public function str_split_random(string $string = '', int $minLength = 1, int $maxLength = 4): array
    {
        $l = strlen($string);
        $i = 0;

        $chunks = [];
        while ($i < $l) {
            $r = rand($minLength, $maxLength);
            $chunks[] = substr($string, $i, $r);
            $i += $r;
        }

        return $chunks;
    }

    /**
     * Overwrite the delete method to include the FSO deletion
     *
     * @param EntityInterface|Artifact $entity
     * @param array $options
     * @return bool
     */
    public function delete(EntityInterface $entity, array $options = []): bool
    {
        if (is_file($entity->full_unc)) {
            unlink($entity->full_unc);
        }

        $basePath = $this->repoUnc;
        $pathParts = explode("\\", trim($entity->unc, "\\/"));

        $paths = [];
        $joiner = '';
        foreach ($pathParts as $part) {
            $joiner = $joiner . $part . "\\";
            $paths[] = $joiner;
        }
        $paths = array_reverse($paths);

        $adapter = new LocalFilesystemAdapter($basePath);
        $filesystem = new Filesystem($adapter);

        //always delete the first dir (even if not empty)
        try {
            $filesystem->deleteDirectory($paths[0]);
            unset($paths[0]);
        } catch (\Throwable $exception) {
        }

        //delete remaining dirs if empty
        foreach ($paths as $path) {
            try {
                $filesInPath = $filesystem->listContents($path)
                    ->map(fn(StorageAttributes $attributes) => $attributes->path())
                    ->toArray();
                if (empty($filesInPath)) {
                    $filesystem->deleteDirectory($path);
                } else {
                    break;
                }
            } catch (\Throwable $exception) {
            }
        }

        $deleteResult = parent::delete($entity, $options);

        $this->ArtifactMetadata->deleteOrphaned();

        return $deleteResult;
    }

    /**
     * Delete an artifact by its token
     *
     * @param string $token
     * @return bool
     */
    public function deleteByToken(string $token): bool
    {
        $artifact = $this->find('all')->where(['token' => $token])->first();

        if ($artifact) {
            return $this->delete($artifact);
        }

        return false;
    }

    /**
     * Delete multiple artifacts by their grouping
     *
     * @param string $grouping
     * @return bool
     */
    public function deleteByGrouping(string $grouping): bool
    {
        $artifacts = $this->find('all')->where(['grouping' => $grouping]);

        foreach ($artifacts as $artifact) {
            if ($artifact) {
                return $this->delete($artifact);
            }
        }

        return false;
    }

    /**
     * Wrapper function
     *
     * @param int $limit
     * @return int
     */
    public function deleteTopExpired(int $limit = 50): int
    {
        $hardLimit = 200;
        $finalLimit = min($limit, $hardLimit);

        $currentDatetime = new DateTime();
        $artifactsToDelete = $this->find('all')
            ->where(['expiration <=' => $currentDatetime->format("Y-m-d H:i:s"), 'auto_delete' => true])
            ->limit($finalLimit);

        $counter = 0;
        foreach ($artifactsToDelete->toArray() as $artifact) {
            $result = $this->delete($artifact);
            if ($result) {
                $counter++;
            }
        }

        return $counter;
    }

    /**
     * Helps to keep the Artifacts table under control.
     * Get specified number of random records and then checks each one for an object in the FS
     * Deletes the Artifact record if no Artifact found.
     *
     * @param int $limit
     * @return int
     */
    public function deleteHasMissingArtifact(int $limit = 200): int
    {
        $hardLimit = 500;
        $finalLimit = min($limit, $hardLimit);

        $dbDriver = ($this->getConnection())->getDriver();
        if ($dbDriver instanceof Sqlite) {
            $rndSQL = "id IN (SELECT id FROM artifacts ORDER BY RANDOM() LIMIT {$finalLimit})";
        } else {
            $rndSQL = "id IN (SELECT TOP ({$finalLimit}) id FROM artifacts TABLESAMPLE (1 PERCENT) ORDER BY NEWID())";
        }

        $artifactsToDelete = $this->find('all')
            ->where([$rndSQL])
            ->limit($finalLimit);

        $counter = 0;
        /**
         * @var Artifact $artifact
         */
        foreach ($artifactsToDelete->toArray() as $artifact) {
            if (!is_file($artifact->full_unc)) {
                $result = $this->delete($artifact);
                if ($result) {
                    $counter++;
                }
            }
        }

        return $counter;
    }

    /**
     * @param $stream
     * @param null $sections
     * @param false $arrays
     * @param false $thumbnail
     * @return array
     */
    private function getCleanExifData($stream, $sections = null, $arrays = false, $thumbnail = false): array
    {
        $exif = @exif_read_data($stream, $sections, $arrays, $thumbnail);

        $allowedExifValues = $this->getAllowedExifValues();

        $exifClean = [];
        if ($exif) {
            foreach ($allowedExifValues as $allowedExifValue) {
                if (isset($exif[$allowedExifValue])) {
                    $exifClean[$allowedExifValue] = $exif[$allowedExifValue];
                }
            }
        }

        return $exifClean;
    }

    /**
     * @return string[]
     */
    private function getAllowedExifValues(): array
    {
        $exifValues = [
            'FileName',
            'FileDateTime',
            'FileSize',
            'FileType',
            'MimeType',
            'SectionsFound',
            'COMPUTED',
            'DateTime',
            'Artist',
            'Copyright',
            'Author',
            'Exif_IFD_Pointer',
            'THUMBNAIL',
            'DateTimeOriginal',
            'DateTimeDigitized',
            'SubSecTimeOriginal',
            'SubSecTimeDigitized',
            'Company',
        ];

        return $exifValues;
    }

    /**
     * @param mixed $name
     * @return Query
     */
    public function findByName(mixed $name): Query
    {
        $name = $this->serialiseName($name);
        return $this->find('all')->where(['name' => $name]);
    }

    /**
     * @param mixed $token
     * @return Query
     */
    public function findByToken(mixed $token): Query
    {
        $token = $this->serialiseName($token);
        return $this->find('all')->where(['token' => $token]);
    }

    /**
     * @param mixed $tokens
     * @return Query
     */
    public function findByTokens(array $tokens): Query
    {
        return $this->find('all')->where(['token IN' => $tokens]);
    }

    /**
     * @param mixed $groupingValue
     * @return Query
     */
    public function findByGrouping(mixed $groupingValue): Query
    {
        $groupingValue = $this->serialiseName($groupingValue);
        return $this->find('all')->where(['grouping' => $groupingValue]);
    }

    /**
     * @param $name
     * @return string
     */
    public function serialiseName($name): string
    {
        if (!is_string($name)) {
            $name = sha1(json_encode($name));
        }

        return $name;
    }

    /**
     * @return array
     */
    private function getDefaultData(): array
    {
        $timeObjCurrent = new DateTime();
        $months = intval(Configure::read("Settings.repo_purge"));

        return [
            'tmp_name' => null,
            'blob' => null,
            'error' => 0,
            'name' => null,
            'description' => null,
            'size' => null,
            'mime_type' => null,
            'activation' => (clone $timeObjCurrent),
            'expiration' => (clone $timeObjCurrent)->addMonths($months),
            'auto_delete' => true,
            'token' => null,
            'url' => null,
            'unc' => null,
            'hash_sum' => null,
            'grouping' => null,
        ];
    }

    /**
     * @param $filePath
     * @param int $numBytes
     * @return false|string
     */
    private function sha1LargeFiles($filePath, int $numBytes = 20 * 1024 * 1024): false|string
    {
        $fileSize = filesize($filePath);
        $fileHandle = fopen($filePath, 'rb');

        if ($fileHandle === false) {
            $this->addDangerAlerts("Could not open the file to calculate a hash sum!");
        }

        if ($fileSize <= $numBytes) {
            // If the file is smaller than or equal to $numBytes, use sha1_file
            fclose($fileHandle);
            return sha1_file($filePath);
        }

        // Read the first $numBytes bytes
        $firstBytes = fread($fileHandle, $numBytes);
        if ($firstBytes === false) {
            $this->addDangerAlerts("Could not read the first bytes to calculate a hash sum!");
        }

        // Move the file pointer to the end minus $numBytes bytes
        fseek($fileHandle, -$numBytes, SEEK_END);

        // Read the last $numBytes bytes
        $lastBytes = fread($fileHandle, $numBytes);
        if ($lastBytes === false) {
            $this->addDangerAlerts("Could not read the last bytes to calculate a hash sum!");
        }

        fclose($fileHandle);

        // Concatenate the first and last bytes and compute the SHA1 hash
        $combinedBytes = $firstBytes . $lastBytes . $fileSize;
        return sha1($combinedBytes);
    }
}
