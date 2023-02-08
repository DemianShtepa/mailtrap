<?php

declare(strict_types=1);

namespace Mailer;

final class Attachment
{
    private string $content;
    private string $fileName;
    private string $mimeType;
    private string $disposition;

    public function __construct(string $content, string $fileName, string $mimeType, string $disposition)
    {
        $this->content = $content;
        $this->fileName = $fileName;
        $this->mimeType = $mimeType;
        $this->disposition = $disposition;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getDisposition(): string
    {
        return $this->disposition;
    }
}