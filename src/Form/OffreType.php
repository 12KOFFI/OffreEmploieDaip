<?php

namespace App\Form;

use App\Entity\Offre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class OffreType extends AbstractType
{
    use FormStylingTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label'      => "Titre global de l'offre (optionnel)",
                'label_html' => true,
                'required'   => false,
                'attr'       => self::INPUT_ATTR + ['placeholder' => 'Ex : Campagne de recrutement 2026'],
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('description', TextareaType::class, [
                'label'      => 'Description générale (optionnel)',
                'required'   => false,
                'attr'       => self::INPUT_ATTR + ['rows' => 5, 'placeholder' => 'Présentez le contexte de l\'offre (optionnel)…'],
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('dateExpiration', DateType::class, [
                'label'      => "Date d'expiration (optionnel)",
                'required'   => false,
                'widget'     => 'single_text',
                'attr'       => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('imageFile', FileType::class, [
                'label'       => "Image illustrative (optionnel)",
                'required'    => false,
                'mapped'      => false,
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
                'attr'        => self::INPUT_ATTR,
                'label_attr'  => self::LABEL_ATTR,
            ])
            ->add('offreMetiers', CollectionType::class, [
                'entry_type'    => OffreMetierType::class,
                'entry_options' => ['label' => false],
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
                'label'         => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Offre::class,
        ]);
    }
}
