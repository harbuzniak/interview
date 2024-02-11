<?php declare(strict_types=1);

namespace App\Email;

use App\Entity\Person;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Webmozart\Assert\Assert;

class EmailSender implements EmailSenderInterface
{
    public function __construct(
        private readonly EmailHelperInterface $helper,
        private readonly MailerInterface $mailer,
        private readonly string $senderEmail,
        private readonly ?string $senderName,
        private readonly LoggerInterface $logger
    )
    {

    }

    public function sendWelcomeEmail(Person $person): void
    {
        Assert::notNull($toEmail = $person->getEmail(), 'Person email is null');
        $subject = null !== ($name = $person->getName()) ? sprintf('Welcome, %s!', $name) : 'Welcome to our website!';
        $email = $this->helper->createEmail(
            new Address($this->senderEmail, $this->senderName),
            $toEmail,
            [],
            [],
            'welcome',
            $subject,
            [
                'name' => $name ?? 'our friend'
            ]
        );
        try {
            $this->mailer->send($email);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to send welcome email', ['to' => $toEmail, 'e' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }
}
