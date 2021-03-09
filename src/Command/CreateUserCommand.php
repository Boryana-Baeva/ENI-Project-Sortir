<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @property string dataDirectory
 * @property SymfonyStyle io
 * @property EntityManagerInterface entityManager
 * @property UserRepository userRepository
 */
class CreateUserCommand extends Command
{

    public function __construct(EntityManagerInterface $entityManager,
                                string $dataDirectory,
                                UserRepository  $userRepository)
    {
        parent::__construct();
        $this->dataDirectory = $dataDirectory;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;


    }

    protected static $defaultName = 'app:create-user';
    protected static $defaultDescription = 'Importer des données en provenance d\'un fichier CSV ou XML ou YAML';

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
    }
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->createUsers();

        return Command::SUCCESS;
    }

    private function getDataFormFile(): array
    {
        $file = $this->dataDirectory.'random-users.csv';

        $fileExtension = pathinfo($file, PATHINFO_EXTENSION);

        $normalizers = [new ObjectNormalizer()];

        $encoders = [
            new CsvEncoder(),
            new XmlEncoder(),
            new YamlEncoder()
        ];

        $serializer = new Serializer($normalizers, $encoders);

        /** @var string $fileString */
        $fileString  = file_get_contents($file);

        $data = $serializer->decode($fileString, $fileExtension);


        if(array_key_exists('results', $data)){
            return $data['results'];
        }
        return $data;
    }

    private function createUsers():void
    {
        $this->io->section('CREATION DES UTILISATEURS A PARTIR DU FICHIER');

        $usersCreated= 0;

        foreach ($this->getDataFormFile() as $row){
            if (array_key_exists('email',$row)&&!empty($row['email'])){
                $user = $this->userRepository->findOneBy([
                    'email'=>$row['email']
                ]);

                if (!$user){
                    $user = new User();

                    $user->setUsername($row['username'])
                        ->setFirstName($row['first_name'])
                        ->setLastName($row['last_name'])
                        ->setEmail($row['email'])
                        ->setPassword($row['password'])
                        ->setAdmin(false)
                        ->setActive(true);
                    $this->entityManager->persist($user);

                    $usersCreated++;
                }
            }
        }

        $this->entityManager->flush();

        if($usersCreated > 1){
            $string = "{$usersCreated} UTILISATEURS CREES EN BASE DE DONNEES;";
        }elseif ($usersCreated === 1){
            $string = " 1 UTILISATEUR A ETE  CREE EN BASE DE DONNEES;";
        }else{
            $string = " AUCUN UTILISATEUR N' A ETE  CREE EN BASE DE DONNEES;";
        }

        $this->io->success($string);

    }
}
