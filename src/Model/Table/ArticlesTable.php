<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Article;
use arajcany\ToolBox\Utility\Security\Security;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\I18n\DateTime;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Articles Model
 *
 * @property \App\Model\Table\RolesTable&\Cake\ORM\Association\BelongsToMany $Roles
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsToMany $Users
 * @property \App\Model\Table\ArticleStatusesTable&\Cake\ORM\Association\BelongsTo $ArticleStatuses
 *
 * @method \App\Model\Entity\Article newEmptyEntity()
 * @method \App\Model\Entity\Article newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Article> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Article get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Article findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Article patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Article> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Article|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Article saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Article>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Article>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Article>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Article> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Article>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Article>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Article>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Article> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ArticlesTable extends AppTable
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('articles');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('ArticleStatuses', [
            'foreignKey' => 'article_status_id',
        ]);
        $this->belongsToMany('Roles', [
            'foreignKey' => 'article_id',
            'targetForeignKey' => 'role_id',
            'joinTable' => 'articles_roles',
        ]);
        $this->belongsToMany('Users', [
            'foreignKey' => 'article_id',
            'targetForeignKey' => 'user_id',
            'joinTable' => 'articles_users',
        ]);

        $this->initializeSchemaJsonFields($this->getJsonFields());
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
            ->integer('article_status_id')
            ->allowEmptyString('article_status_id');

        $validator
            ->integer('user_link')
            ->allowEmptyString('user_link');

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
            ->scalar('title')
            ->maxLength('title', 255)
            ->allowEmptyString('title');

        $validator
            ->scalar('body')
            ->allowEmptyString('body');

        $validator
            ->integer('priority')
            ->allowEmptyString('priority');

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
     * @param Article $article
     * @return Article
     */
    public function reformatEntity(Article $article): Article
    {
        $article->body = $this->extractImagesFromBodyText($article->body);

        $currentDT = new DateTime();

        $activation = $article->activation ?? $currentDT;
        $expiration = $article->expiration ?? $currentDT;

        $article->activation = $activation->setTimezone(LCL_TZ)->startOfDay()->setTimezone('utc');
        $article->expiration = $expiration->setTimezone(LCL_TZ)->endOfDay()->setTimezone('utc');

        return $article;
    }


    /**
     * @param string $bodyText
     * @return string
     */
    public function extractImagesFromBodyText(string $bodyText): string
    {
        $pattern = '/<img\b[^>]*>/i';
        preg_match_all($pattern, $bodyText, $matches);
        $imgTags = $matches[0];

        $groupingKey = sha1(Security::randomBytes(1024));

        /** @var ArtifactsTable $Artifacts */
        $Artifacts = TableRegistry::getTableLocator()->get('Artifacts');

        foreach ($imgTags as $imgTag) {
            $patternSrc = '/src="([^"]*)"/i';
            preg_match($patternSrc, $imgTag, $matches);
            $src = $matches[1];

            $patternFilename = '/data-filename="([^"]*)"/i';
            preg_match($patternFilename, $imgTag, $matches);
            $filename = $matches[1];

            $prefix = 'data:image/png;base64,';
            if (str_starts_with($src, $prefix)) {
                $blob = str_replace($prefix, '', $src);
                $blob = base64_decode($blob);
                $artifact = $Artifacts->createArtifactFromBlob($filename, $blob, $groupingKey);
                if ($artifact) {
                    $srcNew = str_replace($src, $artifact->full_url, $src);
                    $imgTagNew = str_replace($src, $srcNew, $imgTag);

                    $artifactTagIn = ' data-filename="';
                    $artifactTagOut = ' data-artifact-grouping="' . $artifact->grouping . '"' . ' data-artifact-id="' . $artifact->id . '"' . ' data-filename="';
                    $imgTagNew = str_replace($artifactTagIn, $artifactTagOut, $imgTagNew);

                    $bodyText = str_replace($imgTag, $imgTagNew, $bodyText);
                }
            }
        }

        return $bodyText;
    }


    /**
     * Deletes the associated Artifacts
     *
     * @param EntityInterface|Article $entity
     * @param array $options
     * @return bool
     */
    public function delete(EntityInterface|Article $entity, array $options = []): bool
    {
        $pattern = '/data-artifact-grouping="([^"]+)"/';
        preg_match_all($pattern, $entity->body, $matches);
        $groupings = array_unique($matches[1]);

        /** @var ArtifactsTable $ArtifactsTable */
        $ArtifactsTable = TableRegistry::getTableLocator()->get('Artifacts');
        foreach ($groupings as $grouping) {
            $ArtifactsTable->deleteByGrouping($grouping);
        }

        return parent::delete($entity, $options);
    }
}
