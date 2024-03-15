<?php declare(strict_types=1);

namespace App\Command;

use App\Service\Schedulable\SchedulableHolder;
use Cron\CronExpression;
use DateTimeImmutable;
use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(
    name: 'app:scheduler',
    description: 'Scheduler command',
)]
class SchedulerCommand extends Command
{
    private bool $skipScheduler = false;

    public function __construct(
        private readonly SchedulableHolder $schedulableHolder,
        private readonly MessageBusInterface $bus,
        private readonly SerializerInterface $serializer,
        private ?LoggerInterface $logger
    ) {
        parent::__construct();
        $this->logger = $this->logger ?? new NullLogger();
    }

    public function configure(): void
    {
        $this->addOption('print-schedule', null, InputOption::VALUE_NONE);
        $this->addOption('skip-scheduler', null, InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $loop = Loop::get();
        $this->skipScheduler = $input->getOption('skip-scheduler');

        if ($input->getOption('print-schedule') === true) {
            $this->printSchedule($output);
            return self::SUCCESS;
        }

        $output->writeln('[app:scheduler] Started');

        foreach ($this->schedulableHolder->getItems() as $useCase) {
            foreach ($useCase->getCronExpressions() as $cronExpression => $scheduleParams) {
                $this->addCronTimer(
                    $loop,
                    $cronExpression,
                    function () use ($useCase, $scheduleParams, $output, $cronExpression): void {
                        $now = new DateTimeImmutable();
                        $output->writeln($now->format('c') . ' Executing ' . get_class($useCase));

                        $messagesToSchedule = $useCase->getMessagesToSchedule(
                            new CronExpression($cronExpression),
                            $scheduleParams
                        );

                        foreach ($messagesToSchedule as $message) {
                            $output->writeln('--' . $this->serializer->serialize($message, 'json'));
                            $this->bus->dispatch($message);
                        }
                    }
                );
            }
        }

        $loop->run();

        return self::SUCCESS;
    }

    private function addCronTimer(LoopInterface $loop, string $expr, callable $callback): void {
        if ($this->skipScheduler) {
            call_user_func($callback);

            return;
        }

        $cron = new CronExpression($expr);
        try {
            $timeDiff = $cron->getNextRunDate()->getTimestamp() - time();
            $loop->addTimer($timeDiff, function () use ($loop, $expr, $callback): void {
                call_user_func($callback);
                $this->addCronTimer($loop, $expr, $callback);
            });
        } catch (Exception $e) {
        }

    }

    private function printSchedule(OutputInterface $output): void
    {
        $schedule = [];
        foreach ($this->schedulableHolder->getItems() as $useCase) {
            foreach ($useCase->getCronExpressions() as $cronExpression => $scheduleParams) {
                $cronObject = new CronExpression($cronExpression);
                $nextRunDate = $cronObject->getNextRunDate();
                $schedule[] = [
                    'timestamp' => $nextRunDate->getTimestamp(),
                    'nextRun' => $nextRunDate->format('c'),
                    'cron' => $cronExpression,
                    'class' => get_class($useCase),
                    'params' => json_encode((array)$scheduleParams),
                ];
            }
        }
        if ($schedule !== []) {
            usort($schedule, function ($a, $b): int {
                return $a['timestamp'] <=> $b['timestamp'];
            });
            $table = new Table($output);
            $table
                ->setHeaders(array_keys($schedule[0]))
                ->setRows($schedule);
            $table->render();
        }
    }
}
