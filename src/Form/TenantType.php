<?php

namespace App\Form;

use App\Entity\Tenant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class TenantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nome do Tenant',
                'attr'  => ['placeholder' => 'Ex: Procordis'],
            ])
            ->add('domain', TextType::class, [
                'label' => 'Domínio',
                'attr'  => ['placeholder' => 'carreiras.procordis.com.br'],
                'help'  => 'Apenas o host, sem http:// e sem barra final.',
            ])
            ->add('contactEmail', EmailType::class, [
                'label'    => 'E-mail de contato / RH',
                'required' => false,
                'attr'     => ['placeholder' => 'rh@procordis.com.br'],
            ])
            ->add('phone', TextType::class, [
                'label'    => 'Telefone',
                'required' => false,
            ])
            ->add('address', TextType::class, [
                'label'    => 'Endereço',
                'required' => false,
            ])
            ->add('activeTheme', TextType::class, [
                'label'    => 'Tema ativo',
                'required' => false,
                'attr'     => ['placeholder' => 'moderno'],
                'help'     => 'Nome do tema Twig a ser utilizado.',
            ])
            ->add('primaryColor', ColorType::class, [
                'label'    => 'Cor primária (light)',
                'required' => false,
            ])
            ->add('secondaryColor', ColorType::class, [
                'label'    => 'Cor secundária (light)',
                'required' => false,
            ])
            ->add('primaryColorDark', ColorType::class, [
                'label'    => 'Cor primária (dark)',
                'required' => false,
            ])
            ->add('secondaryColorDark', ColorType::class, [
                'label'    => 'Cor secundária (dark)',
                'required' => false,
            ])
            ->add('seoTitle', TextType::class, [
                'label'    => 'Título SEO',
                'required' => false,
            ])
            ->add('seoDescription', TextareaType::class, [
                'label'    => 'Descrição SEO',
                'required' => false,
                'attr'     => ['rows' => 3],
            ])
            ->add('recaptchaSiteKey', TextType::class, [
                'label'    => 'reCAPTCHA Site Key',
                'required' => false,
            ])
            ->add('recaptchaSecretKey', TextType::class, [
                'label'    => 'reCAPTCHA Secret Key',
                'required' => false,
            ])
            ->add('lgpdTermsHtml', TextareaType::class, [
                'label'    => 'Termos de Consentimento LGPD (HTML)',
                'required' => false,
                'attr'     => ['rows' => 8, 'class' => 'quill-editor'],
            ])
            ->add('privacyPolicyHtml', TextareaType::class, [
                'label'    => 'Política de Privacidade (HTML)',
                'required' => false,
                'attr'     => ['rows' => 10, 'class' => 'quill-editor'],
            ])
            ->add('logoFile', VichImageType::class, [
                'label'         => 'Logo (light)',
                'required'      => false,
                'allow_delete'  => true,
                'download_uri'  => false,
                'image_uri'     => true,
            ])
            ->add('darkLogoFile', VichImageType::class, [
                'label'         => 'Logo (dark)',
                'required'      => false,
                'allow_delete'  => true,
                'download_uri'  => false,
                'image_uri'     => true,
            ])
            ->add('faviconFile', VichImageType::class, [
                'label'         => 'Favicon',
                'required'      => false,
                'allow_delete'  => true,
                'download_uri'  => false,
                'image_uri'     => true,
            ])
            ->add('heroTitle', TextType::class, [
                'label'    => 'Título do Hero (landing page)',
                'required' => false,
                'attr'     => ['placeholder' => 'Faça parte do nosso time'],
            ])
            ->add('heroSubtitle', TextType::class, [
                'label'    => 'Subtítulo do Hero',
                'required' => false,
                'attr'     => ['placeholder' => 'Cadastre seu currículo'],
            ])
            ->add('heroDescription', TextareaType::class, [
                'label'    => 'Descrição institucional (landing page)',
                'required' => false,
                'attr'     => ['rows' => 4, 'placeholder' => 'Texto sobre a empresa, cultura, valores...'],
            ])
            ->add('ctaText', TextType::class, [
                'label'    => 'Texto do botão CTA',
                'required' => false,
                'attr'     => ['placeholder' => 'Cadastre seu currículo'],
            ])
            ->add('ctaSubtext', TextType::class, [
                'label'    => 'Texto de apoio do CTA',
                'required' => false,
                'attr'     => ['placeholder' => 'Processo rápido e seguro'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tenant::class,
        ]);
    }
}
