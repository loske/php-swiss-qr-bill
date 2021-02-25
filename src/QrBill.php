<?php

namespace Sprain\SwissQrBill;

use Endroid\QrCode\ErrorCorrectionLevel;
use Sprain\SwissQrBill\Constraint\ValidCreditorInformationPaymentReferenceCombination;
use Sprain\SwissQrBill\DataGroup\AddressInterface;
use Sprain\SwissQrBill\DataGroup\Element\AdditionalInformation;
use Sprain\SwissQrBill\DataGroup\Element\AlternativeScheme;
use Sprain\SwissQrBill\DataGroup\Element\CreditorInformation;
use Sprain\SwissQrBill\DataGroup\Element\Header;
use Sprain\SwissQrBill\DataGroup\Element\PaymentAmountInformation;
use Sprain\SwissQrBill\DataGroup\Element\PaymentReference;
use Sprain\SwissQrBill\DataGroup\Element\StructuredAddress;
use Sprain\SwissQrBill\DataGroup\QrCodeableInterface;
use Sprain\SwissQrBill\Exception\InvalidQrBillDataException;
use Sprain\SwissQrBill\QrCode\QrCode;
use Sprain\SwissQrBill\String\StringModifier;
use Sprain\SwissQrBill\Validator\SelfValidatableInterface;
use Sprain\SwissQrBill\Validator\SelfValidatableTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class QrBill implements SelfValidatableInterface
{
    use SelfValidatableTrait;

    const ERROR_CORRECTION_LEVEL_HIGH = ErrorCorrectionLevel::HIGH;
    const ERROR_CORRECTION_LEVEL_MEDIUM = ErrorCorrectionLevel::MEDIUM;
    const ERROR_CORRECTION_LEVEL_LOW = ErrorCorrectionLevel::LOW;

    const SWISS_CROSS_LOGO_FILE = __DIR__ . '/../assets/swiss-cross.optimized.png';

    const PX_QR_CODE = 543;    // recommended 46x46 mm in px @ 300dpi – in pixel based outputs, the final image size may be slightly different, depending on the qr code contents
    const PX_SWISS_CROSS = 83; // recommended 7x7 mm in px @ 300dpi

    /** @var Header */
    private $header;

    /** @var CreditorInformation */
    private $creditorInformation;

    /** @var AddressInterface*/
    private $creditor;

    /** @var PaymentAmountInformation */
    private $paymentAmountInformation;

    /** @var AddressInterface*/
    private $ultimateDebtor;

    /** @var PaymentReference */
    private $paymentReference;

    /** @var AdditionalInformation */
    private $additionalInformation;

    /** @var AlternativeScheme[] */
    private $alternativeSchemes = [];

    /** @var string  */
    private $errorCorrectionLevel = self::ERROR_CORRECTION_LEVEL_MEDIUM;

    public static function create()
    {
        $header = Header::create(
            Header::QRTYPE_SPC,
            Header::VERSION_0200,
            Header::CODING_LATIN
        );

        $qrBill = new self();
        $qrBill->header = $header;

        return $qrBill;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function setHeader(Header $header)
    {
        $this->header = $header;

        return $this;
    }

    public function getCreditorInformation()
    {
        return $this->creditorInformation;
    }

    public function setCreditorInformation(CreditorInformation $creditorInformation)
    {
        $this->creditorInformation = $creditorInformation;

        return $this;
    }

    public function getCreditor()
    {
        return $this->creditor;
    }

    public function setCreditor(AddressInterface $creditor)
    {
        $this->creditor = $creditor;
        
        return $this;
    }

    public function getPaymentAmountInformation()
    {
        return $this->paymentAmountInformation;
    }

    public function setPaymentAmountInformation(PaymentAmountInformation $paymentAmountInformation)
    {
        $this->paymentAmountInformation = $paymentAmountInformation;
        
        return $this;
    }

    public function getUltimateDebtor()
    {
        return $this->ultimateDebtor;
    }

    public function setUltimateDebtor(AddressInterface $ultimateDebtor)
    {
        $this->ultimateDebtor = $ultimateDebtor;
        
        return $this;
    }

    public function getPaymentReference()
    {
        return $this->paymentReference;
    }

    public function setPaymentReference(PaymentReference $paymentReference)
    {
        $this->paymentReference = $paymentReference;
        
        return $this;
    }

    public function getAdditionalInformation()
    {
        return $this->additionalInformation;
    }

    public function setAdditionalInformation(AdditionalInformation $additionalInformation)
    {
        $this->additionalInformation = $additionalInformation;

        return $this;
    }

    public function getAlternativeSchemes()
    {
        return $this->alternativeSchemes;
    }

    public function setAlternativeSchemes(array $alternativeSchemes)
    {
        $this->alternativeSchemes = $alternativeSchemes;

        return $this;
    }

    public function addAlternativeScheme(AlternativeScheme $alternativeScheme)
    {
        $this->alternativeSchemes[] = $alternativeScheme;

        return $this;
    }

    /**
     * @deprecated Will be removed in v3. The specs require the error correction level to be fixed at medium.
     */
    public function setErrorCorrectionLevel(string $errorCorrectionLevel)
    {
        $this->errorCorrectionLevel = $errorCorrectionLevel;

        return $this;
    }

    public function getQrCode()
    {
        if (!$this->isValid()) {
            throw new InvalidQrBillDataException(
                'The provided data is not valid to generate a qr code. Use getViolations() to find details.'
            );
        }

        $qrCode = new QrCode();
        $qrCode->setText($this->getQrCodeContent());
        $qrCode->setSize(self::PX_QR_CODE);
//        $qrCode->setLogoHeight(self::PX_SWISS_CROSS);
        $qrCode->setLogoWidth(self::PX_SWISS_CROSS);
        $qrCode->setLogoPath(self::SWISS_CROSS_LOGO_FILE);
//        $qrCode->setRoundBlockSize(true, \Endroid\QrCode\QrCode::ROUND_BLOCK_SIZE_MODE_ENLARGE);
        $qrCode->setMargin(0);
        $qrCode->setErrorCorrectionLevel(new ErrorCorrectionLevel($this->errorCorrectionLevel));

        return $qrCode;
    }

    private function getQrCodeContent()
    {
        $elements = [
            $this->getHeader(),
            $this->getCreditorInformation(),
            $this->getCreditor(),
            new StructuredAddress(), # Placeholder for ultimateCreditor, which is currently not yet allowed to be used by the implementation guidelines
            $this->getPaymentAmountInformation(),
            $this->getUltimateDebtor() ?: new StructuredAddress(),
            $this->getPaymentReference(),
            $this->getAdditionalInformation() ?: new AdditionalInformation(),
            $this->getAlternativeSchemes()
        ];

        $qrCodeStringElements = $this->extractQrCodeDataFromElements($elements);

        return implode("\n", $qrCodeStringElements);
    }

    private function extractQrCodeDataFromElements(array $elements)
    {
        $qrCodeElements = [];

        foreach ($elements as $element) {
            if ($element instanceof QrCodeableInterface) {
                $qrCodeElements = array_merge($qrCodeElements, $element->getQrCodeData());
            } elseif (is_array($element)) {
                $qrCodeElements = array_merge($qrCodeElements, $this->extractQrCodeDataFromElements($element));
            }
        }

        array_walk($qrCodeElements, function (&$string) {
            if (is_string($string)) {
                $string = StringModifier::replaceLineBreaksWithString($string);
                $string = StringModifier::replaceMultipleSpacesWithOne($string);
                $string = trim($string);
            }
        });

        return $qrCodeElements;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(
            new ValidCreditorInformationPaymentReferenceCombination()
        );

        $metadata->addPropertyConstraint('header', new Assert\NotNull());
        $metadata->addPropertyConstraint('header', new Assert\Valid());

        $metadata->addPropertyConstraint('creditorInformation', new Assert\NotNull());
        $metadata->addPropertyConstraint('creditorInformation', new Assert\Valid());

        $metadata->addPropertyConstraint('creditor', new Assert\NotNull());
        $metadata->addPropertyConstraint('creditor', new Assert\Valid());

        $metadata->addPropertyConstraint('paymentAmountInformation', new Assert\NotNull());
        $metadata->addPropertyConstraint('paymentAmountInformation', new Assert\Valid());

        $metadata->addPropertyConstraint('ultimateDebtor', new Assert\Valid());

        $metadata->addPropertyConstraint('paymentReference', new Assert\NotNull());
        $metadata->addPropertyConstraint('paymentReference', new Assert\Valid());

        $metadata->addPropertyConstraint('alternativeSchemes', new Assert\Count([
            'max' => 2
        ]));
        $metadata->addPropertyConstraint('alternativeSchemes', new Assert\Valid([
            'traverse' => true
        ]));
    }
}
