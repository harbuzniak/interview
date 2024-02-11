<?php declare(strict_types=1);

namespace App\Tests\Functional;

use App\Email\EmailSenderInterface;
use App\Entity\Person;

class EmailSenderDummy implements EmailSenderInterface
{
    public array $sentEmails = [];

    public function sendWelcomeEmail(Person $person): void
    {
        $this->sentEmails[] = $person;
    }
}
