<?php declare(strict_types=1);

namespace App\Message;

final class SendEmailMessage
{
    public function __construct(private readonly int $personId)
    {
    }

    public function getPersonId(): int
    {
        return $this->personId;
    }

}
