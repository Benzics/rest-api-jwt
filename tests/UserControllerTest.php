<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private $client;

    public function setUp() : void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    public function testRegistration(): void
    {

        $this->client->request('POST', '/api/users/register', [
            'firstName' => 'test',
            'lastName' => 'test',
            'email' => 'email@test.com',
            'password' => 'test',
        ]);
        $response = $this->client->getResponse();

        $responseArray = json_decode($response->getContent(), true);
        $this->assertJson($response->getContent());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('User created successfully.', $responseArray['message']);
    }

    public function testRegistrationFails(): void
    {
        $this->client->request('POST', '/api/users/register', [
            'email' => 'email@test.com',
        ]);
        $response = $this->client->getResponse();

        $this->assertJson($response->getContent());
        $this->assertSame(400, $response->getStatusCode());
    }

    public function testLogin() : void
    {
    }
}
