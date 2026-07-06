<?php

namespace App\Form\Pub;

use App\Entity\WorkExperience;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkExperiencePublicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('companyName', TextType::class, ['label' => 'Empresa'])
            ->add('position', TextType::class, ['label' => 'Cargo'])
            ->add('startDate', DateType::class, ['label' => 'Data de início', 'widget' => 'single_text'])
            ->add('endDate', DateType::class, ['label' => 'Data de término', 'widget' => 'single_text', 'required' => false])
            ->add('currentJob', CheckboxType::class, ['label' => 'Trabalho aqui atualmente', 'required' => false])
            ->add('description', TextareaType::class, ['label' => 'Descrição das atividades', 'required' => false, 'attr' => ['rows' => 3]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => WorkExperience::class]);
    }
}
