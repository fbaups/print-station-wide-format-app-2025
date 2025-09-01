<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\MessageGateways\CellcastSmsGateway;
use App\MessageGateways\SmsGatewayFactory;
use App\Model\Entity\Message;
use App\Model\Entity\MessageConnection;
use arajcany\ToolBox\Utility\Security\Security;
use Cake\Core\Configure;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\I18n\DateTime;
use Cake\Http\Session;
use Cake\Mailer\Mailer;
use Cake\Mailer\Renderer;
use Cake\Mailer\TransportFactory;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;
use Pelago\Emogrifier\CssInliner;
use Throwable;
use function PHPUnit\Framework\arrayHasKey;

/**
 * Messages Model
 *
 * @method Message newEmptyEntity()
 * @method Message newEntity(array $data, array $options = [])
 * @method Message[] newEntities(array $data, array $options = [])
 * @method Message get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method Message findOrCreate($search, ?callable $callback = null, $options = [])
 * @method Message patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method Message[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method Message|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method Message saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method Message[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method Message[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method Message[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method Message[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MessagesTable extends AppTable
{
    private \Cake\ORM\Table|MessageBeaconsTable $MessageBeacons;
    private \Cake\ORM\Table|MessageConnectionsTable $MessageConnections;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('messages');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->MessageBeacons = TableRegistry::getTableLocator()->get('MessageBeacons');
        $this->MessageConnections = TableRegistry::getTableLocator()->get('MessageConnections');

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
            ->scalar('type')
            ->maxLength('type', 50)
            ->allowEmptyString('type');

        $validator
            ->scalar('name')
            ->maxLength('name', 128)
            ->allowEmptyString('name');

        $validator
            ->scalar('description')
            ->maxLength('description', 850)
            ->allowEmptyString('description');

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
            ->dateTime('started')
            ->allowEmptyDateTime('started');

        $validator
            ->dateTime('completed')
            ->allowEmptyDateTime('completed');

        $validator
            ->scalar('server')
            ->maxLength('server', 128)
            ->allowEmptyString('server');

        $validator
            ->scalar('domain')
            ->maxLength('domain', 255)
            ->allowEmptyString('domain');

        $validator
            ->scalar('transport')
            ->maxLength('transport', 50)
            ->allowEmptyString('transport');

        $validator
            ->scalar('profile')
            ->maxLength('profile', 50)
            ->allowEmptyFile('profile');

        $validator
            ->scalar('layout')
            ->maxLength('layout', 255)
            ->allowEmptyString('layout');

        $validator
            ->scalar('template')
            ->maxLength('template', 255)
            ->allowEmptyString('template');

        $validator
            ->scalar('email_format')
            ->maxLength('email_format', 50)
            ->allowEmptyString('email_format');

        $validator
            ->scalar('sender')
            ->maxLength('sender', 850)
            ->allowEmptyString('sender');

        $validator
            ->scalar('email_from')
            ->maxLength('email_from', 850)
            ->allowEmptyString('email_from');

        $validator
            ->scalar('email_to')
            ->maxLength('email_to', 850)
            ->allowEmptyString('email_to');

        $validator
            ->scalar('email_cc')
            ->maxLength('email_cc', 850)
            ->allowEmptyString('email_cc');

        $validator
            ->scalar('email_bcc')
            ->maxLength('email_bcc', 850)
            ->allowEmptyString('email_bcc');

        $validator
            ->scalar('reply_to')
            ->maxLength('reply_to', 850)
            ->allowEmptyString('reply_to');

        $validator
            ->scalar('read_receipt')
            ->maxLength('read_receipt', 850)
            ->allowEmptyString('read_receipt');

        $validator
            ->scalar('subject')
            ->maxLength('subject', 850)
            ->allowEmptyString('subject');

        $validator
            ->scalar('view_vars')
            ->maxLength('view_vars', 1)
            ->allowEmptyString('view_vars');

        $validator
            ->integer('priority')
            ->allowEmptyString('priority');

        $validator
            ->scalar('headers')
            ->maxLength('headers', 2048)
            ->allowEmptyString('headers');

        $validator
            ->integer('smtp_code')
            ->allowEmptyString('smtp_code');

        $validator
            ->scalar('smtp_message')
            ->maxLength('smtp_message', 2048)
            ->allowEmptyString('smtp_message');

        $validator
            ->integer('lock_code')
            ->allowEmptyString('lock_code');

        $validator
            ->scalar('errors_thrown')
            ->maxLength('errors_thrown', 1)
            ->allowEmptyString('errors_thrown');

        $validator
            ->integer('errors_retry')
            ->allowEmptyString('errors_retry');

        $validator
            ->integer('errors_retry_limit')
            ->allowEmptyString('errors_retry_limit');

        $validator
            ->scalar('beacon_hash')
            ->maxLength('beacon_hash', 50)
            ->allowEmptyString('beacon_hash');

        $validator
            ->scalar('hash_sum')
            ->maxLength('hash_sum', 50)
            ->allowEmptyString('hash_sum');

        return $validator;
    }

    /**
     * List of properties that can be JSON encoded
     *
     * @return array
     */
    public function getJsonFields(): array
    {
        $jsonFields = [
            'sender',
            'email_from',
            'email_to',
            'email_cc',
            'email_bcc',
            'reply_to',
            'read_receipt',
            'view_vars',
            'headers',
            'errors_thrown',
        ];

        return $jsonFields;
    }

    /**
     * Default properties for creating a Message
     *
     * @return array
     */
    public function getDefaultMessageProperties(): array
    {
        $session = new Session();
        $fromEmail = $session->read('Auth.User.email');
        $fromName = $session->read('Auth.User.first_name') . " " . $session->read('Auth.User.last_name');

        if (empty($fromEmail) || empty($fromName)) {
            $fromName = Configure::read('Settings.email_from_name');
            $fromEmail = Configure::read('Settings.email_from_address');
        }

        if (empty($fromEmail) || empty($fromName)) {
            $fromName = APP_NAME;
            $fromEmail = str_replace(' ', '', APP_NAME) . '@localhost.com';
        }

        $activation = new DateTime();
        $expiration = (new DateTime())->addHours(1);
        $messageRetryLimit = Configure::read("Settings.message_background_service_retry_limit");

        $default = [
            'type' => 'email',
            'name' => null,
            'description' => null,
            'activation' => $activation,
            'expiration' => $expiration,
            'auto_delete' => 1,
            'started' => null,
            'completed' => null,
            'server' => null,
            'domain' => parse_url(Router::url("/", true), PHP_URL_HOST),
            'transport' => 'default',
            'profile' => 'default',
            'layout' => 'default',
            'template' => 'default',
            'email_format' => 'html', // html|text|both
            'sender' => [Configure::read('Settings.email_from_address') => Configure::read('Settings.email_from_name')],
            'email_from' => [$fromEmail => $fromName],
            'email_to' => null,
            'email_cc' => null,
            'email_bcc' => null,
            'reply_to' => null,
            'read_receipt' => null,
            'subject' => null,
            'view_vars' => null,
            'priority' => 3, //1 (highest) to 5 (lowest)
            'headers' => null,
            'smtp_code' => null,
            'smtp_message' => null,
            'lock_code' => null,
            'errors_thrown' => null,
            'errors_retry' => 0,
            'errors_retry_limit' => $messageRetryLimit,
            'beacon_hash' => sha1(Security::randomString(1024)),
            'hash_sum' => null,
        ];

        return $default;
    }

    /**
     * Simple way to create a Message for sending later.
     * Aids with the JSON type conversion as newEntity() and patchEntity() do not do JSON conversions.
     *
     * See also $this->expandEntities() to find out how you can access records by passing in IDs
     *
     * Built in trap to prevent sending of duplicate emails. Duplicate emails are based on:
     *  - email_to +  subject + view_vars
     *  - to be sent within X hours of an existing email
     *
     * @param array $dataToSave
     * @return Message|bool
     */
    public function createMessage(array $dataToSave = []): Message|bool
    {
        /**
         * @var Message $message
         * @var Message $existingMessage
         */
        $defaultData = $this->getDefaultMessageProperties();
        $dataToSave = array_merge($defaultData, $dataToSave);
        $message = $this->newEmptyEntity();

        //use direct assignment as opposed to patchEntity()
        foreach ($dataToSave as $k => $data) {
            $message->$k = $data;
        }

        //make sure priority conforms
        if (!in_array($message->priority, [1, 2, 3, 4, 5])) {
            $message->priority = 3;
        }

        //make sure email_format conforms
        $message->email_format = strtolower($message->email_format);
        if (!in_array(strtolower($message->email_format), ['html', 'text', 'both'])) {
            $message->email_format = 'html';
        }


        if (empty($message->hash_sum)) {
            $findEmailTo = $message->email_to;
            $findSubject = $message->subject;
            $findViewVars = json_decode(json_encode($message->view_vars), true);
            $message->hash_sum = sha1(json_encode([$findEmailTo, $findSubject, $findViewVars]));
        }

        //====trap email base on email_to + subject + view_vars========================================
        $trapTimeInHours = 36;
        $activationLow = (new DateTime($message->activation))->subHours($trapTimeInHours);
        $activationHigh = (new DateTime($message->activation))->addHours($trapTimeInHours);

        $existingMessage = $this->find('all')
            ->select(['id'], true)
            ->where(['activation >=' => $activationLow->format("Y-m-d H:i:s"), 'activation <=' => $activationHigh->format("Y-m-d H:i:s")])
            ->where(['hash_sum' => $message->hash_sum])
            ->where([1 => 1])
            ->first();

        if ($existingMessage) {
            //modify this record so that it appears already sent.
            $message->lock_code = mt_rand(1, mt_getrandmax());
            $message->started = new DateTime();
            $message->completed = new DateTime();
            $message->smtp_code = 99;
            $message->smtp_message = 'Email Trapped.';
        }
        //=============================================================================================


        if (!$this->save($message)) {
            return false;
        }

        //save message indexing for faster searching
        try {
            /** @var UsersTable $Users */
            $Users = TableRegistry::getTableLocator()->get('Users');

            $fromEmails = [];
            if (is_array($message->email_from)) {
                $fromEmails = $message->email_from;
            }
            foreach ($fromEmails as $k => $v) {
                if (is_numeric($k)) {
                    $emailAddress = $v;
                } else {
                    $emailAddress = $k;
                }
                $user = $Users->find('all')->where(['email' => $emailAddress])->first();
                if ($user) {
                    $messageConnection = $this->MessageConnections->newEmptyEntity();
                    $messageConnection->user_link = $user->id;
                    $messageConnection->message_link = $message->id;
                    $messageConnection->direction = 'from';
                    $this->MessageConnections->save($messageConnection);
                }
            }

            $toEmails = [];
            if (is_array($message->email_to)) {
                $toEmails = array_merge($toEmails, $message->email_to);
            }
            if (is_array($message->email_cc)) {
                $toEmails = array_merge($toEmails, $message->email_cc);
            }
            if (is_array($message->email_bcc)) {
                $toEmails = array_merge($toEmails, $message->email_bcc);
            }
            foreach ($toEmails as $k => $v) {
                if (is_numeric($k)) {
                    $emailAddress = $v;
                } else {
                    $emailAddress = $k;
                }
                $user = $Users->find('all')->where(['email' => $emailAddress])->first();
                if ($user) {
                    $messageConnection = $this->MessageConnections->newEmptyEntity();
                    $messageConnection->user_link = $user->id;
                    $messageConnection->message_link = $message->id;
                    $messageConnection->direction = 'to';
                    $this->MessageConnections->save($messageConnection);
                }
            }

        } catch (\Throwable $exception) {
        }

        return $message;
    }


    /**
     * Count how many Messages are ready to send
     *
     * @return int|null
     */
    public function getReadyToRunCount(): ?int
    {
        $messageQuery = $this->buildQueryForMessages();
        return $messageQuery->count();
    }

    /**
     * @param string|null $typeLimit email or sms
     * @return Message|bool|array|null
     */
    public function getNextMessage(string $typeLimit = null): Message|bool|array|null
    {
        //generate RND number and double check not in use
        $rnd = mt_rand(1, mt_getrandmax());
        $count = $this->find('all')->where(['lock_code' => $rnd])->count();
        if ($count > 0) {
            return false;
        }

        //prevent deadlocks
        try {
            //lock the row first with the RND number
            $messageRowLockSubQuery = $this->buildQueryForMessagesRowLock();
            if ($typeLimit) {
                $messageRowLockSubQuery = $messageRowLockSubQuery->where(['Messages.type' => $typeLimit]);
            }
            $query = $this->updateQuery();
            $res = $query
                ->set(['lock_code' => $rnd])
                ->where(['id' => $messageRowLockSubQuery])
                ->rowCountAndClose();
        } catch (\Throwable $e) {
            return false;
        }

        if ($res == 0) {
            //no messages to send
            return false;
        }

        $messageRetryLimit = Configure::read("Settings.message_background_service_retry_limit");
        $messageRetryLimit = max(1, $messageRetryLimit);
        $messageRetry = 0;
        while ($messageRetry < $messageRetryLimit) {
            //prevent deadlocks
            try {
                //now get the locked row based on the RND number
                /**
                 * @var Message $message
                 */
                $message = $this->find('all')->where(['lock_code' => $rnd])->first();

                if ($message) {
                    $timeObjCurrent = new DateTime();
                    $message->started = $timeObjCurrent;
                    $this->save($message);
                    return $message;
                } else {
                    return false;
                }
            } catch (\Throwable $e) {
                $messageRetry++;
            }
        }

        return false;
    }

    /**
     * Returns a query of Messages that can be run
     *
     * @return Query
     */
    public function buildQueryForMessagesRowLock(): Query
    {
        $timeObjCurrent = new DateTime();

        $selectList = [
            "Messages.id",
        ];
        $messageQuery = $this->find('all')
            ->select($selectList)
            ->where(['Messages.lock_code IS NULL'])
            ->where(['Messages.started IS NULL'])
            ->where(['OR' => ['Messages.activation <=' => $timeObjCurrent, 'Messages.activation IS NULL']])
            ->where(['OR' => ['Messages.expiration >=' => $timeObjCurrent, 'Messages.expiration IS NULL']])
            ->orderByAsc('Messages.priority')
            ->orderByAsc('Messages.id')
            ->limit(1);

        return $messageQuery;
    }

    /**
     * Returns a query of Messages that can be run
     *
     * @return Query
     */
    public function buildQueryForMessages(): Query
    {
        $timeObjCurrent = new DateTime();

        $messageQuery = $this->find('all')
            ->where(['Messages.started IS NULL'])
            ->where(['OR' => ['Messages.activation <=' => $timeObjCurrent, 'Messages.activation IS NULL']])
            ->where(['OR' => ['Messages.expiration >=' => $timeObjCurrent, 'Messages.expiration IS NULL']])
            ->orderByAsc('Messages.priority')
            ->orderByAsc('Messages.id');

        return $messageQuery;
    }

    /**
     * Expands out Entity IDs into fully blown Entities
     *
     * Example $entities:
     * [
     *      'user' => 1,
     *      'artifacts' => [1, 2, 3, 4],
     *      'pings' => ['table' => 'users', 'id' => 2],
     *      'pongs' => ['table' => 'users', 'id' => [3, 4, 5]],
     * ]
     *
     * As you can see from above
     *  - you can use the short form of just the IDs (int|int[])
     *  - you can use the long form where you define the table and ids
     *
     * Tables
     * - if you use the short form, we inflect the table name into its plural form (e.g. 'user' to 'users' above)
     * - if you use the long form, the table name is NOT inflected into it plural form. May lead to errors.
     *
     * The function will always deliver back Entities based in how you passed in the IDs
     *  - int 1 will deliver back $query->first()
     *  - array [1] will deliver back $query->toArray()
     *  - array [1,2,3,4] will deliver back $query->toArray()
     *
     * @param array $entities
     * @param bool $hydrate
     * @return array
     */
    public function expandEntities(array $entities, bool $hydrate = true): array
    {
        $expandedEntities = [];
        foreach ($entities as $name => $inputsToExpand) {

            $queryFirst = false;

            if (isset($inputsToExpand['table'])) {
                $table = $inputsToExpand['table'];
                unset($inputsToExpand['table']);
            } else {
                $table = Inflector::pluralize($name);
            }

            $ids = [0];
            //extract from long form array
            if (isset($inputsToExpand['id'])) {
                $ids = $inputsToExpand['id'];
                unset($inputsToExpand['id']);
                if (is_int($ids)) {
                    $ids = [$ids];
                    $queryFirst = true;
                }
            }

            //extract from short form integer
            if (is_int($inputsToExpand)) {
                $ids = [$inputsToExpand];
                $queryFirst = true;
            }

            //extract from short form integer[]
            if (is_array($inputsToExpand)) {
                $inputsToExpand = array_values($inputsToExpand);
                if (isSeqArr($inputsToExpand)) {
                    $ids = $inputsToExpand;
                }
            }

            $Table = TableRegistry::getTableLocator()->get($table);
            $query = $Table->find('all')->where(['id IN' => $ids]);

            if ($hydrate) {
                $query = $query->enableHydration();
            } else {
                $query = $query->disableHydration();
            }

            if ($queryFirst) {
                $expandedEntities[$name] = $query->first();
            } else {
                $expandedEntities[$name] = $query->toArray();
            }

        }

        return $expandedEntities;
    }


    /**
     * Wrapper function to redirect to EMAIL or SMS or FAX function.
     *
     *
     * @param Message|int $idOrMessage
     * @return Message|false
     */
    public function sendMessage(Message|int $idOrMessage): Message|false
    {
        /** @var Message $message */
        $message = $this->asEntity($idOrMessage);

        if (!$message) {
            return false;
        }

        if (in_array($message->type, ['email', 'mail'])) {
            return $this->sendEmailMessage($message);
        } elseif (in_array($message->type, ['mms', 'sms'])) {
            return $this->sendSmsMessage($message);
        } elseif (in_array($message->type, ['fax', 'facsimile'])) {
            return $this->sendFaxMessage($message);
        }

        return false;
    }


    /**
     * Workhorse to send a Message as an email.
     *
     * Best to use a Background Service to call this method as it hangs the App till the Message is sent.
     *
     * @param Message|int $idOrMessage
     * @return Message|false
     */
    public function sendEmailMessage(Message|int $idOrMessage): Message|false
    {
        /** @var Message $message */
        $message = $this->asEntity($idOrMessage);

        if (!$message) {
            return false;
        }

        //log start time
        $message->started = new DateTime();

        /* render the body of the email as text/html */
        $render = new Renderer();

        $additionalViewVars = [
            'domain' => $message->domain,
            'beacon_hash' => $message->beacon_hash,
        ];

        if ($message->view_vars) {
            $viewVars = $message->view_vars;
            if (isset($viewVars['entities'])) {
                $viewVars['entities'] = $this->expandEntities($viewVars['entities']);
            }
            $viewVars = (array_merge($additionalViewVars, $viewVars));
        } else {
            $viewVars = $additionalViewVars;
        }

        $render->viewBuilder()
            ->setTemplate($message->template)
            ->setLayout($message->layout)
            ->setVars($viewVars);

        if ($message->email_format) {
            if ($message->email_format === strtolower('both')) {
                $renderFormat = ['html', 'text'];
            } else {
                $renderFormat = [strtolower($message->email_format)];
            }
        } else {
            $renderFormat = ['html'];
        }
        $bodyData = ($render->render('', $renderFormat));
        if (isset($bodyData['html'])) {
            try {
                $bodyData['html'] = CssInliner::fromHtml($bodyData['html'])->inlineCss()->render();
            } catch (Throwable $exception) {
            }
        }

        /* convert message entity to cake mailer message */
        $cakeMailerMessage = new \Cake\Mailer\Message();
        //subject
        if ($message->subject) {
            $cakeMailerMessage->setSubject($message->subject);
        }

        //body
        if ($message->email_format) {
            $cakeMailerMessage->setEmailFormat($message->email_format);
        }
        if (isset($bodyData['html'])) {
            $cakeMailerMessage->setBodyHtml($bodyData['html']);
        }
        if (isset($bodyData['text'])) {
            $cakeMailerMessage->setBodyHtml($bodyData['text']);
        }

        //to and from
        if ($message->sender) {
            $cakeMailerMessage->setSender($message->sender);
        }
        if ($message->email_from) {
            $cakeMailerMessage->setFrom($message->email_from);
        }
        if ($message->email_to) {
            $cakeMailerMessage->setTo($message->email_to);
        }
        if ($message->email_cc) {
            $cakeMailerMessage->setCc($message->email_cc);
        }
        if ($message->email_bcc) {
            $cakeMailerMessage->setBcc($message->email_bcc);
        }
        if ($message->reply_to) {
            $cakeMailerMessage->setReplyTo($message->reply_to);
        }
        if ($message->read_receipt) {
            $cakeMailerMessage->setReadReceipt($message->read_receipt);
        }

        //headers
        if ($message->headers) {
            $cakeMailerMessage->setHeaders($message->headers);
        }
        if ($message->priority) {
            $cakeMailerMessage->setPriority($message->priority);
        }

        //domain
        if ($message->domain) {
            $cakeMailerMessage->setDomain($message->domain);
        }

        //profile??
        //if ($message->profile) {
        //    $mailer->setProfile($message->profile);
        //}

        /* send the message via a transport */
        try {
            $transport = TransportFactory::get('default');
            $sendResult = $transport->send($cakeMailerMessage);
            if ($sendResult) {
                $message->smtp_code = 1;
                $message->smtp_message = __("Email Sent.");

                $message->completed = new DateTime();
                $message->errors_thrown = null;
            } else {
                $message->smtp_code = 0;
                $message->smtp_message = __("Email Failed.");

                $message->completed = null;
                $message->errors_thrown = __("Email Failed.");

                if ($message->errors_retry < $message->errors_retry_limit) {
                    $message->errors_retry = $message->errors_retry + 1;
                    $message->started = null;
                    $message->completed = null;
                    $message->lock_code = null;
                }
            }
        } catch (Throwable $e) {
            $errorsThrown = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ];
            $errorsThrown = json_decode(json_encode($errorsThrown), true);

            $message->smtp_code = 98;
            $message->smtp_message = __("Transport Error.");

            $message->completed = null;
            $message->errors_thrown = $errorsThrown;

            if ($message->errors_retry < $message->errors_retry_limit) {
                $message->errors_retry = $message->errors_retry + 1;
                $message->started = null;
                $message->completed = null;
                $message->lock_code = null;
            }
        }

        //update the message entity with the result
        $this->save($message);

        return $message;
    }

    /**
     * Simple mechanism for resending a Message.
     *
     * Reset the DB flags as not sent, hence Message Background Service will pick up and resend.
     * Will roll forward Seed and Message Activation and Expiration dates.
     *
     * @param Message|int $idOrMessage
     * @param bool $asClone create a cloned record in the DB to send (i.e. keep original as is)
     * @return Message|bool
     */
    public function resendMessage(Message|int $idOrMessage, bool $asClone = true): Message|bool
    {
        $message = $this->asEntity($idOrMessage);
        if (!$message) {
            return false;
        }

        if (isset($message->view_vars)) {
            if (isset($message->view_vars['seed']['id'])) {
                /** @var SeedsTable $Seeds */
                $Seeds = TableRegistry::getTableLocator()->get('Seeds');
                $Seeds->rollForwardActivationExpiration($message->view_vars['seed']['id']);
                $Seeds->decreaseBid($message->view_vars['seed']['id']);
            }
        }

        $this->rollForwardActivationExpiration($message);

        $patch = [
            'started' => null,
            'completed' => null,
            'smtp_code' => null,
            'smtp_message' => null,
            'lock_code' => null,
            'errors_thrown' => null,
            'errors_retry' => 0,
        ];
        $message = $this->patchEntity($message, $patch);

        //clone the message
        /** @var MessageConnection[] $messageConnections */
        $messageConnections = false;
        if ($asClone) {
            $messageConnections = $this->MessageConnections->find('all')->where(['message_link' => $message->id]);
            $message = clone $message;
            unset($message->id);
            unset($message->created);
            unset($message->modified);
            $message->setNew(true);
            $message->beacon_hash = sha1(Security::randomString(1024));
        }

        $message = $this->save($message);

        //clone the message_connections
        if ($message) {
            if ($messageConnections) {
                foreach ($messageConnections as $messageConnection) {
                    try {
                        $messageConnection = $messageConnection->toArray();
                        unset($messageConnection['id'], $messageConnection['created']);
                        $messageConnection['message_link'] = $message->id;
                        $messageConnection = $this->MessageConnections->newEntity($messageConnection);
                        $this->MessageConnections->save($messageConnection);
                    } catch (Throwable $exception) {
                    }
                }
            }
        }

        if ($message) {
            return true;
        } else {
            return false;
        }
    }

    public function sendSmsMessage(Message|int $idOrMessage): Message|false
    {
        /** @var Message $message */
        $message = $this->asEntity($idOrMessage);

        if (!$message) {
            return false;
        }

        /**
         * Update with any SMS Gateway Class
         */
        $SmsGateway = (new SmsGatewayFactory())->getSmsGateway();;

        //send the SMS message
        return $SmsGateway->sendSms($message);
    }


    public function sendFaxMessage(Message|int $idOrMessage): Message|false
    {
        return false;
    }
}
