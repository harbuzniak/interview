<?php declare(strict_types=1);

namespace App\Tests\Functional;

use App\Kernel;
use DAMA\DoctrineTestBundle\PHPUnit\PHPUnitExtension;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Orm\ManagerRegistryAwareEntityManagerProvider;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Webmozart\Assert\Assert;

class DBSchemaPHPUnitExtension extends PHPUnitExtension
{
    protected static bool $schemaCreated = false;


    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        parent::bootstrap($configuration, $facade, $parameters);
        self::createSchema();
    }

    public static function createSchema(): void
    {
        if (! self::$schemaCreated) {
            $kernel = new Kernel('test', false);
            $kernel->boot();
            $application = new Application($kernel);

            try {
                self::doCreateSchema($application);
            } finally {
                $kernel->shutdown();
            }
            self::$schemaCreated = true;
        }
    }

    private static function doCreateSchema(Application $application): void
    {
        $container = $application->getKernel()
            ->getContainer()
        ;
        /** @var ManagerRegistry|null $manager */
        $manager = $container->get('doctrine');
        Assert::isInstanceOf($manager, ManagerRegistry::class);
        // add the database:drop command to the application and run it
        $command = new DropDatabaseDoctrineCommand($manager);
        $application->add($command);
        $input = new ArrayInput([
            'command' => 'doctrine:database:drop',
            '--force' => true,
        ]);
        $command->run($input, new ConsoleOutput());

        // add the database:create command to the application and run it
        $command = new CreateDatabaseDoctrineCommand($manager);
        $application->add($command);
        $input = new ArrayInput([
            'command' => 'doctrine:database:create',
        ]);
        $command->run($input, new ConsoleOutput());

        // let Doctrine create the database schema (i.e. the tables)
        $provider = new ManagerRegistryAwareEntityManagerProvider($manager);
        $command = new CreateCommand($provider);
        $application->add($command);
        $input = new ArrayInput([
            'command' => 'orm:schema-tool:create'
        ]);
        $command->run($input, new ConsoleOutput());
    }
}
