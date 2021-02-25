<?php

namespace Sprain\SwissQrBill\DataGroup\Element;

use Sprain\SwissQrBill\DataGroup\AddressInterface;
use Sprain\SwissQrBill\DataGroup\QrCodeableInterface;
use Sprain\SwissQrBill\Validator\SelfValidatableInterface;
use Sprain\SwissQrBill\Validator\SelfValidatableTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class CombinedAddress implements AddressInterface, SelfValidatableInterface, QrCodeableInterface
{
    use SelfValidatableTrait;

    const ADDRESS_TYPE = 'K';

    /**
     * Name or company
     *
     * @var string
     */
    private $name;

    /**
     * Address line 1
     *
     * Street and building number or P.O. Box
     *
     * @var string
     */
    private $addressLine1;

    /**
     * Address line 2
     *
     * Postal code and town
     *
     * @var string
     */
    private $addressLine2;

    /**
     * Country (ISO 3166-1 alpha-2)
     *
     * @var string
     */
    private $country;

    public static function create(
        $name,
        $addressLine1,
        $addressLine2,
        $country
    ) {
        $combinedAddress = new self();
        $combinedAddress->name = $name;
        $combinedAddress->addressLine1 = $addressLine1;
        $combinedAddress->addressLine2 = $addressLine2;
        $combinedAddress->country = strtoupper($country);

        return $combinedAddress;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function getFullAddress()
    {
        $address = $this->getName();

        if ($this->getAddressLine1()) {
            $address .= "\n" . $this->getAddressLine1();
        }

        if (in_array($this->getCountry(), ['CH', 'FL'])) {
            $address .= "\n" . $this->getAddressLine2();
        } else {
            $address .= sprintf("\n%s-%s", $this->getCountry(), $this->getAddressLine2());
        }

        return $address;
    }

    public function getQrCodeData()
    {
        return [
            $this->getAddressLine2() ? self::ADDRESS_TYPE : '',
            $this->getName(),
            $this->getAddressLine1(),
            $this->getAddressLine2(),
            '',
            '',
            $this->getCountry()
        ];
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new Assert\NotBlank());
        $metadata->addPropertyConstraint('name', new Assert\Length([
            'max' => 70
        ]));


        $metadata->addPropertyConstraint('addressLine1', new Assert\Length([
            'max' => 70
        ]));

        $metadata->addPropertyConstraint('addressLine2', new Assert\NotBlank());
        $metadata->addPropertyConstraint('addressLine2', new Assert\Length([
            'max' => 70
        ]));

        $metadata->addPropertyConstraint('country', new Assert\NotBlank());
        $metadata->addPropertyConstraint('country', new Assert\Country());
    }
}
