<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NotAllUpperCaseValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /** @var App\Validator\NotAllUpperCase $constraint */

        if (null === $value || '' === $value) {
            return;
        }
        
        if ( $value === mb_strtoupper($value) ) {
            $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
            return;
        }
        $upperCase = 0;
        $lowerCase = 0;
        $length = mb_strlen($value);
        foreach (str_split($value) as $char) {
            if ( ctype_upper($char) ) {
                $upperCase++; 
            } else {
                $lowerCase++;
            }
        }
        if ($upperCase/$length >= 0.6) {
            $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
        }
    }
}
