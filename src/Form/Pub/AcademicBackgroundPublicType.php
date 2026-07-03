<?php

namespace App\Form\Pub;

use App\Entity\AcademicBackground;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AcademicBackgroundPublicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('institution', TextType::class, ['label' => 'Instituição'])
            ->add('degree', TextType::class, ['label' => 'Grau / Curso'])
            ->add('fieldOfStudy', TextType::class, ['label' => 'Área de estudo', 'required' => false])
            ->add('startDate', DateType::class, ['label' => 'Início', 'widget' => 'single_text'])
            ->add('endDate', DateType::class, ['label' => 'Conclusão', 'widget' => 'single_text', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => AcademicBackground::class]);
    }
}
