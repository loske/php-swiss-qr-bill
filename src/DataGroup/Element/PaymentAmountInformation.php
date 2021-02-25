<?php

namespace Sprain\SwissQrBill\DataGroup\Element;

use Sprain\SwissQrBill\DataGroup\QrCodeableInterface;
use Sprain\SwissQrBill\Validator\SelfValidatableInterface;
use Sprain\SwissQrBill\Validator\SelfValidatableTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class PaymentAmountInformation implements QrCodeableInterface, SelfValidatableInterface
{
    use SelfValidatableTrait;

    const CURRENCY_CHF = 'CHF';
    const CURRENCY_EUR = 'EUR';

    /**
     * The payment amount due
     *
     * @var float
     */
    private $amount;

    /**
     * Payment currency code (ISO 4217)
     *
     * @var string
     */
    private $currency;

    public static function create($currency, $amount = null)
    {
        $paymentInformation = new self();
        $paymentInformation->currency = strtoupper($currency);
        $paymentInformation->amount = $amount;

        return $paymentInformation;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getFormattedAmount()
    {
        if (null === $this->amount) {
            return '';
        }

        return number_format(
            $this->amount,
            2,
            '.',
            ' '
        );
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getQrCodeData()
    {
        if (null !== $this->getAmount()) {
            $amountOutput = number_format($this->getAmount(), 2, '.', '');
        } else {
            $amountOutput = null;
        }

        return [
            $amountOutput,
            $this->getCurrency()
        ];
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('amount', new Assert\Range([
            'min' => 0,
            'max'=> 999999999.99
        ]));

        $metadata->addPropertyConstraint('currency', new Assert\Choice([
            self::CURRENCY_CHF,
            self::CURRENCY_EUR
        ]));
    }
}
