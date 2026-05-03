<?php

declare(strict_types=1);

namespace App\Tests\Functional\Profile;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProfileUpsertTest extends WebTestCase
{
    public function testCreatesProfileForAuthenticatedUser(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));
        $token = $this->login($client, 'profile-create@example.com');

        $client->request(
            'PUT',
            '/api/profile',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_TOKEN_CV' => $token,
            ],
            content: json_encode([
                'headline' => 'Desenvolvedor Backend PHP/Symfony',
                'bio' => 'Construo APIs limpas, testaveis e prontas para escala.',
                'phone' => '+55 11 99999-9999',
                'city' => 'Sao Paulo',
                'state' => 'SP',
                'country' => 'Brasil',
                'linkedin_url' => 'https://www.linkedin.com/in/gustavo',
                'github_url' => 'https://github.com/gustavo',
                'website_url' => 'https://gustavo.dev',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $response = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Perfil criado com sucesso', $response['message']);
        self::assertSame('Desenvolvedor Backend PHP/Symfony', $response['data']['headline']);
        self::assertSame('Sao Paulo', $response['data']['city']);
        self::assertSame('https://github.com/gustavo', $response['data']['github_url']);
        self::assertArrayHasKey('id', $response['data']);
        self::assertArrayNotHasKey('success', $response);
    }

    public function testUpdatesExistingProfileForAuthenticatedUser(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));
        $token = $this->login($client, 'profile-update@example.com');

        $client->request(
            'PUT',
            '/api/profile',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_TOKEN_CV' => $token,
            ],
            content: json_encode([
                'headline' => 'Desenvolvedor Backend',
                'city' => 'Sao Paulo',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $created = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        $client->request(
            'PUT',
            '/api/profile',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_TOKEN_CV' => $token,
            ],
            content: json_encode([
                'headline' => 'Arquiteto Backend Symfony',
                'city' => 'Curitiba',
                'country' => 'Brasil',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(200);

        $updated = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Perfil atualizado com sucesso', $updated['message']);
        self::assertSame($created['data']['id'], $updated['data']['id']);
        self::assertSame('Arquiteto Backend Symfony', $updated['data']['headline']);
        self::assertSame('Curitiba', $updated['data']['city']);
        self::assertSame('Brasil', $updated['data']['country']);
        self::assertArrayNotHasKey('success', $updated);
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