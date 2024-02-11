<?php declare(strict_types=1);

namespace App\Email;

use App\Entity\Person;

interface EmailSenderInterface
{
    public function sendWelcomeEmail(Person $person): void;
}
