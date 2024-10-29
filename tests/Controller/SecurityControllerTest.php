<?php

// tests/Controller/SecurityControllerTest.php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
public function testLogin(): void
{
$client = static::createClient();
$crawler = $client->request('GET', '/login');

$this->assertResponseIsSuccessful();
$this->assertSelectorTextContains('h1', 'Connectez-vous');

    $form = $crawler->selectButton('Se connecter')->form();
    $form['email'] = 'milhan@gmail.com';
    $form['password'] = 'SuperAdmin123!@';

    $client->submit($form);

    $this->assertResponseRedirects('/');

    $client->followRedirect();
    $this->assertSelectorTextContains('p', 'Hey, Milhan');
}
}
