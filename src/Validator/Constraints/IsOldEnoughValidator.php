<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class IsOldEnoughValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof IsOldEnough) {
            throw new UnexpectedTypeException($constraint, IsOldEnough::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof \DateTimeInterface) {
            throw new UnexpectedValueException($value, '\DateTimeInterface');
        }

        $today = new \DateTime();
        $ageLimit = $today->modify('-16 years');

        if ($value > $ageLimit) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
