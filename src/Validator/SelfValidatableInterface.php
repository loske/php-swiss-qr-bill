<?php

namespace Sprain\SwissQrBill\Validator;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

interface SelfValidatableInterface
{
    public function getViolations();

    public function isValid();

    public static function loadValidatorMetadata(ClassMetadata $metadata);
}
