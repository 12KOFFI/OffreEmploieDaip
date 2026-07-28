<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class EntrepriseRegistrationType extends AbstractType
{
    private const INPUT_ATTR = ['class' => 'field-input'];
    private const LABEL_ATTR = ['class' => 'field-label'];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email professionnel',
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => ['label' => 'Mot de passe', 'attr' => self::INPUT_ATTR, 'label_attr' => self::LABEL_ATTR],
                'second_options' => ['label' => 'Confirmer le mot de passe', 'attr' => self::INPUT_ATTR, 'label_attr' => self::LABEL_ATTR],
                'invalid_message' => 'Les deux mots de passe ne correspondent pas.',
                'constraints' => [
                    new NotBlank(message: 'Merci de saisir un mot de passe.'),
                    new Length(
                        min: 8,
                        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caracteres.',
                        max: 4096,
                    ),
                ],
            ])
            ->add('nom', TextType::class, [
                'label' => "Nom de l'entreprise",
                'property_path' => 'entreprise.nom',
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('siret', TextType::class, [
                'label' => 'SIRET (optionnel)',
                'required' => false,
                'property_path' => 'entreprise.siret',
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('description', TextareaType::class, [
                'label' => "Description de l'entreprise (optionnel)",
                'required' => false,
                'property_path' => 'entreprise.description',
                'attr' => self::INPUT_ATTR + ['rows' => 4],
                'label_attr' => self::LABEL_ATTR,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
