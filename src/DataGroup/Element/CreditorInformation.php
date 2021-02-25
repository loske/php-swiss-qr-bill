<?php

namespace Sprain\SwissQrBill\DataGroup\Element;

use Sprain\SwissQrBill\DataGroup\QrCodeableInterface;
use Sprain\SwissQrBill\Validator\SelfValidatableInterface;
use Sprain\SwissQrBill\Validator\SelfValidatableTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class CreditorInformation implements QrCodeableInterface, SelfValidatableInterface
{
    use SelfValidatableTrait;

    /**
     * IBAN or QR-IBAN of the creditor
     *
     * @var string
     */
    private $iban;

    public static function create($iban)
    {
        $creditorInformation = new self();
        $creditorInformation->iban = preg_replace('/\s+/', '', $iban);

        return $creditorInformation;
    }

    public function getIban()
    {
        return $this->iban;
    }

    public function getFormattedIban()
    {
        if (null === $this->iban) {
            return null;
        }

        return trim(chunk_split($this->iban, 4, ' '));
    }

    public function containsQrIban()
    {
        $qrIid = substr($this->iban, 4, 5);

        if ($this->isValid() && (int) $qrIid >= 30000 && (int) $qrIid <= 31999) {
            return true;
        }

        return false;
    }

    public function getQrCodeData()
    {
        return [
            $this->getIban()
        ];
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        // Only IBANs with CH or LI country code
        $metadata->addPropertyConstraint('iban', new Assert\NotBlank());
        $metadata->addPropertyConstraint('iban', new Assert\Iban());
        $metadata->addPropertyConstraint('iban', new Assert\Regex([
            'pattern' => '/^(CH|LI)/',
            'match' => true
        ]));
    }
}
