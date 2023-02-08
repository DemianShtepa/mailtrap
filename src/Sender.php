<?php

declare(strict_types=1);

namespace Mailer;

use Webmozart\Assert\Assert;

final class Sender
{
    private string $name;
    private string $email;

    public function __construct(string $name, string $email)
    {
        Assert::email($email);
        $this->name = $name;
        $this->email = $email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}