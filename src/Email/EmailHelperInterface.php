<?php declare(strict_types=1);

namespace App\Email;

use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

interface EmailHelperInterface
{

    /**
     * @param string|Address $from
     * @param string|Address $to
     * @param array<int, string|Address> $cc
     * @param array<int, string|Address> $bcc
     * @param string $template
     * @param string $subject
     * @param array $params
     * @return Email
     */
    public function createEmail(mixed $from, mixed $to, array $cc, array $bcc, string $template, string $subject, array $params): Email;
}
