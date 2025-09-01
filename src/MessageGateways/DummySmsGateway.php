<?php

namespace App\MessageGateways;

use App\Model\Entity\Message;
use App\Model\Table\MessagesTable;
use arajcany\ToolBox\Utility\Security\Security;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

/**
 * Use this dummy gateway when you don't need to send real SMS Messages.
 */
class DummySmsGateway implements SmsGatewayInterface
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
        $guid = Security::guid();
        $currentDatetime = new DateTime();
        $message->started = $currentDatetime;
        $message->completed = $currentDatetime;
        $message->smtp_code = 200;
        $message->smtp_message = 'Success';
        $message->api_response = '{
    "status_code": 200,
    "headers": {},
    "body": {
                "meta": {
                    "code": 200,
                    "status": "SUCCESS"
                },
                "msg": "Queued",
                "data": {
                    "messages": [
                        {
                            "message_id": "' . $guid . '",
                            "from" : "YOUR_SENDER_ID",
                            "to": "' . $message->email_to . '",
                            "body": "This is not a real SMS - just an example of a possible API response.",
                            "date": "' . $currentDatetime->format('Y-m-d H:i:s') . '"
                        }
                    ],
                    "total_numbers": 1,
                    "success_number": 1,
                    "credits_used": 1
                }
            }
}';

        return $this->Messages->save($message);
    }

    public function getSms(string $messageId): false|array
    {
        $rndMins = mt_rand(60, 6000);
        $randomDatetime = (new DateTime())->subMinutes($rndMins);

        $sampleResponse = '{
    "meta": {
        "code": 200,
        "status": "SUCCESS"
    },
    "msg": "Found SMS",
    "data": [
        {
            "to": "+61NNNNNNNNN",
                "body": "This is not a real SMS - just an example of a possible API response.",
            "sent_time": "' . $randomDatetime->format('Y-m-d H:i:s') . '",
            "message_id": "' . $messageId . '",
            "status": "Delivered"
        }
    ]
}';
        return json_decode($sampleResponse, JSON_OBJECT_AS_ARRAY);
    }

    public function getBalance($type = 'sms'): false|string
    {
        return '5831.43';
    }

    public function getAccount(): false|array
    {
        $sampleResponse = '{
    "meta": {
        "code": 200,
        "status": "SUCCESS"
    },
    "msg": "Account Data",
    "data": {
        "account_name": "John Citizen",
        "account_email": "john.citizen@example.com",
        "sms_balance": "5831.43",
        "mms_balance": "1006.00"
    }
}';
        return json_decode($sampleResponse, JSON_OBJECT_AS_ARRAY);
    }
}
