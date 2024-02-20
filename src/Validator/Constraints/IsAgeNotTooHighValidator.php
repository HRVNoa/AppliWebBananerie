<?php
// src/Validator/Constraints/IsAgeNotTooHighValidator.php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class IsAgeNotTooHighValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof IsAgeNotTooHigh) {
            throw new UnexpectedTypeException($constraint, IsAgeNotTooHigh::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof \DateTimeInterface) {
            throw new UnexpectedValueException($value, '\DateTimeInterface');
        }

        $limitDate = new \DateTime('-110 years');

        if ($value < $limitDate) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
