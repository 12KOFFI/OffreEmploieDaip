<?php

namespace App\Form;

use App\Dto\RegistrationDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class EntrepriseRegistrationType extends AbstractType
{
    use FormStylingTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email professionnel <span class="text-red-500">*</span>',
                'label_html' => true,
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Mot de passe <span class="text-red-500">*</span>', 'label_html' => true, 'attr' => self::INPUT_ATTR, 'label_attr' => self::LABEL_ATTR],
                'second_options' => ['label' => 'Confirmer le mot de passe <span class="text-red-500">*</span>', 'label_html' => true, 'attr' => self::INPUT_ATTR, 'label_attr' => self::LABEL_ATTR],
                'invalid_message' => 'Les deux mots de passe ne correspondent pas.',
                'constraints' => [
                    new NotBlank(message: 'Merci de saisir un mot de passe.'),
                    new Length(
                        min: 8,
                        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caracteres.',
                        max: 4096,
                    ),
                    new Regex(
                        pattern: '/^(?=.*[a-zA-Z])(?=.*\d).+$/',
                        message: 'Le mot de passe doit contenir au moins une lettre et un chiffre.',
                    ),
                ],
            ])
            ->add('nom', TextType::class, [
                'label' => "Nom de l'entreprise <span class=\"text-red-500\">*</span>",
                'label_html' => true,
                'required' => true,
                'attr' => self::INPUT_ATTR,
                'label_attr' => self::LABEL_ATTR,
                'constraints' => [
                    new NotBlank(message: "Le nom de l'entreprise est obligatoire."),
                ],
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
                'attr' => self::INPUT_ATTR + ['rows' => 4],
                'label_attr' => self::LABEL_ATTR,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RegistrationDto::class,
        ]);
    }
}
