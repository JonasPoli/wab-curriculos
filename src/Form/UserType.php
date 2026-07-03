<?php

namespace App\Form;

use App\Entity\Tenant;
use App\Entity\User;
use App\Enum\WorkGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nome completo',
                'attr'  => ['placeholder' => 'Maria Souza'],
            ])
            ->add('username', TextType::class, [
                'label' => 'Username (login)',
                'attr'  => ['placeholder' => 'maria.souza'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
            ])
            ->add('workGroup', EnumType::class, [
                'label'        => 'Perfil de acesso',
                'class'        => WorkGroup::class,
                'choice_label' => fn(WorkGroup $wg) => $wg->label(),
            ])
            ->add('tenant', EntityType::class, [
                'label'        => 'Tenant',
                'class'        => Tenant::class,
                'choice_label' => 'name',
                'required'     => false,
                'placeholder'  => '— Super Admin (sem tenant) —',
                'help'         => 'Deixe vazio para criar um Super Administrador global.',
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type'            => PasswordType::class,
                'mapped'          => false,
                'required'        => $options['is_new'],
                'first_options'   => ['label' => 'Senha', 'attr' => ['autocomplete' => 'new-password']],
                'second_options'  => ['label' => 'Confirmar senha'],
                'invalid_message' => 'As senhas não coincidem.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_new'     => false,
        ]);
    }
}
