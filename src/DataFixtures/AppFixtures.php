<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Entity\Hamster;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        for ($i = 0; $i < 10; $i++) {
            $user = $this->createUser($manager, $faker, $i === 0); // Premier user = admin
            // Créer 4 hamsters : 2 mâles, 2 femelles
            $this->createHamster($manager, $faker, $user, 'm');
            $this->createHamster($manager, $faker, $user, 'm');
            $this->createHamster($manager, $faker, $user, 'f');
            $this->createHamster($manager, $faker, $user, 'f');
        }

        $manager->flush();
    }

    public function createUser(ObjectManager $manager, $faker, bool $isAdmin = false): User
    {
        $user = new User();
        $user->setName($faker->name());
        $user->setEmail($faker->unique()->email());
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $user->setRoles($isAdmin ? ['ROLE_ADMIN'] : ['ROLE_USER']);
        $user->setGold(500);

        $manager->persist($user);
        return $user;
    }

    public function createHamster(ObjectManager $manager, $faker, User $owner, string $genre): Hamster
    {
        $hamster = new Hamster();
        $hamster->setName($genre === 'm' ? $faker->firstNameMale() : $faker->firstNameFemale());
        $hamster->setHunger($faker->numberBetween(0, 100));
        $hamster->setAge($faker->numberBetween(0, 400));
        $hamster->setGenre($genre);
        $hamster->setActive($faker->boolean(90) ? 1 : 0);
        $hamster->setOwner($owner);
        $owner->addHamster($hamster);

        $manager->persist($hamster);
        return $hamster;
    }
}
