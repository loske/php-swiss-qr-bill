<?php

namespace Sprain\SwissQrBill\PaymentPart\Output\HtmlOutput\Template;

class PrintableStylesTemplate
{
    public const TEMPLATE = <<<EOT
#qr-bill-separate-info {
    border-bottom: 0;
}

#qr-bill-separate-info-text {
    display: none;
}

#qr-bill-receipt {
    border-right: 0;
}
EOT;
}
