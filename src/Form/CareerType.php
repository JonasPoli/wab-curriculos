<?php

namespace App\Form;

use App\Entity\AreaOfInterest;
use App\Entity\Career;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CareerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Título do cargo',
                'attr'  => ['placeholder' => 'Ex: Enfermeiro UTI, Recepcionista...'],
            ])
            ->add('area', EntityType::class, [
                'label'        => 'Área de interesse',
                'class'        => AreaOfInterest::class,
                'choice_label' => 'title',
                'choices'      => $options['areas'],
            ])
            ->add('active', CheckboxType::class, [
                'label'    => 'Ativo (disponível para seleção)',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Career::class,
            'areas'      => [],
        ]);
    }
}
