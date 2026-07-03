<?php

namespace App\Form\Pub;

use App\Entity\Candidate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class CandidateAccessType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, ['label' => 'E-mail de acesso'])
            ->add('newPassword', RepeatedType::class, [
                'type'           => PasswordType::class,
                'mapped'         => false,
                'required'       => false,
                'first_options'  => ['label' => 'Nova senha', 'attr' => ['placeholder' => 'Deixe em branco para não alterar']],
                'second_options' => ['label' => 'Confirmar nova senha'],
                'constraints'    => [new Length(['min' => 8, 'minMessage' => 'Mínimo 8 caracteres.'])],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Candidate::class]);
    }
}
