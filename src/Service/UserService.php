<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Hamster;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class UserService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function createUser(string $email, string $password): User
    {
        if ($this->userRepository->findOneBy(['email' => $email])) {
            throw new \Exception("Email déjà utilisé");
        }

        $user = new User();
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles(['ROLE_USER']);
        $user->setGold(500);
        $user->setName($email);

        // Persister le user d'abord
        $this->entityManager->persist($user);
        $this->entityManager->flush(); // Flush pour obtenir l'ID du user

        $faker = Factory::create();

        // 2 mâles
        for ($i = 0; $i < 2; $i++) {
            $hamster = new Hamster();
            $hamster->setName($faker->firstNameMale());
            $hamster->setHunger(100);
            $hamster->setAge(0);
            $hamster->setGenre('m');
            $hamster->setActive(1);
            $hamster->setOwner($user);
            $user->addHamster($hamster);
            $this->entityManager->persist($hamster);
        }

        // 2 femelles
        for ($i = 0; $i < 2; $i++) {
            $hamster = new Hamster();
            $hamster->setName($faker->firstNameFemale());
            $hamster->setHunger(100);
            $hamster->setAge(0);
            $hamster->setGenre('f');
            $hamster->setActive(1);
            $hamster->setOwner($user);
            $user->addHamster($hamster);
            $this->entityManager->persist($hamster);
        }

        $this->entityManager->flush();

        return $user;
    }
}
