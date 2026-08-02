<?php

namespace App\Form;

use App\Entity\Competence;
use App\Entity\Offre;
use App\Entity\Secteur;
use App\Enum\NiveauEtude;
use App\Enum\TypeContrat;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class OffreType extends AbstractType
{
    private const INPUT_ATTR = ['class' => 'field-input'];
    private const LABEL_ATTR = ['class' => 'field-label'];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre du poste',
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => self::INPUT_ATTR + ['rows' => 6],
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('typeContrat', EnumType::class, [
                'label' => 'Type de contrat',
                'class' => TypeContrat::class,
                'choice_label' => fn (TypeContrat $t) => $t->label(),
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('salaireMin', IntegerType::class, [
                'label' => 'Salaire minimum (FCFA)',
                'required' => false,
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('salaireMax', IntegerType::class, [
                'label' => 'Salaire maximum (FCFA)',
                'required' => false,
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('nbAnneesExperience', IntegerType::class, [
                'label' => "Années d'expérience requises",
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('nombrePostes', IntegerType::class, [
                'label' => 'Nombre de postes à pourvoir',
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('niveauEtude', EnumType::class, [
                'label' => "Niveau d'étude (optionnel)",
                'required' => false,
                'class' => NiveauEtude::class,
                'choice_label' => fn (NiveauEtude $n) => $n->label(),
                'placeholder' => 'Sélectionner un niveau',
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('dateExpiration', DateType::class, [
                'label' => "Date d'expiration (optionnel)",
                'required' => false,
                'widget' => 'single_text',
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('secteur', EntityType::class, [
                'label' => "Secteur d'activité",
                'class' => Secteur::class,
                'choice_label' => 'nom',
                'required' => false,
                'placeholder' => 'Sélectionner un secteur',
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image de l\'offre (optionnel)',
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
            ->add('competences', EntityType::class, [
                'label' => 'Compétences requises',
                'class' => Competence::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
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