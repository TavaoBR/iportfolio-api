<?php

declare(strict_types=1);

namespace App\Tests\Functional\Resume;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ResumeShowTest extends WebTestCase
{
    public function testShowsAuthenticatedUserResumeByPublicId(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));

        $token = $this->login($client, 'resume-show@example.com');
        $publicId = $this->createResume($client, $token, 'CV Public ID');

        $client->request(
            'GET',
            '/api/resumes/'.$publicId,
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_TOKEN_CV' => $token,
            ]
        );

        self::assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Curriculo encontrado com sucesso', $response['message']);
        self::assertSame($publicId, $response['data']['public_id']);
        self::assertSame('CV Public ID', $response['data']['title']);
        self::assertArrayNotHasKey('success', $response);
    }

    public function testDoesNotShowAnotherUserResumeByPublicId(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));

        $ownerToken = $this->login($client, 'resume-owner-show@example.com');
        $intruderToken = $this->login($client, 'resume-intruder-show@example.com');
        $ownerPublicId = $this->createResume($client, $ownerToken, 'CV do dono');

        $client->request(
            'GET',
            '/api/resumes/'.$ownerPublicId,
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_TOKEN_CV' => $intruderToken,
            ]
        );

        self::assertResponseStatusCodeSame(404);

        $response = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Curriculo nao encontrado', $response['message']);
        self::assertArrayNotHasKey('success', $response);
    }

    private function createResume($client, string $token, string $title): string
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
                'is_main' => true,
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $response = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        return $response['data']['public_id'];
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