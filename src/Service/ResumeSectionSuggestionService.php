<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Certification;
use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Resume;
use App\Entity\User;
use App\Exception\Resume\ResumeNotFoundException;
use App\Repository\CertificationRepository;
use App\Repository\EducationRepository;
use App\Repository\ExperienceRepository;
use App\Repository\ProjectRepository;
use App\Repository\ResumeRepository;
use App\Repository\SkillRepository;
use App\Repository\UserProfileRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * Texto sugerido por tipo de secao a partir do perfil e dos blocos do utilizador (autocomplete no editor).
 */
final class ResumeSectionSuggestionService
{
    public function __construct(
        private readonly ResumeRepository $resumes,
        private readonly UserProfileRepository $profiles,
        private readonly SkillRepository $skills,
        private readonly ExperienceRepository $experiences,
        private readonly EducationRepository $educations,
        private readonly CertificationRepository $certifications,
        private readonly ProjectRepository $projects,
    ) {
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function suggest(User $user, string $publicResumeId): array
    {
        try {
            $resume = $this->resumes->findByPublicIdForUser($publicResumeId, $user);

            if (!$resume instanceof Resume) {
                throw new ResumeNotFoundException();
            }

            $lines = [];
            $lines['personal_info'] = $this->buildPersonalInfo($user);
            $lines['professional_summary'] = $this->buildSummary($user);
            $lines['experiences'] = $this->buildExperiences($user);
            $lines['educations'] = $this->buildEducations($user);
            $lines['skills'] = $this->buildSkills($user);
            $lines['certifications'] = $this->buildCertifications($user);
            $lines['projects'] = $this->buildProjects($user);
            $lines['links'] = $this->buildLinks($user);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Sugestoes geradas a partir do teu perfil e conteudo',
                'data' => [
                    'by_section_type' => array_map(
                        static fn (?string $t): string => $t ?? '',
                        $lines,
                    ),
                ],
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

    private function buildPersonalInfo(User $user): string
    {
        $profile = $this->profiles->findByUser($user);
        $city = $profile?->getCity();
        $country = $profile?->getCountry();
        $phone = $profile?->getPhone();
        $parts = array_filter([
            'Nome: '.$user->getName(),
            'Email: '.$user->getEmail(),
            $phone !== null && $phone !== '' ? 'Telefone: '.$phone : null,
            $city !== null || $country !== null ? 'Local: '.trim(implode(', ', array_filter([$city, $country]))) : null,
        ]);

        return implode("\n", $parts);
    }

    private function buildSummary(User $user): ?string
    {
        $profile = $this->profiles->findByUser($user);
        if ($profile === null) {
            return null;
        }

        $headline = $profile->getHeadline();
        $bio = $profile->getBio();
        $parts = array_filter([$headline, $bio]);

        return $parts !== [] ? implode("\n\n", $parts) : null;
    }

    private function buildExperiences(User $user): string
    {
        $rows = $this->experiences->findByUser($user);
        $blocks = array_map(static function (Experience $e): string {
            $range = '';
            if ($e->getStartDate()) {
                $range = $e->getStartDate()->format('Y-m');
            }
            if ($e->isCurrent()) {
                $range .= $range !== '' ? ' — atual' : 'atual';
            } elseif ($e->getEndDate()) {
                $range .= ($range !== '' ? ' — ' : '').$e->getEndDate()->format('Y-m');
            }

            $body = trim(implode("\n", array_filter([
                $e->getRole().' @ '.$e->getCompany(),
                $range !== '' ? 'Periodo: '.$range : null,
                $e->getLocation() ? 'Local: '.$e->getLocation() : null,
                $e->getDescription(),
            ])));

            return '- '.$body;
        }, $rows);

        return implode("\n\n", $blocks);
    }

    private function buildEducations(User $user): string
    {
        $rows = $this->educations->findByUser($user);
        $blocks = array_map(static function (Education $e): string {
            $parts = array_filter([
                $e->getInstitution(),
                $e->getFieldOfStudy(),
                $e->getDegree(),
                $e->getDescription(),
            ]);

            return '- '.implode(' — ', $parts);
        }, $rows);

        return implode("\n\n", $blocks);
    }

    private function buildSkills(User $user): string
    {
        $rows = $this->skills->findByUser($user);
        $names = array_map(static fn ($s) => $s->getName(), $rows);

        return implode(', ', $names);
    }

    private function buildCertifications(User $user): string
    {
        $rows = $this->certifications->findByUser($user);
        $blocks = array_map(static function (Certification $c): string {
            return '- '.implode(' — ', array_filter([$c->getName(), $c->getIssuer()]));
        }, $rows);

        return implode("\n", $blocks);
    }

    private function buildProjects(User $user): string
    {
        $rows = $this->projects->findByUser($user);
        $blocks = array_map(static function ($p): string {
            $lines = array_filter([$p->getName(), $p->getDescription()]);

            return '- '.implode("\n  ", $lines);
        }, $rows);

        return implode("\n\n", $blocks);
    }

    private function buildLinks(User $user): ?string
    {
        $profile = $this->profiles->findByUser($user);
        if ($profile === null) {
            return null;
        }

        $links = array_filter([
            $profile->getLinkedinUrl() ? 'LinkedIn: '.$profile->getLinkedinUrl() : null,
            $profile->getGithubUrl() ? 'GitHub: '.$profile->getGithubUrl() : null,
            $profile->getWebsiteUrl() ? 'Site: '.$profile->getWebsiteUrl() : null,
        ]);

        return $links !== [] ? implode("\n", $links) : null;
    }
}
