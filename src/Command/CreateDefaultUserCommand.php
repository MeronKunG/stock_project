<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\WarehouseInfo;
use App\Repository\WarehouseInfoRepository;

class CreateDefaultUserCommand extends Command
{
    protected static $defaultName = 'app:create-default-user';

    private $entityManager;
    private $passwordEncoder;
    private $userRepository;
    private $warehouseRepository;

    public function __construct(
        string $name = null,
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository,
        WarehouseInfoRepository $warehouseRepository
    ) {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
        $this->warehouseRepository=$warehouseRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Create default user');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $warehouseCode = $this->warehouseRepository->findOneBy(array('warehouseCode' => 'W00'));
        if($warehouseCode == null) {
            $warehouseInfo = new WarehouseInfo();
            $warehouseInfo->setWarehouseCode('W00');
            $warehouseInfo->setWarehouseName('HQ Warehouse');
            $warehouseInfo->setWarehouseStatus(0);

            $user = new User();
            $user->setUsername("admin");
            $user->setEmail("admin@local");
            $user->setRoles(['ROLE_USER', 'ROLE_SUPER_USER', 'ROLE_ADMIN']);
            $user->setUserStatus(1);
            $user->setDefaultWarehouse($warehouseInfo);
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                'password'
            ));

            $this->entityManager->persist($warehouseInfo);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $io->success('User admin has been created.');

//        } elseif (!$this->userRepository->count(['username' => 'admin'])) {
        } else {
            $io->note("User admin already exists.");
        }


    }
}
