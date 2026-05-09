<?php

declare(strict_types=1);

namespace App\Tests\Functional\Profile;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProfileReadTest extends WebTestCase
{
    public function testShowsProfileForAuthenticatedUser(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));
        $token = $this->login($client, 'profile-read@example.com');

        $client->request(
            'POST',
            '/api/profile',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_TOKEN_CV' => $token,
            ],
            content: json_encode([
                'headline' => 'Engenheiro de Software',
                'bio' => 'Foco em backend, arquitetura e qualidade.',
                'city' => 'Campinas',
                'state' => 'SP',
                'country' => 'Brasil',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $client->request('GET', '/api/profile', server: [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_TOKEN_CV' => $token,
        ]);

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Perfil encontrado com sucesso', $response['message']);
        self::assertSame('Engenheiro de Software', $response['data']['headline']);
        self::assertSame('Campinas', $response['data']['city']);
        self::assertArrayNotHasKey('success', $response);
    }

    public function testReturnsNotFoundWhenAuthenticatedUserHasNoProfile(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));
        $token = $this->login($client, 'profile-missing@example.com');

        $client->request('GET', '/api/profile', server: [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_TOKEN_CV' => $token,
        ]);

        self::assertResponseStatusCodeSame(404);

        $response = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Perfil nao encontrado', $response['message']);
        self::assertArrayNotHasKey('success', $response);
    }

    private function login($client, string $email): string
    {
        $client->request(
            'POST',
            '/api/users',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'name' => 'Gustavo Oliveira',
                'email' => $email,
                'password' => 'Senha123',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $client->request(
            'POST',
            '/api/auth/login',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'email' => $email,
                'password' => 'Senha123',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        return $response['data']['token'];
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