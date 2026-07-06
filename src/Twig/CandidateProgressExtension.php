<?php

namespace App\Twig;

use App\Entity\Candidate;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CandidateProgressExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('candidate_progress', [$this, 'getProgress']),
        ];
    }

    public function getProgress(Candidate $candidate): array
    {
        $steps = [
            ['label' => 'Dados pessoais', 'route' => 'pub_candidate_profile', 'icon' => 'user', 'done' => $candidate->getName() && $candidate->getEmail() && $candidate->getPhone() && $candidate->getCity() && $candidate->getState()],
            ['label' => 'Experiências', 'route' => 'pub_candidate_experience', 'icon' => 'briefcase', 'done' => $candidate->getWorkExperiences()->count() > 0],
            ['label' => 'Formação acadêmica', 'route' => 'pub_candidate_academic', 'icon' => 'graduation-cap', 'done' => $candidate->getAcademicBackgrounds()->count() > 0],
            ['label' => 'Disponibilidade', 'route' => 'pub_candidate_availability', 'icon' => 'calendar-check', 'done' => $candidate->getCareers()->count() > 0],
            ['label' => 'Currículo PDF', 'route' => 'pub_candidate_profile', 'icon' => 'file-pdf', 'done' => (bool) $candidate->getResumeFilename()],
            ['label' => 'Dados de acesso', 'route' => 'pub_candidate_access', 'icon' => 'lock', 'done' => true],
        ];

        $doneCount = count(array_filter($steps, fn($s) => $s['done']));
        $totalSteps = count($steps);
        $percent = $totalSteps > 0 ? (int) round(($doneCount / $totalSteps) * 100) : 0;

        return [
            'steps' => $steps,
            'doneCount' => $doneCount,
            'totalSteps' => $totalSteps,
            'percent' => $percent,
        ];
    }
}
