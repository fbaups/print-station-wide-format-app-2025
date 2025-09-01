<?php

namespace App\MessageGateways;

use App\Model\Entity\Message;
use App\Model\Table\MessagesTable;
use Cake\Cache\Cache;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use GuzzleHttp\Client;

class CellcastSmsGateway implements SmsGatewayInterface
{
    private Table|MessagesTable $Messages;
    private null|string $username;
    private null|string $password;
    private null|string $appKey;
    private null|string $appId;


    public function __construct($username = null, $password = null, $appKey = null, $appId = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->appKey = $appKey;
        $this->appId = $appId;
        $this->Messages = TableRegistry::getTableLocator()->get('Messages');
    }


    /**
     * @param Message $message
     * @return false|Message
     */
    public function sendSms(Message $message): false|Message
    {
        $startTime = new DateTime();

        try {
            $url = 'https://cellcast.com.au/api/v3/send-sms';
            $fields = [
                'sms_text' => $message->subject,
                'numbers' => [$message->email_to]
            ];

            $headers = array(
                'APPKEY' => $this->appKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            );

            $client = new Client();
            $response = $client->post($url, [
                'headers' => $headers,
                'json' => $fields
            ]);
            $statusCode = $response->getStatusCode();
            $headers = $response->getHeaders();
            $body = $response->getBody()->getContents();
            $bodyDecoded = json_decode($body, JSON_OBJECT_AS_ARRAY);
            unset($bodyDecoded['low_sms_alert']);

            //dump($headers, $statusCode, $body);
            $api_response = [
                'status_code' => $statusCode,
                'headers' => $headers,
                'body' => $bodyDecoded,
            ];


            $message->started = $startTime;
            $message->completed = new DateTime();
            $message->smtp_code = $bodyDecoded['meta']['code'];
            $message->smtp_message = $bodyDecoded['msg'];
            $message->api_response = json_encode($api_response);

            return $this->Messages->save($message);

        } catch (\Throwable $exception) {
            $exception->getMessage();
        }

        return false;
    }

    public function getSms(string $messageId): false|array
    {
        try {
            $url = "https://cellcast.com.au/api/v3/get-sms?message_id={$messageId}";

            $headers = array(
                'APPKEY' => $this->appKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            );

            $client = new Client();
            $response = $client->get($url, [
                'headers' => $headers,
            ]);
            $headers = $response->getHeaders();

            return json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);
        } catch (\Throwable $exception) {
            $exception->getMessage();
        }

        return false;
    }

    public function getBalance($type = 'sms'): false|string
    {
        $account = $this->getAccount();
        if ($type === 'sms') {
            return $account['data']['sms_balance'] ?? false;
        } elseif ($type === 'mms') {
            return $account['data']['mms_balance'] ?? false;
        }

        return false;
    }

    public function getAccount(): false|array
    {
        $smsAccount = Cache::read('SmsGateway.account', 'quick_burn');
        if ($smsAccount) {
            return $smsAccount;
        }

        try {
            $url = 'https://cellcast.com.au/api/v3/account';

            $headers = array(
                'APPKEY' => $this->appKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            );

            $client = new Client();
            $response = $client->get($url, [
                'headers' => $headers,
            ]);
            $headers = $response->getHeaders();
            $body = json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);

            Cache::write('SmsGateway.account', $body, 'quick_burn');

            return $body;

        } catch (\Throwable $exception) {
            $exception->getMessage();
        }

        return false;
    }
}
