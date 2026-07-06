<?php

namespace App\Form\Pub;

use App\Entity\AcademicBackground;
use App\Enum\AcademicStatus;
use App\Enum\EducationLevel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AcademicBackgroundPublicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('educationLevel', EnumType::class, [
                'class'       => EducationLevel::class,
                'label'       => 'Nível de escolaridade',
                'choice_label' => fn (EducationLevel $level) => $level->label(),
                'placeholder' => 'Selecione...',
            ])
            ->add('institution', TextType::class, ['label' => 'Instituição'])
            ->add('course', TextType::class, ['label' => 'Curso'])
            ->add('status', EnumType::class, [
                'class'       => AcademicStatus::class,
                'label'       => 'Status',
                'choice_label' => fn (AcademicStatus $s) => $s->label(),
                'placeholder' => 'Selecione...',
            ])
            ->add('startDate', DateType::class, ['label' => 'Início', 'widget' => 'single_text', 'required' => false])
            ->add('endDate', DateType::class, ['label' => 'Conclusão', 'widget' => 'single_text', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => AcademicBackground::class]);
    }
}
