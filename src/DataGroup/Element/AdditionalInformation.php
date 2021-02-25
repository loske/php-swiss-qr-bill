<?php

namespace Sprain\SwissQrBill\DataGroup\Element;

use Sprain\SwissQrBill\DataGroup\QrCodeableInterface;
use Sprain\SwissQrBill\Validator\SelfValidatableInterface;
use Sprain\SwissQrBill\Validator\SelfValidatableTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class AdditionalInformation implements QrCodeableInterface, SelfValidatableInterface
{
    use SelfValidatableTrait;

    const TRAILER_EPD = 'EPD';

    /**
     * Unstructured information can be used to indicate the payment purpose
     * or for additional textual information about payments with a structured reference.
     *
     * @var string
     */
    private $message;

    /**
     * Bill information contains coded information for automated booking of the payment.
     * The data is not forwarded with the payment.
     *
     * @var string
     */
    private $billInformation;

    public static function create(
        $message,
        $billInformation = null
    ) {
        $additionalInformation = new self();
        $additionalInformation->message = $message;
        $additionalInformation->billInformation = $billInformation;

        return $additionalInformation;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getBillInformation()
    {
        return $this->billInformation;
    }

    public function getFormattedString()
    {
        $string = $this->getMessage();
        if ($this->getBillInformation()) {
            $string .= "\n".$this->getBillInformation();
        }

        return $string;
    }

    public function getQrCodeData()
    {
        return [
            $this->getMessage(),
            self::TRAILER_EPD,
            $this->getBillInformation()
        ];
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('message', new Assert\Length([
            'max' => 140
        ]));

        $metadata->addPropertyConstraint('billInformation', new Assert\Length([
            'max' => 140
        ]));
    }
}
