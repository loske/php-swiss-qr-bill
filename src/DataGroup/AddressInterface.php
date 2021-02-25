<?php

namespace Sprain\SwissQrBill\DataGroup;

interface AddressInterface
{
    public function getName();

    public function getCountry();

    public function getFullAddress();
}
