<?php

namespace App\Form;

use App\Entity\Metier;
use App\Entity\OffreMetier;
use App\Repository\MetierRepository;
use App\Enum\Diplome;
use App\Enum\NiveauEtude;
use App\Enum\TypeContrat;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OffreMetierType extends AbstractType
{
    use FormStylingTrait;

    public function __construct(private readonly MetierRepository $metierRepository) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('metier', EntityType::class, [
                'label'        => 'Métier / Poste' . self::REQUIRED_STAR,
                'label_html'   => true,
                'class'        => Metier::class,
                'choice_label' => 'nom',
                'placeholder'  => 'Rechercher un métier...',
                'attr'         => self::INPUT_ATTR,
                'label_attr'   => self::LABEL_ATTR,
            ])
            ->add('autreMetier', TextType::class, [
                'label'    => 'Nom du nouveau métier',
                'mapped'   => false,
                'required' => false,
                'attr'     => self::INPUT_ATTR + ['class' => 'field-input autre-metier-input', 'placeholder' => 'Ex : Technicien réseau'],
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('typeContrat', EnumType::class, [
                'label'        => 'Type de contrat',
                'class'        => TypeContrat::class,
                'choice_label' => fn(TypeContrat $t) => $t->label(),
                'required'     => false,
                'placeholder'  => '— Non spécifié —',
                'attr'         => self::INPUT_ATTR,
                'label_attr'   => self::LABEL_ATTR,
            ])
            ->add('nombrePostes', IntegerType::class, [
                'label'      => 'Nombre de postes' . self::REQUIRED_STAR,
                'label_html' => true,
                'attr'       => self::INPUT_ATTR + ['min' => 1],
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('ville', TextType::class, [
                'label'      => 'Ville' . self::REQUIRED_STAR,
                'label_html' => true,
                'attr'       => self::INPUT_ATTR + ['placeholder' => 'Ex : Abidjan'],
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('niveauEtude', EnumType::class, [
                'label'        => "Niveau d'étude",
                'class'        => NiveauEtude::class,
                'choice_label' => fn(NiveauEtude $n) => $n->label(),
                'required'     => false,
                'placeholder'  => '— Optionnel —',
                'attr'         => self::INPUT_ATTR,
                'label_attr'   => self::LABEL_ATTR,
            ])
            ->add('diplome', EnumType::class, [
                'label'        => "Diplôme",
                'class'        => Diplome::class,
                'choice_label' => fn(Diplome $d) => $d->label(),
                'required'     => false,
                'placeholder'  => '— Optionnel —',
                'attr'         => self::INPUT_ATTR,
                'label_attr'   => self::LABEL_ATTR,
            ])
            ->add('nbAnneesExperience', IntegerType::class, [
                'label'      => "Années d'expérience",
                'required'   => false,
                'attr'       => self::INPUT_ATTR + ['min' => 0, 'placeholder' => 'Ex : 2'],
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('salaireMin', IntegerType::class, [
                'label'      => 'Salaire Min',
                'required'   => false,
                'attr'       => self::INPUT_ATTR + ['placeholder' => 'Ex : 300000', 'step' => 10000],
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('salaireMax', IntegerType::class, [
                'label'      => 'Salaire Max',
                'required'   => false,
                'attr'       => self::INPUT_ATTR + ['placeholder' => 'Ex : 500000', 'step' => 10000],
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('prerequis', TextareaType::class, [
                'label'      => 'Prérequis spécifiques',
                'required'   => false,
                'attr'       => self::INPUT_ATTR + ['rows' => 3, 'placeholder' => 'Compétences, certifications, langues (Optionnel)…'],
                'label_attr' => self::LABEL_ATTR,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OffreMetier::class,
        ]);
    }
}
