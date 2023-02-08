<?php

declare(strict_types=1);

namespace Mailer;

use Exception;
use GuzzleHttp\Client as HttpClient;

final class Client
{
    private const URL = 'https://send.api.mailtrap.io/api/send';
    private const TOKEN_HEADER = 'Api-Token';
    private const CONTENT_TYPE = 'application/json';

    private string $token;
    private HttpClient $httpClient;

    public function __construct(HttpClient $httpClient, string $token)
    {
        $this->token = $token;
        $this->httpClient = $httpClient;
    }

    public function send(Sender $sender, Receiver $receiver, Mail $mail): void
    {
        $response = $this->request($this->getBody($sender, $receiver, $mail));

        if (array_key_exists('errors', $response)) {
            throw new Exception(json_encode($response['errors']));
        }
    }

    private function getBody(Sender $sender, Receiver $receiver, Mail $mail): array
    {
        $body = [
            'from' => [
                'email' => $sender->getEmail(),
                'name' => $sender->getName(),
            ],
            'to' => [
                [
                    'email' => $receiver->getEmail(),
                    'name' => $receiver->getName(),
                ]
            ],
            'subject' => $mail->getSubject(),
            'text' => $mail->getText()
        ];

        $html = $mail->getHtml();
        if (!is_null($html)) {
            $body['html'] = $html;
        }

        $attachments = $mail->getAttachments();
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $body['attachments'][] = [
                    'content' => $attachment->getContent(),
                    'type' => $attachment->getMimeType(),
                    'filename' => $attachment->getFileName(),
                    'disposition' => $attachment->getDisposition(),
                    'content_id' => $attachment->getFileName()
                ];
            }
        }

        return $body;
    }

    private function request(array $body): array
    {
        $response = $this->httpClient->post(
            self::URL,
            [
                'body' => json_encode($body),
                'headers' => [self::TOKEN_HEADER => $this->token, 'Content-Type' => self::CONTENT_TYPE],
                'http_errors' => false
            ]
        );

        return json_decode($response->getBody()->getContents(), true);
    }
}