<?php


namespace App\Command;
/*
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'AddUserCommand',
    description: 'Add a short description for your command',
)]
class AddUserCommand extends Command
{
    protected static $defaultName = 'app:add-user';
    private $entityManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a new user')
            ->setHelp('This command allows you to add a new user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = $io->ask('Enter username');
        $password = $io->askHidden('Enter password');
        $mfaEnabled = $io->confirm('Enable MFA (Multi-Factor Authentication)?');

        $user = new User();
        $user->setUsername($username);
        $encodedPassword = $this->passwordEncoder->encodePassword($user, $password);
        $user->setPassword($encodedPassword);
        $user->setMfaEnabled($mfaEnabled);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('User successfully added.');

        return Command::SUCCESS;
    }
    
    

}*/
?>