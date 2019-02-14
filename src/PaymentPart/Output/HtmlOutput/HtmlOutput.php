<?php

namespace Sprain\SwissQrBill\PaymentPart\Output\HtmlOutput;

use Sprain\SwissQrBill\PaymentPart\Output\AbstractOutput;
use Sprain\SwissQrBill\PaymentPart\Output\HtmlOutput\Template\ContentElementTemplate;
use Sprain\SwissQrBill\PaymentPart\Output\HtmlOutput\Template\PaymentPartTemplate;
use Sprain\SwissQrBill\PaymentPart\Output\OutputInterface;
use Sprain\SwissQrBill\PaymentPart\Translation\Translation;

final class HtmlOutput extends AbstractOutput implements OutputInterface
{
    public function getPaymentPart() : string
    {
        $paymentPart = PaymentPartTemplate::TEMPLATE;

        $paymentPart = $this->addSwissQrCodeImage($paymentPart);
        $paymentPart = $this->addInformationContent($paymentPart);
        $paymentPart = $this->addInformationContentReceipt($paymentPart);
        $paymentPart = $this->addCurrencyContent($paymentPart);
        $paymentPart = $this->addAmountContent($paymentPart);

        $paymentPart = $this->translateContents($paymentPart, $this->getLanguage());

        return $paymentPart;
    }

    private function addSwissQrCodeImage(string $paymentPart) : string
    {
        $paymentPart = str_replace('{{ swiss-qr-image }}', $this->qrBill->getQrCode()->writeDataUri(), $paymentPart);

        return $paymentPart;
    }

    private function addInformationContent(string $paymentPart) : string
    {
        $informationContent = '';

        foreach($this->getInformationElements() as $key => $content) {
            $informationContentPart = $this->getContentElement('{{ '.$key.' }}', $content);
            $informationContent .= $informationContentPart;
        }

        $paymentPart = str_replace('{{ information-content }}', $informationContent, $paymentPart);

        return $paymentPart;
    }

    private function addInformationContentReceipt(string $paymentPart) : string
    {
        $informationContent = '';

        foreach($this->getInformationElementsOfReceipt() as $key => $content) {
            $informationContentPart = $this->getContentElement('{{ '.$key.' }}', $content);
            $informationContent .= $informationContentPart;
        }

        $paymentPart = str_replace('{{ information-content-receipt }}', $informationContent, $paymentPart);

        return $paymentPart;
    }

    private function addCurrencyContent(string $paymentPart) : string
    {
        $currencyContent = $this->getContentElement('{{ text.currency }}', $this->qrBill->getPaymentAmountInformation()->getCurrency());
        $paymentPart = str_replace('{{ currency-content }}', $currencyContent, $paymentPart);

        return $paymentPart;
    }

    private function addAmountContent(string $paymentPart) : string
    {
        $amountString = number_format(
            $this->qrBill->getPaymentAmountInformation()->getAmount(),
            2,
            '.',
            ' '
        );

        $amountContent = $this->getContentElement('{{ text.amount }}', $amountString);
        $paymentPart = str_replace('{{ amount-content }}', $amountContent, $paymentPart);

        return $paymentPart;
    }

    private function getContentElement(string $title, string $content) : string
    {
        $contentElementTemplate = ContentElementTemplate::TEMPLATE;
        $contentElement = $contentElementTemplate;

        $contentElement = str_replace('{{ title }}', $title, $contentElement);
        $contentElement = str_replace('{{ content }}', nl2br($content), $contentElement);

        return $contentElement;
    }

    private function translateContents($paymentPart, $language)
    {
        $translations = Translation::getAllByLanguage($language);
        foreach($translations as $key => $text) {
            $paymentPart = str_replace('{{ text.' . $key . ' }}', $text, $paymentPart);
        }

        return $paymentPart;
    }
}