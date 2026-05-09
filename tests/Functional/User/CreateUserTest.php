<?php

declare(strict_types=1);

namespace App\Tests\Functional\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CreateUserTest extends WebTestCase
{
    public function testCreatesUserWithoutAvatar(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));

        $client->request(
            'POST',
            '/api/users',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'name' => 'Gustavo Oliveira',
                'email' => 'gustavo@example.com',
                'password' => 'Senha123',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $payload = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Conta criada com sucesso', $payload['message']);
        self::assertArrayHasKey('data', $payload);
        self::assertSame('Gustavo Oliveira', $payload['data']['name']);
        self::assertSame('gustavo@example.com', $payload['data']['email']);
        self::assertArrayNotHasKey('password', $payload['data']);
        self::assertArrayNotHasKey('success', $payload);
    }

    public function testDoesNotCreateUserWithDuplicatedEmail(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));

        $payload = [
            'name' => 'Gustavo Oliveira',
            'email' => 'duplicado@example.com',
            'password' => 'Senha123',
        ];

        $client->request(
            'POST',
            '/api/users',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode($payload, JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $client->request(
            'POST',
            '/api/users',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode($payload, JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(409);

        $response = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Usuario ja cadastrado', $response['message']);
        self::assertArrayNotHasKey('success', $response);
    }
    public function testRejectsInvalidCreatePayload(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));

        $client->request(
            'POST',
            '/api/users',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'name' => '',
                'email' => 'email-invalido',
                'password' => 'abcdefgh',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(422);

        $response = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Dados invalidos', $response['message']);
        self::assertArrayHasKey('errors', $response);
        self::assertArrayNotHasKey('success', $response);
    }
    public function testCreatesUserWithAvatar(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));

        $client->request(
            'POST',
            '/api/users',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'name' => 'Gustavo Oliveira',
                'email' => 'avatar@example.com',
                'password' => 'Senha123',
                'avatar' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $response = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        $sentAvatar = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=';
        self::assertIsString($response['data']['avatar']);
        self::assertSame($sentAvatar, $response['data']['avatar']);

        $em = self::getContainer()->get(EntityManagerInterface::class);
        $persisted = $em->find(User::class, $response['data']['id']);
        self::assertInstanceOf(User::class, $persisted);
        self::assertSame($sentAvatar, $persisted->getAvatar());
    }
    public function testRejectsInvalidAvatar(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));

        $client->request(
            'POST',
            '/api/users',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'name' => 'Gustavo Oliveira',
                'email' => 'invalid-avatar@example.com',
                'password' => 'Senha123',
                'avatar' => 'isso-nao-e-base64',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(422);

        $response = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Avatar deve ser uma imagem em base64 valida', $response['message']);
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