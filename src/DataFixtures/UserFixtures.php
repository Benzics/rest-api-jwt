<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // for our automated tests, we are setting a dummy user data
        // password is 123456
        $user = new User();

        // this is our hashed equivalent of 123456
        $encodedPass = '$argon2id$v=19$m=65536,t=4,p=1$nzq5sLm01f4bIVN93QLFhQ$5z3VW7fs4/a6CvPlU4MfigB/NDzkuhX+1HMyjg9xWd8';

        // set our dummy data
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setEmail('user@test.com');
        $user->setUpdatedAt(new \DateTimeImmutable());
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setPassword($encodedPass);

        $manager->persist($user);

        $manager->flush();
    }
}
