<?php declare(strict_types=1);

namespace App\Email;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;

class EmailHelper implements EmailHelperInterface
{
    public function createEmail(mixed $from, mixed $to, array $cc, array $bcc, string $template, string $subject, array $params): Email
    {
        $email = new TemplatedEmail();
        $email
            ->from($from)
            ->to($to)
            ->cc(...$cc)
            ->bcc(...$bcc)
            ->subject($subject)
            ->htmlTemplate("email_templates/{$template}.html.twig")
            ->textTemplate("email_templates/{$template}.txt.twig")
            ->context($params);
        return $email;
    }
}
