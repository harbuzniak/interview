<?php declare(strict_types=1);

namespace App\Service\Schedulable;

use Cron\CronExpression;

/**
 * @template TRequest
 */
interface SchedulableInterface
{
    /**
     * https://crontab.guru/
     * @return array<string, object|null>
     */
    public function getCronExpressions(): array;

    /**
     * @return list<TRequest>
     */
    public function getMessagesToSchedule(CronExpression $cronExpression, ?object $scheduleParams = null): array;
}
