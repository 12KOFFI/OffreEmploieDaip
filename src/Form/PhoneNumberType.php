<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class PhoneNumberType extends AbstractType
{
    public const COUNTRY_CODES = [
        '+225' => '+225',
        '+221' => '+221',
        '+33' => '+33',
        '+229' => '+229',
        '+228' => '+228',
        '+226' => '+226',
        '+223' => '+223',
        '+237' => '+237',
        '+224' => '+224',
        '+227' => '+227',
        '+241' => '+241',
        '+242' => '+242',
        '+243' => '+243',
        '+212' => '+212',
        '+213' => '+213',
        '+216' => '+216',
        '+1' => '+1',
        '+32' => '+32',
        '+41' => '+41',
        '+44' => '+44',
        '+233' => '+233',
        '+234' => '+234',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isRequired = $options['required'] ?? true;

        $numeroConstraints = [];
        if ($isRequired) {
            $numeroConstraints[] = new NotBlank([
                'message' => 'Le numéro de téléphone est obligatoire.',
            ]);
        }
        $numeroConstraints[] = new Regex([
            'pattern' => '/^\d{10}$/',
            'message' => 'Le numéro doit contenir exactement 10 chiffres.',
        ]);

        $builder
            ->add('indicatif', ChoiceType::class, [
                'choices' => self::COUNTRY_CODES,
                'data' => '+225',
                'attr' => [
                    'class' => 'w-[100px] sm:w-[110px] shrink-0 border-0 border-r border-slate-200 bg-slate-50/80 py-3 sm:py-2.5 text-[15px] sm:text-sm font-semibold text-slate-700 focus:outline-none focus:ring-0 cursor-pointer hover:bg-slate-100 transition-colors min-h-[48px] sm:min-h-0',
                ],
                'label' => false,
            ])
            ->add('numero', TelType::class, [
                'required' => $isRequired,
                'attr' => [
                    'inputmode' => 'numeric',
                    'pattern' => '[0-9]{10}',
                    'maxlength' => '10',
                    'placeholder' => 'Ex: 0701020304',
                    'class' => 'flex-1 min-w-0 w-full bg-transparent border-0 px-3.5 py-3 sm:py-2.5 text-[16px] sm:text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-0 font-medium min-h-[48px] sm:min-h-0',
                    'oninput' => "this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)",
                ],
                'constraints' => $numeroConstraints,
                'label' => false,
            ]);

        $builder->addModelTransformer(new CallbackTransformer(
            // Transform model (string) to form view (array)
            function (?string $phoneString): array {
                if (empty($phoneString)) {
                    return [
                        'indicatif' => '+225',
                        'numero' => '',
                    ];
                }

                if (preg_match('/^(\+\d{1,4})\s*(.*)$/', trim($phoneString), $matches)) {
                    return [
                        'indicatif' => $matches[1],
                        'numero' => preg_replace('/\D/', '', $matches[2]),
                    ];
                }

                return [
                    'indicatif' => '+225',
                    'numero' => preg_replace('/\D/', '', $phoneString),
                ];
            },
            // Transform form view (array) to model (string)
            function (?array $phoneArray): ?string {
                if (empty($phoneArray)) {
                    return null;
                }

                $indicatif = trim($phoneArray['indicatif'] ?? '+225');
                $numero = trim(preg_replace('/\D/', '', $phoneArray['numero'] ?? ''));

                if (empty($numero)) {
                    return null;
                }

                return $indicatif . ' ' . $numero;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'compound' => true,
            'error_bubbling' => false,
        ]);
    }
}
