<?php

namespace Sprain\SwissQrBill\PaymentPart\Output;

use Sprain\SwissQrBill\QrBill;

interface OutputInterface
{
    public function getQrBill(): ?QrBill;

    public function getLanguage();

    public function getPaymentPart();

    public function setPrintable(bool $printable);

    public function isPrintable()

    public function setQrCodeImageFormat(string $imageFormat);

    public function getQrCodeImageFormat();
}
