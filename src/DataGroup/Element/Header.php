<?php

namespace Sprain\SwissQrBill\DataGroup\Element;

use Sprain\SwissQrBill\DataGroup\QrCodeableInterface;
use Sprain\SwissQrBill\Validator\SelfValidatableInterface;
use Sprain\SwissQrBill\Validator\SelfValidatableTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Header implements QrCodeableInterface, SelfValidatableInterface
{
    use SelfValidatableTrait;

    const QRTYPE_SPC = 'SPC';
    const VERSION_0200 = '0200';
    const CODING_LATIN = 1;

    /**
     * Unambiguous indicator for the Swiss QR code.
     *
     * @var string
     */
    private $qrType;

    /**
     * Version of the specifications (Implementation Guidelines) in use on
     * the date on which the Swiss QR code was created.
     * The first two positions indicate the main version, the following the
     * two positions the sub-version ("0200" for version 2.0).
     *
     * @var string
     */
    private $version;

    /**
     * Character set code
     *
     * @var int
     */
    private $coding;

    public static function create($qrType, $version, $coding)
    {
        $header = new self();
        $header->coding = $coding;
        $header->qrType = $qrType;
        $header->version = $version;

        return $header;
    }

    public function getQrType()
    {
        return $this->qrType;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getCoding()
    {
        return $this->coding;
    }

    public function getQrCodeData()
    {
        return [
            $this->getQrType(),
            $this->getVersion(),
            $this->getCoding()
        ];
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        // Fixed length, three-digit, alphanumeric
        $metadata->addPropertyConstraint('qrType', new Assert\NotBlank());
        $metadata->addPropertyConstraint('qrType', new Assert\Regex([
            'pattern' => '/^[a-zA-Z0-9]{3}$/',
            'match' => true
        ]));

        // Fixed length, four-digit, numeric
        $metadata->addPropertyConstraint('version', new Assert\NotBlank());
        $metadata->addPropertyConstraint('version', new Assert\Regex([
            'pattern' => '/^\d{4}$/',
            'match' => true
        ]));


        // One-digit, numeric
        $metadata->addPropertyConstraint('coding', new Assert\NotBlank());
        $metadata->addPropertyConstraint('coding', new Assert\Regex([
            'pattern' => '/^\d{1}$/',
            'match' => true
        ]));
    }
}
