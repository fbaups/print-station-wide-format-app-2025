<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\ArticleStatus;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Closure;
use Psr\SimpleCache\CacheInterface;

/**
 * ArticleStatuses Model
 *
 * @method ArticleStatus newEmptyEntity()
 * @method ArticleStatus newEntity(array $data, array $options = [])
 * @method array<ArticleStatus> newEntities(array $data, array $options = [])
 * @method ArticleStatus get(mixed $primaryKey, array|string $finder = 'all', CacheInterface|string|null $cache = null, Closure|string|null $cacheKey = null, mixed ...$args)
 * @method ArticleStatus findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method ArticleStatus patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method array<ArticleStatus> patchEntities(iterable $entities, array $data, array $options = [])
 * @method ArticleStatus|false save(EntityInterface $entity, array $options = [])
 * @method ArticleStatus saveOrFail(EntityInterface $entity, array $options = [])
 * @method iterable<ArticleStatus>|ResultSetInterface<ArticleStatus>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<ArticleStatus>|ResultSetInterface<ArticleStatus> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<ArticleStatus>|ResultSetInterface<ArticleStatus>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<ArticleStatus>|ResultSetInterface<ArticleStatus> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin TimestampBehavior
 */
class ArticleStatusesTable extends AppTable
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

        $this->setTable('article_statuses');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->initializeSchemaJsonFields($this->getJsonFields());
    }

    /**
     * Default validation rules.
     *
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('sort')
            ->allowEmptyString('sort');

        $validator
            ->scalar('name')
            ->maxLength('name', 50)
            ->allowEmptyString('name');

        $validator
            ->scalar('description')
            ->maxLength('description', 1024)
            ->allowEmptyString('description');

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
}
