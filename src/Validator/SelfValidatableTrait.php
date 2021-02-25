<?php

namespace Sprain\SwissQrBill\Validator;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait SelfValidatableTrait
{
    /** @var ValidatorInterface */
    private $validator;

    public function getViolations()
    {
        if (null == $this->validator) {
            $this->validator = Validation::createValidatorBuilder()
                ->addMethodMapping('loadValidatorMetadata')
                ->getValidator();
        }

        return $this->validator->validate($this);
    }

    public function isValid()
    {
        if (0 == $this->getViolations()->count()) {
            return true;
        }

        return false;
    }
}
