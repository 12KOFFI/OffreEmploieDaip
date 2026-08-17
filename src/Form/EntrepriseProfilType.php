<?php

namespace App\Form;

use App\Entity\Entreprise;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class EntrepriseProfilType extends AbstractType
{
    use FormStylingTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => "Nom de l'entreprise <span class=\"text-red-500\">*</span>",
                'label_html' => true,
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
            ])

            ->add('contact', PhoneNumberType::class, [
                'label' => "Contact de l’entreprise <span class=\"text-red-500\">*</span>",
                'label_html' => true,
                'required' => true,
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('contactResponsable', PhoneNumberType::class, [
                'label' => "Contact du responsable <span class=\"text-red-500\">*</span>",
                'label_html' => true,
                'required' => true,
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('autreContact', PhoneNumberType::class, [
                'label' => 'Autre contact <span class="text-xs font-normal text-slate-400">(optionnel)</span>',
                'label_html' => true,
                'required' => false,
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('description', TextareaType::class, [
                'label' => "Description de l'entreprise (optionnel)",
                'required' => false,
                'attr' => self::INPUT_ATTR + ['rows' => 5],
                'label_attr' => self::LABEL_ATTR,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Entreprise::class,
        ]);
    }
}
