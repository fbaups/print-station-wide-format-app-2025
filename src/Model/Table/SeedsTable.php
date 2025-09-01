<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Log\Engine\Auditor;
use App\Model\Entity\Seed;
use arajcany\ToolBox\Utility\TextFormatter;
use Cake\Core\Configure;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Http\ServerRequest;
use Cake\I18n\DateTime;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Routing\Router;
use Cake\Utility\Inflector;
use Cake\Utility\Security;
use Cake\Validation\Validator;

/**
 * Seeds Model
 *
 * @method Seed newEmptyEntity()
 * @method Seed newEntity(array $data, array $options = [])
 * @method Seed[] newEntities(array $data, array $options = [])
 * @method Seed get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method Seed findOrCreate($search, ?callable $callback = null, $options = [])
 * @method Seed patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method Seed[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method Seed|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method Seed saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method Seed[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method Seed[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method Seed[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method Seed[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SeedsTable extends AppTable
{
    private int $seedDefaultDuration = 60;
    private int $seedDefaultBidLimit = 1;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('seeds');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

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
            ->dateTime('activation')
            ->allowEmptyDateTime('activation');

        $validator
            ->dateTime('expiration')
            ->allowEmptyDateTime('expiration');

        $validator
            ->scalar('token')
            ->maxLength('token', 50)
            ->allowEmptyString('token');

        $validator
            ->scalar('url')
            ->maxLength('url', 1024)
            ->allowEmptyString('url');

        $validator
            ->integer('bids')
            ->allowEmptyString('bids');

        $validator
            ->integer('bid_limit')
            ->allowEmptyString('bid_limit');

        $validator
            ->integer('user_link')
            ->allowEmptyString('user_link');

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
     * Wrapper function
     *
     * @param array $options
     * @return mixed
     */
    public function createSeedReturnToken(array $options = []): mixed
    {
        $seed = $this->createSeed($options);
        return $seed->token;
    }

    /**
     * Create a Seed as per the passed in $options.
     * The Seed is all the data.
     * The token is just the random hash.
     *
     * @param array $options
     * @return Seed
     */
    public function createSeed(array $options = []): Seed
    {
        $token = sha1(Security::randomBytes(10000));
        $optionsDefault = [
            'activation' => new DateTime(),
            'expiration' => new DateTime('+ ' . $this->seedDefaultDuration . ' seconds'),
            'token' => $token,
            'url' => '',
            'bids' => 0,
            'bid_limit' => $this->seedDefaultBidLimit,
            'user_link' => 0,
        ];
        $options = array_merge($optionsDefault, $options);

        //make sure UTC
        $options['activation'] = $options['activation']->setTimezone('UTC');
        $options['expiration'] = $options['expiration']->setTimezone('UTC');

        //convert the CakePHP url syntax to string
        if (is_array($options['url'])) {
            $options['url'] = Router::url($options['url']);
        }

        //insert the token into the url
        $options['url'] = str_replace('{token}', $token, $options['url']);
        $options['url'] = str_replace('%7Btoken%7D', $token, $options['url']);

        //make sure that the IIS subdirectory (i.e. application alias) is removed
        $iisSubDir = Router::url(['prefix' => false, 'controller' => '/']);
        if (strlen($iisSubDir) > 1 && TextFormatter::startsWith($options['url'], $iisSubDir)) {
            $options['url'] = str_replace($iisSubDir, '', $options['url']);
        }

        $seed = $this->newEmptyEntity();
        $seed->activation = $options['activation'];
        $seed->expiration = $options['expiration'];
        $seed->token = $options['token'];
        $seed->url = $options['url'];
        $seed->bids = $options['bids'];
        $seed->bid_limit = $options['bid_limit'];
        $seed->user_link = $options['user_link'];

        $this->save($seed);

        return $seed;
    }

    /**
     * Useful when you need to keep a token that should never be used again or need to expire a seed.
     *
     * @param string $token
     * @return Seed|bool
     */
    public function createExpiredSeedFromToken(string $token): Seed|bool
    {
        $testSeed = $this->getSeed($token);
        if ($testSeed) {
            $seed = $testSeed;
        } else {
            $seed = $this->newEmptyEntity();
        }

        $seed->activation = (new DateTime())->subYears(1)->subSeconds(30);
        $seed->expiration = (new DateTime())->subYears(1);
        $seed->token = $token;
        $seed->url = null;
        $seed->bids = 1;
        $seed->bid_limit = 1;
        $seed->user_link = 0;

        $this->save($seed);

        return $seed;
    }

    /**
     * Validate that the seed is active.
     * Checks to see if the token exists
     * Checks activation time
     * Checks expiration time
     * Checks the number of bids
     * Checks if seed is locked to url (minus query string portion)
     *
     * Returns a bool if seed is Valid or Invalid
     * To get invalid reasons, call $this->getDangerAlerts()
     *
     * @param string $token
     * @param bool $forceUrlCheck
     * @return bool
     */
    public function validateSeed(string $token, bool $forceUrlCheck = false): bool
    {
        /**
         * @var Seed $seed
         */
        $seed = $this->getSeed($token);

        //set the default return value
        $return = true;

        //check that seed exists
        if (!$seed) {
            //return immediately as there is no point doing more checks if seed does not exist
            $this->addDangerAlerts(__('Seed does not exist.'));
            return false;
        }

        //check that current datetime is within the seed activation and expiration datetime
        $frozenTimeObj = new DateTime('now');
        $activation = $seed->activation;
        $expiration = $seed->expiration;
        $activationReadable = (!is_null($activation) ? $activation->i18nFormat("yyyy-MM-dd HH:mm:ss", LCL_TZ) : '');
        $expirationReadable = (!is_null($expiration) ? $expiration->i18nFormat("yyyy-MM-dd HH:mm:ss", LCL_TZ) : '');
        if ($activation && $expiration) {
            if ($frozenTimeObj->greaterThanOrEquals($activation) === false || $frozenTimeObj->lessThanOrEquals($expiration) === false) {
                if ($frozenTimeObj->greaterThanOrEquals($activation) === false) {
                    $this->addDangerAlerts(__('Seed will activate on {0}.', $activationReadable));
                    $return = false;
                }

                if ($frozenTimeObj->lessThanOrEquals($expiration) === false) {
                    $this->addDangerAlerts(__('Seed expired on {0}.', $expirationReadable));
                    $return = false;
                }
            }
        }

        //check bids < bid_limit
        if ($seed->bid_limit >= 0) {
            if ($seed->bids >= $seed->bid_limit) {
                $this->addDangerAlerts(__('Maximum number of bids reached.'));
                $return = false;
            }
        }

        //check if seed is locked to url
        if (!empty($seed->url)) {
            $currentUrl = "/" . str_replace(Router::url("/", true), "", Router::url(null, true));

            $seedUrl = explode("?", $seed->url);
            $seedUrl = $seedUrl[0];

            if (!TextFormatter::startsWith($currentUrl, $seedUrl)) {
                $this->addDangerAlerts(__('Seed does not belong to this url.'));
                $this->addDangerAlerts($currentUrl);
                $this->addDangerAlerts($seedUrl);
                $return = false;
            }
        }

        if (empty($seed->url) && $forceUrlCheck) {
            $this->addDangerAlerts(__('Seed does not belong to this url.'));
            $return = false;
        }

        return $return;
    }

    /**
     * Get all the properties of the seed.
     *
     * @param $token
     * @return bool|Seed
     */
    public function getSeed($token): bool|Seed
    {
        $token = trim($token);

        /**
         * @var Seed $seed
         */
        $seed = $this->find()
            ->where(['token' => $token])
            ->first();

        if (!$seed) {
            return false;
        }

        return $seed;
    }

    /**
     * Expire a Seed by setting the bids to = bid_limit
     *
     * @param $token
     * @param int $count
     * @return bool
     */
    public function expireSeed($token): bool
    {
        /**
         * @var Seed $seed
         */
        $seed = $this->find()
            ->where(['token' => $token])
            ->first();

        if (!$seed) {
            return false;
        } else {
            $seed->bids = $seed->bid_limit;
            $this->save($seed);
            return true;
        }
    }

    /**
     * Increase the bid count
     * Validating the Seed has no effect, you need to increase/decrease the bid manually
     *
     * @param $token
     * @param int $count
     * @return bool
     */
    public function increaseBid($token, int $count = 1): bool
    {
        /**
         * @var Seed $seed
         */
        $seed = $this->find()
            ->where(['token' => $token])
            ->first();

        if (!$seed) {
            return false;
        } else {
            $seed->bids = $seed->bids + $count;
            $this->save($seed);
            return true;
        }
    }

    /**
     * Decrease the bid count
     * Validating the Seed has no effect, you need to increase/decrease the bid manually
     *
     * @param $token
     * @param int $count
     * @return bool
     */
    public function decreaseBid($token, int $count = 1): bool
    {
        return $this->increaseBid($token, -($count));
    }

    /**
     * @param ServerRequest $request
     * @param bool $increaseBid
     * @return bool
     */
    public function validateBearerTokenInRequest(ServerRequest $request, bool $increaseBid = true): bool
    {
        $Auditor = new Auditor();

        //extract the bearer token
        $authBearerToken = $this->extractBearerTokenFromRequest($request);
        if (!$authBearerToken) {
            $msg = __('Bearer Token not supplied.');
            $Auditor->auditWarning($msg);
            $this->addDangerAlerts($msg);

            return false;
        }

        //check the bearer token
        $isTokenValid = $this->validateSeed($authBearerToken, true);
        if (!$isTokenValid) {
            $msg = __('Bearer Token not valid.');
            $Auditor->auditWarning($msg);
            $this->addDangerAlerts($msg);

            return false;
        }

        if($increaseBid){
            $this->increaseBid($authBearerToken);
        }

        return true;
    }
}
