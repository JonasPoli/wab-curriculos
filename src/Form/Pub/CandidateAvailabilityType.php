<?php

namespace App\Form\Pub;

use App\Entity\Candidate;
use App\Entity\Career;
use App\Repository\CareerRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidateAvailabilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $days = [
            'mondayMorning'      => 'Segunda — Manhã',
            'mondayAfternoon'    => 'Segunda — Tarde',
            'tuesdayMorning'     => 'Terça — Manhã',
            'tuesdayAfternoon'   => 'Terça — Tarde',
            'wednesdayMorning'   => 'Quarta — Manhã',
            'wednesdayAfternoon' => 'Quarta — Tarde',
            'thursdayMorning'    => 'Quinta — Manhã',
            'thursdayAfternoon'  => 'Quinta — Tarde',
            'fridayMorning'      => 'Sexta — Manhã',
            'fridayAfternoon'    => 'Sexta — Tarde',
        ];

        foreach ($days as $field => $label) {
            $builder->add($field, CheckboxType::class, ['label' => $label, 'required' => false]);
        }

        $builder->add('careers', EntityType::class, [
            'class'         => Career::class,
            'label'         => 'Cargos de interesse',
            'multiple'      => true,
            'expanded'      => true,
            'required'      => false,
            'query_builder' => fn (CareerRepository $repo) => $repo->createQueryBuilder('c')
                ->join('c.area', 'a')
                ->orderBy('a.name', 'ASC')
                ->addOrderBy('c.title', 'ASC'),
            'choice_label'  => fn (Career $c) => $c->getArea()->getName() . ' › ' . $c->getTitle(),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Candidate::class]);
    }
}
