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
    private const INPUT_ATTR = ['class' => 'field-input'];
    private const LABEL_ATTR = ['class' => 'field-label'];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => "Nom de l'entreprise <span class=\"text-red-500\">*</span>",
                'label_html' => true,
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('siret', TextType::class, [
                'label' => 'SIRET (optionnel)',
                'required' => false,
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('siteWeb', UrlType::class, [
                'label' => 'Site web (optionnel)',
                'required' => false,
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('logo', FileType::class, [
                'label' => 'Logo de l\'entreprise (optionnel)',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG, PNG ou WebP).',
                    ])
                ],
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('contact', TextType::class, [
                'label' => 'Contact (email ou téléphone)',
                'required' => false,
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
                'help' => 'Email ou numéro de téléphone affiché sur la page de détail.',
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
