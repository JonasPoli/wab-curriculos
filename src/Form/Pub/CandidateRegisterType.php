<?php

namespace App\Form\Pub;

use App\Entity\Candidate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CandidateRegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nome completo', 'attr' => ['placeholder' => 'Seu nome completo']])
            ->add('email', EmailType::class, ['label' => 'E-mail', 'attr' => ['placeholder' => 'seu@email.com']])
            ->add('phone', TextType::class, ['label' => 'Telefone / WhatsApp', 'attr' => ['placeholder' => '(11) 99999-9999']])
            ->add('city', TextType::class, ['label' => 'Cidade', 'attr' => ['placeholder' => 'São Paulo']])
            ->add('state', TextType::class, ['label' => 'Estado (UF)', 'attr' => ['placeholder' => 'SP', 'maxlength' => 2]])
            ->add('plainPassword', RepeatedType::class, [
                'type'            => PasswordType::class,
                'mapped'          => false,
                'first_options'   => ['label' => 'Senha', 'attr' => ['placeholder' => 'Mínimo 8 caracteres']],
                'second_options'  => ['label' => 'Confirmar senha', 'attr' => ['placeholder' => 'Repita a senha']],
                'constraints'     => [
                    new NotBlank(['message' => 'A senha é obrigatória.']),
                    new Length(['min' => 8, 'minMessage' => 'A senha deve ter no mínimo 8 caracteres.']),
                ],
            ])
            ->add('lgpdConsent', CheckboxType::class, [
                'label'    => 'Li e aceito os termos de uso e a política de privacidade.',
                'mapped'   => false,
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Candidate::class]);
    }
}
