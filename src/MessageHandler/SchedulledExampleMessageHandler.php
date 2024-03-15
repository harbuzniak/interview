<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\SchedulledExampleMessage;
use App\Service\Schedulable\SchedulableInterface;
use Cron\CronExpression;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SchedulledExampleMessageHandler implements SchedulableInterface
{
    public function __invoke(SchedulledExampleMessage $message)
    {
        // do something with your message
    }

    public function getCronExpressions(): array
    {
        return [
            '* * * * *' => null
        ];
    }

    public function getMessagesToSchedule(CronExpression $cronExpression, ?object $scheduleParams = null): array
    {
        return [new SchedulledExampleMessage()];
    }
}
