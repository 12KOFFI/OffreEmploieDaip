<?php

namespace App\Form;

use App\Entity\Entreprise;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntrepriseProfilType extends AbstractType
{
    private const INPUT_ATTR = ['class' => 'field-input'];
    private const LABEL_ATTR = ['class' => 'field-label'];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => "Nom de l'entreprise",
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
            ->add('logo', TextType::class, [
                'label' => "URL du logo (optionnel)",
                'required' => false,
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
                'help' => "Pas encore d'upload de fichier — collez l'URL d'une image hébergée ailleurs pour l'instant.",
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
