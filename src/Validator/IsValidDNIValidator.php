<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Validator;

use App\Utils\Validaciones;
use Doctrine\Common\Annotations\Annotation;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Description of DNIControlDigitValidator.
 *
 * @author ibilbao
 */

/**
 * @Annotation
 */
class IsValidDNIValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\IsValidDNI */

        if (null === $value || '' === $value) {
            return;
        }

        $return = Validaciones::valida_nif_cif_nie($value);

        if ($return <= 0) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
