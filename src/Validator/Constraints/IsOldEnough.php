<?php
// src/Validator/Constraints/IsOldEnough.php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class IsOldEnough extends Constraint
{
public $message = 'L\'âge doit être d\'au moins 16 ans.';
}
