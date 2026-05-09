<?php

declare(strict_types=1);

namespace App\Tests\Functional\Profile;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProfileValidationTest extends WebTestCase
{
    public function testRejectsMissingToken(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));

        $client->request(
            'POST',
            '/api/profile',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode(['headline' => 'Sem token'], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(401);

        $response = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Token ausente', $response['message']);
        self::assertArrayNotHasKey('success', $response);
    }

    public function testRejectsInvalidUrls(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));
        $token = $this->login($client, 'profile-invalid-url@example.com');

        $client->request(
            'POST',
            '/api/profile',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_TOKEN_CV' => $token,
            ],
            content: json_encode([
                'headline' => 'Dev',
                'linkedin_url' => 'linkedin-invalido',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(422);

        $response = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Dados invalidos', $response['message']);
        self::assertArrayHasKey('errors', $response);
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