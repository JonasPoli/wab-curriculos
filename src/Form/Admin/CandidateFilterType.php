<?php

namespace App\Form\Admin;

use App\Entity\AreaOfInterest;
use App\Entity\Career;
use App\Entity\Tenant;
use App\Repository\AreaOfInterestRepository;
use App\Repository\CareerRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidateFilterType extends AbstractType
{
    private const UF_LIST = [
        'AC', 'AL', 'AM', 'AP', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
        'MG', 'MS', 'MT', 'PA', 'PB', 'PE', 'PI', 'PR', 'RJ', 'RN',
        'RO', 'RR', 'RS', 'SC', 'SE', 'SP', 'TO',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $tenant = $options['tenant'];

        $builder
            ->add('q', TextType::class, [
                'required' => false,
                'label'    => false,
                'attr'     => ['placeholder' => 'Nome ou e-mail...'],
            ])
            ->add('area', EntityType::class, [
                'class'         => AreaOfInterest::class,
                'required'      => false,
                'label'         => false,
                'placeholder'   => 'Todas as áreas',
                'query_builder' => fn (AreaOfInterestRepository $r) => $r
                    ->createQueryBuilder('a')
                    ->where('a.tenant = :tenant')
                    ->setParameter('tenant', $tenant)
                    ->orderBy('a.title', 'ASC'),
                'choice_label'  => 'title',
            ])
            ->add('career', EntityType::class, [
                'class'         => Career::class,
                'required'      => false,
                'label'         => false,
                'placeholder'   => 'Todos os cargos',
                'query_builder' => fn (CareerRepository $r) => $r
                    ->createQueryBuilder('c')
                    ->join('c.area', 'a')
                    ->where('a.tenant = :tenant')
                    ->setParameter('tenant', $tenant)
                    ->orderBy('a.title', 'ASC')
                    ->addOrderBy('c.title', 'ASC'),
                'choice_label'  => fn (Career $c) => $c->getArea()->getTitle() . ' › ' . $c->getTitle(),
            ])
            ->add('state', ChoiceType::class, [
                'required'    => false,
                'label'       => false,
                'placeholder' => 'Estado (UF)',
                'choices'     => array_combine(self::UF_LIST, self::UF_LIST),
            ])
            ->add('contractType', ChoiceType::class, [
                'required'    => false,
                'label'       => false,
                'placeholder' => 'Tipo de contrato',
                'choices'     => [
                    'CLT'        => 'clt',
                    'PJ'         => 'pj',
                    'Freelancer' => 'freelancer',
                    'Temporário' => 'temporario',
                    'Estágio'    => 'estagio',
                ],
            ])
            ->add('immediateStart', ChoiceType::class, [
                'required'    => false,
                'label'       => false,
                'placeholder' => 'Início imediato',
                'choices'     => ['Sim' => '1', 'Não' => '0'],
            ])
            ->add('hasResume', ChoiceType::class, [
                'required'    => false,
                'label'       => false,
                'placeholder' => 'PDF currículo',
                'choices'     => ['Com PDF' => '1', 'Sem PDF' => '0'],
            ])
            ->add('dateFrom', TextType::class, [
                'required' => false,
                'label'    => false,
                'attr'     => ['type' => 'date'],
            ])
            ->add('dateTo', TextType::class, [
                'required' => false,
                'label'    => false,
                'attr'     => ['type' => 'date'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => null,
            'csrf_protection' => false,
            'method'          => 'GET',
            'tenant'          => null,
        ]);
        $resolver->setAllowedTypes('tenant', ['null', Tenant::class]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
