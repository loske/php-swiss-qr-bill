<?php

namespace Sprain\SwissQrBill\PaymentPart\Output\Element;

class Text implements OutputElementInterface
{
    private $text;

    public static function create(string $text)
    {
        $element = new self();
        $element->text = $text;

        return $element;
    }

    public function getText()
    {
        return $this->text;
    }
}
