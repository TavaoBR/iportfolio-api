<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\AI\AiCompareJobDTO;
use App\Entity\AIAnalysis;
use App\Entity\Resume;
use App\Entity\User;
use App\Exception\AI\AIAnalysisNotFoundException;
use App\Exception\Resume\ResumeNotFoundException;
use App\Mapper\AIAnalysisMapper;
use App\Repository\AIAnalysisRepository;
use App\Repository\ResumeRepository;
use Symfony\Component\HttpFoundation\Response;

final class ResumeAiService
{
    public function __construct(
        private readonly ResumeRepository $resumes,
        private readonly AIAnalysisRepository $analyses,
        private readonly AIAnalysisMapper $mapper,
    ) {
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function enqueueAnalyze(User $user, string $publicResumeId): array
    {
        return $this->enqueue($user, $publicResumeId, AIAnalysis::TYPE_ANALYZE, null);
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function enqueueOptimize(User $user, string $publicResumeId): array
    {
        return $this->enqueue($user, $publicResumeId, AIAnalysis::TYPE_OPTIMIZE, null);
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function enqueueCompareJob(User $user, string $publicResumeId, AiCompareJobDTO $dto): array
    {
        return $this->enqueue($user, $publicResumeId, AIAnalysis::TYPE_COMPARE_JOB, [
            'job_description' => $dto->jobDescription,
        ]);
    }

    /**
     * @return array{status: int, message: string, data?: list<array<string, mixed>>, errors?: mixed}
     */
    public function list(User $user, string $publicResumeId): array
    {
        try {
            $resume = $this->requireResume($user, $publicResumeId);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Analises listadas',
                'data' => $this->mapper->toArrayList($this->analyses->findByResumeOrdered($resume)),
            ];
        } catch (ResumeNotFoundException $e) {
            return [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Ocorreu algum erro inesperado',
                'errors' => $e->getMessage(),
            ];
        }
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function show(User $user, string $publicResumeId, int $analysisId): array
    {
        try {
            $resume = $this->requireResume($user, $publicResumeId);
            $row = $this->analyses->findOneOnResume($resume, $analysisId);

            if (!$row instanceof AIAnalysis) {
                throw new AIAnalysisNotFoundException('Analise nao encontrada');
            }

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Analise encontrada',
                'data' => $this->mapper->toArray($row),
            ];
        } catch (AIAnalysisNotFoundException $e) {
            return [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => $e->getMessage(),
            ];
        } catch (ResumeNotFoundException $e) {
            return [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Ocorreu algum erro inesperado',
                'errors' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param array<string, mixed>|null $payload
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    private function enqueue(User $user, string $publicResumeId, string $type, ?array $payload): array
    {
        try {
            $resume = $this->requireResume($user, $publicResumeId);

            $job = new AIAnalysis($resume, $type, $payload);
            $this->analyses->save($job);

            return [
                'status' => Response::HTTP_CREATED,
                'message' => 'Pedido registrado — processamento pela IA ficara disponivel assim que o worker estiver ativo.',
                'data' => $this->mapper->toArray($job),
            ];
        } catch (ResumeNotFoundException $e) {
            return [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Ocorreu algum erro inesperado',
                'errors' => $e->getMessage(),
            ];
        }
    }

    private function requireResume(User $user, string $publicResumeId): Resume
    {
        $resume = $this->resumes->findByPublicIdForUser($publicResumeId, $user);

        if ($resume === null) {
            throw new ResumeNotFoundException();
        }

        return $resume;
    }
}
