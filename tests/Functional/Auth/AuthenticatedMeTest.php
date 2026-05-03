<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AuthenticatedMeTest extends WebTestCase
{
    public function testShowsAuthenticatedUserFromTokenMetadata(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));

        $client->request(
            'POST',
            '/api/users',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'name' => 'Gustavo Oliveira',
                'email' => 'me@example.com',
                'password' => 'Senha123',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $client->request(
            'POST',
            '/api/auth/login',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'email' => 'me@example.com',
                'password' => 'Senha123',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(200);

        $login = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);
        $token = $login['data']['token'];

        $client->request('GET', '/api/me', server: [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_TOKEN_CV' => $token,
        ]);

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Usuario autenticado encontrado com sucesso', $response['message']);
        self::assertSame('Gustavo Oliveira', $response['data']['name']);
        self::assertSame('me@example.com', $response['data']['email']);
        self::assertArrayHasKey('session_metadata', $response['data']);
        self::assertSame('me@example.com', $response['data']['session_metadata']['email']);
        self::assertSame($response['data']['id'], $response['data']['session_metadata']['user_id']);
        self::assertArrayNotHasKey('password', $response['data']);
        self::assertArrayNotHasKey('success', $response);
    }

    public function testRejectsMissingToken(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));

        $client->request('GET', '/api/me', server: ['HTTP_ACCEPT' => 'application/json']);

        self::assertResponseStatusCodeSame(401);

        $response = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Token ausente', $response['message']);
        self::assertArrayNotHasKey('success', $response);
    }

    public function testRejectsInvalidToken(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));

        $client->request('GET', '/api/me', server: [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_TOKEN_CV' => 'token-invalido',
        ]);

        self::assertResponseStatusCodeSame(401);

        $response = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Token invalido', $response['message']);
        self::assertArrayNotHasKey('success', $response);
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