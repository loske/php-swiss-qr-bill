<?php

namespace Sprain\SwissQrBill\DataGroup\Element;

use Sprain\SwissQrBill\DataGroup\QrCodeableInterface;
use Sprain\SwissQrBill\Validator\SelfValidatableInterface;
use Sprain\SwissQrBill\Validator\SelfValidatableTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class AlternativeScheme implements QrCodeableInterface, SelfValidatableInterface
{
    use SelfValidatableTrait;

    /**
     * Parameter character chain of the alternative scheme
     *
     * @var string
     */
    private $parameter;

    public static function create($parameter)
    {
        $alternativeScheme = new self();
        $alternativeScheme->parameter = $parameter;

        return $alternativeScheme;
    }

    public function getParameter()
    {
        return $this->parameter;
    }

    public function getQrCodeData()
    {
        return [
            $this->getParameter()
        ];
    }

    /**
     * Note that no real-life alternative schemes yet exist. Therefore validation is kept simple yet.
     * @link https://www.paymentstandards.ch/en/home/software-partner/alternative-schemes.html
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('parameter', new Assert\NotBlank());
        $metadata->addPropertyConstraint('parameter', new Assert\Length([
            'max' => 100
        ]));
    }
}
