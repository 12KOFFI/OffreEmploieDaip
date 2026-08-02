<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'constraints' => [
                        new NotBlank(),
                        new Length(['min' => 6, 'max' => 4096]),
                    ],
                    'label' => 'Nouveau mot de passe <span class="text-red-500">*</span>',
                    'label_html' => true,
                    'attr' => ['class' => 'field-input', 'placeholder' => '••••••••'],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe <span class="text-red-500">*</span>',
                    'label_html' => true,
                    'attr' => ['class' => 'field-input', 'placeholder' => '••••••••'],
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'attr' => ['class' => 'field-input'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Réinitialiser le mot de passe',
                'attr' => ['class' => 'btn-orange w-full'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}