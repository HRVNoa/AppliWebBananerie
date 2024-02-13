<?php
// src/Validator/Constraints/IsAgeNotTooHigh.php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class IsAgeNotTooHigh extends Constraint
{
    public $message = 'L\'âge ne doit pas dépasser 110 ans.';
}
