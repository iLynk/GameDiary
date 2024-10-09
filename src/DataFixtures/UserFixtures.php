<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // ROLE ADMINISTRATEUR
        $user = new User();
        $user->setName('Milhan')
            ->setEmail('milhan@gmail.com')
            ->setPassword('milhan33')
            ->setRoles(['ROLE_ADMIN'])
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($user);

        //ROLE MODERATEUR
        $user = new User();
        $user->setName('Salome')
            ->setEmail('salome@gmail.com')
            ->setPassword('salome33')
            ->setRoles(['ROLE_MODERATOR'])
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($user);

        // ROLE UTILISATEUR
        $user = new User();
        $user->setName('Valerie')
            ->setEmail('valerie@gmail.com')
            ->setPassword('valerie33')
            ->setRoles(['ROLE_USER'])
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($user);

        $manager->flush();
    }
}
