<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use App\Validator\NotAllUpperCase;

class NotAllUpperCaseValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        /** @var NotAllUpperCase $constraint */

        if (null === $value || '' === $value) {
            return;
        }
        
        if ( $value === mb_strtoupper((string) $value) ) {
            $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
            return;
        }
        $upperCase = 0;
        $lowerCase = 0;
        $length = mb_strlen((string) $value);
        foreach (str_split((string) $value) as $char) {
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
