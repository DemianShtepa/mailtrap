<?php

declare(strict_types=1);

namespace Tests;

use DG\BypassFinals;
use GuzzleHttp\Client as HttpClient;
use Mailer\Attachment;
use Mailer\Client;
use Mailer\Mail;
use Mailer\Receiver;
use Mailer\Sender;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class ClientTest extends TestCase
{
    private const TOKEN = 'token';

    private MockObject $httpClient;
    private Client $client;

    protected function setUp(): void
    {
        BypassFinals::enable();

        parent::setUp();

        $this->httpClient = $this->createMock(HttpClient::class);
        $this->client = new Client($this->httpClient, self::TOKEN);
    }

    public function testItSendsMail(): void
    {
        $sender = $this->mockSender();
        $receiver = $this->mockReceiver();
        $mail = $this->mockMail();

        $stream = $this->createMock(StreamInterface::class);
        $stream
            ->expects($this->once())
            ->method('getContents')
            ->willReturn('{"success":true}');

        $this->mockHttpClient($stream);

        $this->client->send(
            $sender,
            $receiver,
            $mail
        );
    }

    public function testItReturnsException(): void
    {
        $this->expectExceptionMessage('["invalid Content-Type: mime: no media type"]');

        $sender = $this->mockSender();
        $receiver = $this->mockReceiver();
        $mail = $this->mockMail();

        $stream = $this->createMock(StreamInterface::class);
        $stream
            ->expects($this->once())
            ->method('getContents')
            ->willReturn('{"errors":["invalid Content-Type: mime: no media type"]}');

        $this->mockHttpClient($stream);

        $this->client->send(
            $sender,
            $receiver,
            $mail
        );
    }

    private function mockSender(): MockObject
    {
        $sender = $this->createMock(Sender::class);
        $sender
            ->expects($this->once())
            ->method('getEmail')
            ->willReturn('sender@mail.com');
        $sender
            ->expects($this->once())
            ->method('getName')
            ->willReturn('Sender');

        return $sender;
    }

    private function mockReceiver(): MockObject
    {
        $receiver = $this->createMock(Receiver::class);
        $receiver
            ->expects($this->once())
            ->method('getEmail')
            ->willReturn('receiver@mail.com');
        $receiver
            ->expects($this->once())
            ->method('getName')
            ->willReturn('Receiver');

        return $receiver;
    }

    private function mockMail(): MockObject
    {
        $attachment = $this->createMock(Attachment::class);
        $attachment
            ->expects($this->once())
            ->method('getContent')
            ->willReturn('Content');
        $attachment
            ->expects($this->exactly(2))
            ->method('getFileName')
            ->willReturn('index.html');
        $attachment
            ->expects($this->once())
            ->method('getMimeType')
            ->willReturn('text/html');
        $attachment
            ->expects($this->once())
            ->method('getDisposition')
            ->willReturn('attachment');
        $mail = $this->createMock(Mail::class);
        $mail
            ->expects($this->once())
            ->method('getSubject')
            ->willReturn('Subject');
        $mail
            ->expects($this->once())
            ->method('getText')
            ->willReturn('Text');
        $mail
            ->expects($this->once())
            ->method('getHtml')
            ->willReturn('<html>html</html>');
        $mail
            ->expects($this->once())
            ->method('getAttachments')
            ->willReturn([$attachment]);

        return $mail;
    }

    private function mockHttpClient(MockObject $stream): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($stream);
        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                'https://send.api.mailtrap.io/api/send',
                [
                    'body' => '{"from":{"email":"sender@mail.com","name":"Sender"},"to":[{"email":"receiver@mail.com","name":"Receiver"}],"subject":"Subject","text":"Text","html":"<html>html<\/html>","attachments":[{"content":"Content","type":"text\/html","filename":"index.html","disposition":"attachment","content_id":"index.html"}]}',
                    'headers' => ['Api-Token' => self::TOKEN, 'Content-Type' => 'application/json'],
                    'http_errors' => false
                ]
            )
            ->willReturn($response);
    }
}