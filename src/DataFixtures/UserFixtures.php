<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    // Injecte le service de hachage dans le constructeur
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // ROLE ADMINISTRATEUR
        $user = new User();
        $user->setName('Milhan')
            ->setEmail('milhan@gmail.com')
            ->setRoles(['ROLE_ADMIN'])
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());

        // Hacher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'SuperAdmin123!@');
        $user->setPassword($hashedPassword);
        $manager->persist($user);

        // ROLE MODERATEUR
        $user = new User();
        $user->setName('Salome')
            ->setEmail('salome@gmail.com')
            ->setRoles(['ROLE_MODERATOR'])
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());

        // Hacher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'SuperModerator123!@');
        $user->setPassword($hashedPassword);
        $manager->persist($user);

        // ROLE UTILISATEUR
        $user = new User();
        $user->setName('Valerie')
            ->setEmail('valerie@gmail.com')
            ->setRoles(['ROLE_USER'])
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());

        // Hacher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'SuperUser123!@');
        $user->setPassword($hashedPassword);
        $manager->persist($user);


        // Exécuter l'insertion en base de données
        $manager->flush();
    }
}
