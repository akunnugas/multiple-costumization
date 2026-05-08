<?php

namespace Modules\Core\Transport;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class NotificationMailTransport extends AbstractTransport
{
    private string $host;
    private string $appId;
    private string $appSecret;

    const CHANNEL = 'email';

    /**
     * Create a new transport instance.
     *
     * @param string $host
     * @param string $appId
     * @param string $appSecret
     */
    public function __construct(string $host, string $appId, string $appSecret)
    {
        parent::__construct();

        $this->host = $host;
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    /**
     * Send message.
     *
     * @param SentMessage $message
     * @return void
     * @throws RequestException
     */
    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $response = Http::withHeaders([
            'App-Id' => $this->appId,
            'App-Secret' => $this->appSecret
        ])->post($this->host . '/api/v1/notifications', [
            'channel' => static::CHANNEL,
            'message' => $email->getBody()->getBody(),
            'from' => collect($email->getFrom())->all()[0]->getAddress(),
            'to' => collect($email->getTo())->all()[0]->getAddress(),
            'subject' => $email->getSubject(),
            'sender_name' => config('app.name')
        ]);

        $response->throw();
    }

    /**
     * Get the string representation of the transport.
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'notification-mail';
    }
}
