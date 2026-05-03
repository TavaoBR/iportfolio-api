<?php

declare(strict_types=1);

namespace App\Tests\Functional\Resume;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ResumeListTest extends WebTestCase
{
    public function testListsOnlyAuthenticatedUserResumes(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));

        $firstUserToken = $this->login($client, 'resume-owner@example.com');
        $secondUserToken = $this->login($client, 'resume-other@example.com');

        $this->createResume($client, $firstUserToken, 'CV Principal', true);
        $this->createResume($client, $firstUserToken, 'CV Alternativo', false);
        $this->createResume($client, $secondUserToken, 'CV de outro usuario', true);

        $client->request(
            'GET',
            '/api/resumes',
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_TOKEN_CV' => $firstUserToken,
            ]
        );

        self::assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Curriculos encontrados com sucesso', $response['message']);
        self::assertCount(2, $response['data']);
        self::assertSame('CV Principal', $response['data'][0]['title']);
        self::assertTrue($response['data'][0]['is_main']);
        self::assertSame('CV Alternativo', $response['data'][1]['title']);
        self::assertFalse($response['data'][1]['is_main']);
        self::assertNotContains('CV de outro usuario', array_column($response['data'], 'title'));
        self::assertArrayNotHasKey('success', $response);
    }

    private function createResume($client, string $token, string $title, bool $isMain): void
    {
        $client->request(
            'POST',
            '/api/resumes',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_TOKEN_CV' => $token,
            ],
            content: json_encode([
                'title' => $title,
                'target_role' => 'Desenvolvedor Backend PHP',
                'language' => 'pt_BR',
                'is_main' => $isMain,
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);
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