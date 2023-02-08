<?php

declare(strict_types=1);

namespace Mailer;

final class Mail
{
    private string $subject;
    private string $text;
    private ?string $html;
    /** @var Attachment[] */
    private array $attachments;

    public function __construct(string $subject, string $text, ?string $html = null, array $attachments = [])
    {
        $this->subject = $subject;
        $this->text = $text;
        $this->html = $html;
        $this->attachments = $attachments;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getHtml(): ?string
    {
        return $this->html;
    }

    /** @return Attachment[] */
    public function getAttachments(): array
    {
        return $this->attachments;
    }
}