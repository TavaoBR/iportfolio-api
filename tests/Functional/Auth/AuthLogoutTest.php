<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AuthLogoutTest extends WebTestCase
{
    public function testLogsOutAndInvalidatesCurrentToken(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));

        $client->request(
            'POST',
            '/api/users',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'name' => 'Gustavo Oliveira',
                'email' => 'logout@example.com',
                'password' => 'Senha123',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $client->request(
            'POST',
            '/api/auth/login',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'email' => 'logout@example.com',
                'password' => 'Senha123',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(200);

        $login = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);
        $token = $login['data']['token'];

        $client->request('POST', '/api/auth/logout', server: [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_TOKEN_CV' => $token,
        ]);

        self::assertResponseStatusCodeSame(200);

        $logout = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Logout realizado com sucesso', $logout['message']);
        self::assertArrayNotHasKey('success', $logout);

        $client->request('GET', '/api/me', server: [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_TOKEN_CV' => $token,
        ]);

        self::assertResponseStatusCodeSame(401);

        $me = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Token invalido', $me['message']);
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