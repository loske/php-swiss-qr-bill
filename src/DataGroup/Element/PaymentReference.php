<?php

namespace Sprain\SwissQrBill\DataGroup\Element;

use Sprain\SwissQrBill\Constraint\ValidCreditorReference;
use Sprain\SwissQrBill\DataGroup\QrCodeableInterface;
use Sprain\SwissQrBill\String\StringModifier;
use Sprain\SwissQrBill\Validator\SelfValidatableInterface;
use Sprain\SwissQrBill\Validator\SelfValidatableTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class PaymentReference implements GroupSequenceProviderInterface, QrCodeableInterface, SelfValidatableInterface
{
    use SelfValidatableTrait;

    const TYPE_QR = 'QRR';
    const TYPE_SCOR = 'SCOR';
    const TYPE_NON = 'NON';

    /**
     * Reference type
     *
     * @var string
     */
    private $type;

    /**
     * Structured reference number
     * Either a QR reference or a Creditor Reference (ISO 11649)
     *
     * @var string|null
     */
    private $reference;

    public static function create($type, $reference = null)
    {
        $paymentReference = new self();
        $paymentReference->type = $type;
        $paymentReference->reference = $reference;

        if (null !== $paymentReference->reference) {
            $paymentReference->reference = StringModifier::stripWhitespace($paymentReference->reference);
        }

        if ('' === ($paymentReference->reference)) {
            $paymentReference->reference = null;
        }

        return $paymentReference;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function getFormattedReference()
    {
        switch ($this->type) {
            case self::TYPE_QR:
                return trim(strrev(chunk_split(strrev($this->reference), 5, ' ')));
            case self::TYPE_SCOR:
                return trim(chunk_split($this->reference, 4, ' '));
            default:
                return null;
        }
    }

    public function getQrCodeData()
    {
        return [
            $this->getType(),
            $this->getReference()
        ];
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->setGroupSequenceProvider(true);

        $metadata->addPropertyConstraint('type', new Assert\NotBlank([
            'groups' => ['default']
        ]));
        $metadata->addPropertyConstraint('type', new Assert\Choice([
            'groups' => ['default'],
            'choices' => [
                self::TYPE_QR,
                self::TYPE_SCOR,
                self::TYPE_NON
            ]
        ]));

        $metadata->addPropertyConstraint('reference', new Assert\Type([
            'type' => 'alnum',
            'groups' => [self::TYPE_QR]
        ]));
        $metadata->addPropertyConstraint('reference', new Assert\NotBlank([
            'groups' => [self::TYPE_QR, self::TYPE_SCOR]
        ]));
        $metadata->addPropertyConstraint('reference', new Assert\Length([
            'min' => 27,
            'max' => 27,
            'groups' => [self::TYPE_QR]
        ]));
        $metadata->addPropertyConstraint('reference', new Assert\Blank([
            'groups' => [self::TYPE_NON]
        ]));
        $metadata->addPropertyConstraint('reference', new ValidCreditorReference([
            'groups' => [self::TYPE_SCOR]
        ]));
    }

    public function getGroupSequence()
    {
        $groups = [
            'default',
            $this->getType()
        ];

        return $groups;
    }
}
