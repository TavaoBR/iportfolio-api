<?php

declare(strict_types=1);

namespace App\Tests\Functional\Resume;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ResumeCreateTest extends WebTestCase
{
    public function testCreatesResumeForAuthenticatedUser(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));
        $token = $this->login($client, 'resume-create@example.com');

        $client->request(
            'POST',
            '/api/resumes',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_TOKEN_CV' => $token,
            ],
            content: json_encode([
                'title' => 'CV Backend Symfony',
                'target_role' => 'Desenvolvedor Backend PHP',
                'language' => 'pt_BR',
                'is_main' => true,
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $response = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Curriculo criado com sucesso', $response['message']);
        self::assertSame('CV Backend Symfony', $response['data']['title']);
        self::assertSame('Desenvolvedor Backend PHP', $response['data']['target_role']);
        self::assertSame('pt_BR', $response['data']['language']);
        self::assertTrue($response['data']['is_main']);
        self::assertNull($response['data']['ats_score']);
        self::assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $response['data']['public_id']);
        self::assertArrayNotHasKey('template_key', $response['data']);
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