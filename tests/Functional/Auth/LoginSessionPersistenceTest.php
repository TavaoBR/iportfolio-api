<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Repository\LoginSessionRepository;
use App\Service\Auth\AuthTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LoginSessionPersistenceTest extends WebTestCase
{
    public function testLoginPersistsSessionWithDurationAndMetadata(): void
    {
        $client = self::createClient();
        $container = $client->getContainer();
        $this->resetDatabase($container->get(EntityManagerInterface::class));

        $client->request(
            'POST',
            '/api/users',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'name' => 'Gustavo Oliveira',
                'email' => 'session@example.com',
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
                'REMOTE_ADDR' => '127.0.0.20',
            ],
            content: json_encode([
                'email' => 'session@example.com',
                'password' => 'Senha123',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);
        $token = $response['data']['token'];
        $tokenHash = $container->get(AuthTokenService::class)->hash($token);

        $session = $container->get(LoginSessionRepository::class)->findActiveByTokenHash($tokenHash);

        self::assertNotNull($session);
        self::assertSame($response['data']['user']['id'], $session->getUserId());
        self::assertSame('127.0.0.20', $session->getIp());
        self::assertGreaterThan($session->getLoginDateTime(), $session->getExpireDateTime());
        self::assertSame('session@example.com', $session->getSessionMetadata()['email']);
        self::assertSame($response['data']['session_metadata'], $session->getSessionMetadata());
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