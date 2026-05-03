<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AuthLoginTest extends WebTestCase
{
    public function testLogsInWithValidCredentials(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));

        $client->request(
            'POST',
            '/api/users',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'name' => 'Gustavo Oliveira',
                'email' => 'auth@example.com',
                'password' => 'Senha123',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $client->request(
            'POST',
            '/api/auth/login',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'email' => 'auth@example.com',
                'password' => 'Senha123',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Login realizado com sucesso', $response['message']);
        self::assertArrayHasKey('data', $response);
        self::assertIsString($response['data']['token']);
        self::assertNotSame('', $response['data']['token']);
        self::assertSame('Gustavo Oliveira', $response['data']['user']['name']);
        self::assertSame('auth@example.com', $response['data']['user']['email']);
        self::assertArrayNotHasKey('password', $response['data']['user']);
        self::assertArrayNotHasKey('success', $response);
    }

    public function testRejectsInvalidPassword(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));

        $client->request(
            'POST',
            '/api/users',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'name' => 'Gustavo Oliveira',
                'email' => 'invalid-password@example.com',
                'password' => 'Senha123',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $client->request(
            'POST',
            '/api/auth/login',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'email' => 'invalid-password@example.com',
                'password' => 'senha-errada',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(401);

        $response = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Email ou senha invalidos', $response['message']);
        self::assertArrayNotHasKey('data', $response);
        self::assertArrayNotHasKey('success', $response);
    }
    public function testLoginReturnsSessionMetadata(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));

        $client->request(
            'POST',
            '/api/users',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'name' => 'Gustavo Oliveira',
                'email' => 'metadata@example.com',
                'password' => 'Senha123',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $client->request(
            'POST',
            '/api/auth/login',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'REMOTE_ADDR' => '127.0.0.10',
            ],
            content: json_encode([
                'email' => 'metadata@example.com',
                'password' => 'Senha123',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('session_metadata', $response['data']);
        self::assertSame($response['data']['user']['id'], $response['data']['session_metadata']['user_id']);
        self::assertSame('metadata@example.com', $response['data']['session_metadata']['email']);
        self::assertSame('127.0.0.10', $response['data']['session_metadata']['ip']);
        self::assertSame('X-Token-CV', $response['data']['session_metadata']['token_header']);
        self::assertArrayHasKey('issued_at', $response['data']['session_metadata']);
        self::assertArrayHasKey('expires_at', $response['data']['session_metadata']);
    }
    private function resetDatabase(EntityManagerInterface $entityManager): void
    {
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);

        if ($metadata !== []) {
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        }
    }
}