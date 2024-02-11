<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Email\EmailSenderInterface;
use App\Entity\Person;
use App\Message\SendEmailMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendEmailMessageHandler
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EmailSenderInterface $sender,
        private ?LoggerInterface $logger
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function __invoke(SendEmailMessage $message): void
    {
        $personId = $message->getPersonId();
        $person = $this->em->find(Person::class, $personId);
        if ($person === null) {
            $this->logger->error('SendEmailMessageHandler: Person not found', ['id' => $personId]);
            return;
        }
        $this->sender->sendWelcomeEmail($person);
    }
}
