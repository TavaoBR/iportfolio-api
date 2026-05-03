<?php

declare(strict_types=1);

namespace App\Tests\Functional\User;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UserStatusTest extends WebTestCase
{
    public function testActivatesAndDeactivatesUser(): void
    {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));

        $client->request(
            'POST',
            '/api/users',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'name' => 'Gustavo Oliveira',
                'email' => 'status@example.com',
                'password' => 'Senha123',
            ], JSON_THROW_ON_ERROR)
        );

        $created = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);
        $id = $created['data']['id'];

        $client->request('PATCH', '/api/users/'.$id.'/deactivate', server: ['HTTP_ACCEPT' => 'application/json']);
        self::assertResponseStatusCodeSame(200);

        $deactivated = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('Usuario desativado com sucesso', $deactivated['message']);
        self::assertFalse($deactivated['data']['is_active']);

        $client->request('PATCH', '/api/users/'.$id.'/activate', server: ['HTTP_ACCEPT' => 'application/json']);
        self::assertResponseStatusCodeSame(200);

        $activated = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('Usuario ativado com sucesso', $activated['message']);
        self::assertTrue($activated['data']['is_active']);
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