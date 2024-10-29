<?php

// tests/Entity/UserTest.php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUser(): void
    {
        $user = new User();
        $user->setName('NameTest')
            ->setPassword('SuperTestPassword#!')
            ->setEmail('emailtest@example.com');

        $this->assertEquals('NameTest', $user->getName());
        $this->assertEquals('SuperTestPassword#!', $user->getPassword());
        $this->assertEquals('emailtest@example.com', $user->getEmail());

    }
}