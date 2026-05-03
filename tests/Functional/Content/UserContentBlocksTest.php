<?php

declare(strict_types=1);

namespace App\Tests\Functional\Content;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UserContentBlocksTest extends WebTestCase
{
    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $expected
     */
    #[DataProvider('blocks')]
    public function testAuthenticatedUserCanCreateAndListOwnContentBlock(
        string $endpoint,
        string $createdMessage,
        string $listMessage,
        array $payload,
        array $expected,
    ): void {
        $client = self::createClient();
        $this->resetDatabase($client->getContainer()->get(EntityManagerInterface::class));

        $ownerToken = $this->login($client, 'owner-'.$endpoint.'@example.com');
        $otherToken = $this->login($client, 'other-'.$endpoint.'@example.com');

        $client->request(
            'POST',
            '/api/'.$endpoint,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_TOKEN_CV' => $ownerToken,
            ],
            content: json_encode($payload, JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $created = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($createdMessage, $created['message']);
        self::assertArrayNotHasKey('success', $created);

        foreach ($expected as $field => $value) {
            self::assertSame($value, $created['data'][$field]);
        }

        $client->request(
            'POST',
            '/api/'.$endpoint,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_TOKEN_CV' => $otherToken,
            ],
            content: json_encode($payload, JSON_THROW_ON_ERROR)
        );
        self::assertResponseStatusCodeSame(201);

        $client->request(
            'GET',
            '/api/'.$endpoint,
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_TOKEN_CV' => $ownerToken,
            ]
        );

        self::assertResponseIsSuccessful();

        $listed = json_decode($client->getResponse()->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($listMessage, $listed['message']);
        self::assertCount(1, $listed['data']);
        self::assertArrayNotHasKey('success', $listed);

        foreach ($expected as $field => $value) {
            self::assertSame($value, $listed['data'][0][$field]);
        }
    }

    /**
     * @return iterable<string, array{string, string, string, array<string, mixed>, array<string, mixed>}>
     */
    public static function blocks(): iterable
    {
        yield 'experience' => [
            'experiences',
            'Experiencia criada com sucesso',
            'Experiencias encontradas com sucesso',
            [
                'company' => 'Delbank',
                'role' => 'Desenvolvedor Backend',
                'description' => 'Desenvolvimento de APIs em Symfony.',
                'location' => 'Sao Paulo, SP',
                'start_date' => '2024-01-10',
                'end_date' => null,
                'is_current' => true,
                'sort_order' => 1,
            ],
            [
                'company' => 'Delbank',
                'role' => 'Desenvolvedor Backend',
                'is_current' => true,
                'sort_order' => 1,
            ],
        ];

        yield 'education' => [
            'educations',
            'Formacao criada com sucesso',
            'Formacoes encontradas com sucesso',
            [
                'institution' => 'FIAP',
                'degree' => 'Tecnologo',
                'field_of_study' => 'Analise e Desenvolvimento de Sistemas',
                'description' => 'Formacao focada em desenvolvimento de software.',
                'start_date' => '2022-02-01',
                'end_date' => '2024-12-20',
                'is_current' => false,
                'sort_order' => 1,
            ],
            [
                'institution' => 'FIAP',
                'degree' => 'Tecnologo',
                'field_of_study' => 'Analise e Desenvolvimento de Sistemas',
                'is_current' => false,
                'sort_order' => 1,
            ],
        ];

        yield 'skill' => [
            'skills',
            'Competencia criada com sucesso',
            'Competencias encontradas com sucesso',
            [
                'name' => 'Symfony',
                'category' => 'Backend',
                'level' => 'advanced',
                'sort_order' => 1,
            ],
            [
                'name' => 'Symfony',
                'category' => 'Backend',
                'level' => 'advanced',
                'sort_order' => 1,
            ],
        ];

        yield 'certification' => [
            'certifications',
            'Certificacao criada com sucesso',
            'Certificacoes encontradas com sucesso',
            [
                'name' => 'Symfony Certification',
                'issuer' => 'SensioLabs',
                'credential_url' => 'https://example.com/cert/symfony',
                'issued_at' => '2025-03-10',
                'expires_at' => null,
                'sort_order' => 1,
            ],
            [
                'name' => 'Symfony Certification',
                'issuer' => 'SensioLabs',
                'credential_url' => 'https://example.com/cert/symfony',
                'sort_order' => 1,
            ],
        ];

        yield 'project' => [
            'projects',
            'Projeto criado com sucesso',
            'Projetos encontrados com sucesso',
            [
                'name' => 'Smart Portfolio CV',
                'description' => 'API para curriculos e portfolios inteligentes.',
                'project_url' => 'https://example.com/smart-portfolio',
                'repository_url' => 'https://github.com/example/smart-portfolio',
                'start_date' => '2025-01-01',
                'end_date' => null,
                'is_current' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Smart Portfolio CV',
                'project_url' => 'https://example.com/smart-portfolio',
                'repository_url' => 'https://github.com/example/smart-portfolio',
                'is_current' => true,
                'sort_order' => 1,
            ],
        ];
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