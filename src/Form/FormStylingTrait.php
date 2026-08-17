<?php

namespace App\Form;

/**
 * Constantes de style Tailwind partagees entre tous les FormTypes (audit M2).
 */
trait FormStylingTrait
{
    private const INPUT_ATTR = ['class' => 'field-input'];
    private const LABEL_ATTR = ['class' => 'field-label'];
    private const REQUIRED_STAR = ' <span class="text-red-500">*</span>';
}
