<?php

namespace App\MessageGateways;

use App\Model\Entity\Message;

interface SmsGatewayInterface
{

    /**
     * @param Message $message
     * @return false|Message
     */
    public function sendSms(Message $message): false|Message;

    public function getSms(string $messageId): false|array;

    /**
     * Most providers will have an SMS and MMS balance
     *
     * @param string $type
     * @return false|array
     */
    public function getBalance(string $type = 'sms'): false|string;

    public function getAccount(): false|array;

}
