<?php declare(strict_types=1);

namespace App\Command;

use App\Entity\ApiUser;
use App\Repository\ApiUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

#[AsCommand(
    name: 'app:add-api-user',
    description: 'Add api user by email',
)]
class AddApiUserCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User Email')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $repo = $this->em->getRepository(ApiUser::class);
        Assert::isInstanceOf($repo, ApiUserRepository::class);
        $apiUser = $repo->findOneBy(['email' => $email]);
        if(null !== $apiUser) {
            $io->error('User already exists, API Key: '.$apiUser->getApiKey());
            return Command::FAILURE;
        }
        $apiUser = new ApiUser();
        $apiUser->setEmail($email);
        $apiUser->setRoles(['ROLE_API']);
        $this->em->persist($apiUser);
        $this->em->flush();

        $io->success('New API User created, API Key: '.$apiUser->getApiKey());

        return Command::SUCCESS;
    }
}
