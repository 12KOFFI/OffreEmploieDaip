<?php

namespace App\Form;

use App\Entity\Competence;
use App\Entity\Offre;
use App\Entity\Secteur;
use App\Enum\TypeContrat;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('niveauEtude', TextType::class, [
                'label' => "Niveau d'étude (optionnel)",
                'required' => false,
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
            ->add('competences', EntityType::class, [
                'label' => 'Compétences requises',
                'class' => Competence::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                // Note : liste evolutive alimentee par les entreprises.
                // Une vraie autocompletion (creation a la volee) necessite
                // un peu de JS cote front - a brancher plus tard, cf README.
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
