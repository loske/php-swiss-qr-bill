<?php

namespace Sprain\SwissQrBill\PaymentPart\Output\Element;

class Title implements OutputElementInterface
{
    private $title;

    public static function create(string $title)
    {
        $element = new self();
        $element->title = $title;

        return $element;
    }

    public function getTitle()
    {
        return $this->title;
    }
}
