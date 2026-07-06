<?php

namespace App\Form\Pub;

use App\Entity\Candidate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CandidateProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nome completo'])
            ->add('email', TextType::class, ['label' => 'E-mail', 'disabled' => true])
            ->add('phone', TextType::class, ['label' => 'Telefone / WhatsApp'])
            ->add('birthDate', DateType::class, [
                'label'  => 'Data de nascimento',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('city', TextType::class, ['label' => 'Cidade'])
            ->add('state', TextType::class, ['label' => 'Estado (UF)', 'attr' => ['maxlength' => 2]])
            ->add('linkedinUrl', UrlType::class, ['label' => 'LinkedIn', 'required' => false, 'default_protocol' => 'https'])
            ->add('lattesUrl', UrlType::class, ['label' => 'Lattes', 'required' => false, 'default_protocol' => 'https'])
            ->add('councilName', TextType::class, ['label' => 'Conselho profissional', 'required' => false])
            ->add('registrationNumber', TextType::class, ['label' => 'Número de registro', 'required' => false])
            ->add('professionalSummary', TextareaType::class, [
                'label'    => 'Resumo profissional',
                'required' => false,
                'attr'     => ['rows' => 5, 'placeholder' => 'Descreva brevemente sua trajetória e objetivos profissionais...'],
            ])
            ->add('candidateMessage', TextareaType::class, [
                'label'    => 'Mensagem para o selecionador',
                'required' => false,
                'attr'     => ['rows' => 3, 'placeholder' => 'Algo mais que queira compartilhar?'],
            ])
            ->add('contractTypes', ChoiceType::class, [
                'label'    => 'Tipo de contrato de interesse',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices'  => [
                    'CLT'        => 'CLT',
                    'PJ'         => 'PJ',
                    'Estágio'    => 'Estágio',
                    'Temporário' => 'Temporário',
                    'Voluntário' => 'Voluntário',
                ],
            ])
            ->add('immediateStart', CheckboxType::class, [
                'label'    => 'Disponível para início imediato',
                'required' => false,
            ])
            ->add('resumeFile', FileType::class, [
                'label'    => 'Currículo em PDF',
                'mapped'   => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize'   => '5M',
                        'mimeTypes' => ['application/pdf'],
                        'mimeTypesMessage' => 'Envie um arquivo PDF válido (máx. 5 MB).',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Candidate::class]);
    }
}
