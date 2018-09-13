<?php
/**
 * Description
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/${Package}
 */
namespace heidelpay\NmgPhpSdk\TransactionTypes;

use heidelpay\NmgPhpSdk\Exceptions\MissingResourceException;
use heidelpay\NmgPhpSdk\HeidelpayResourceInterface;
use heidelpay\NmgPhpSdk\Payment;
use heidelpay\NmgPhpSdk\PaymentTypes\PaymentTypeInterface;
use heidelpay\NmgPhpSdk\Traits\HasCancellationsTrait;

class Authorization extends AbstractTransactionType
{
    use HasCancellationsTrait;

    /** @var float $amount */
    protected $amount = 0.0;

    /** @var string $currency */
    protected $currency = '';

    /** @var string $returnUrl */
    protected $returnUrl = '';

    /** @var string $uniqueId */
    private $uniqueId = '';

    /**
     * Authorization constructor.
     * @param float $amount
     * @param string $currency
     * @param string $returnUrl
     */
    public function __construct($amount, $currency, $returnUrl)
    {
        $this->setAmount($amount);
        $this->setCurrency($currency);
        $this->setReturnUrl($returnUrl);

        parent::__construct(null);
    }

    //<editor-fold desc="Setters/Getters">
    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return HeidelpayResourceInterface
     */
    public function setAmount(float $amount): HeidelpayResourceInterface
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return HeidelpayResourceInterface
     */
    public function setCurrency(string $currency): HeidelpayResourceInterface
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }

    /**
     * @param string $returnUrl
     * @return HeidelpayResourceInterface
     */
    public function setReturnUrl(string $returnUrl): HeidelpayResourceInterface
    {
        $this->returnUrl = $returnUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    /**
     * @param string $uniqueId
     * @return HeidelpayResourceInterface
     */
    public function setUniqueId(string $uniqueId): HeidelpayResourceInterface
    {
        $this->uniqueId = $uniqueId;
        return $this;
    }
    //</editor-fold>

    //<editor-fold desc="Overridable Methods">
    /**
     * {@inheritDoc}
     */
    public function getResourcePath()
    {
        return 'authorize';
    }

    /**
     * {@inheritDoc}
     */
    public function getLinkedResources(): array
    {
        /** @var Payment $payment */
        $payment = $this->getPayment();
        $paymentType = $payment ? $payment->getPaymentType() : null;
        if (!$paymentType instanceof PaymentTypeInterface) {
            throw new MissingResourceException();
        }

        return [
            'customer'=> $payment->getCustomer(),
            'type' => $paymentType
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function handleResponse(\stdClass $response)
    {
        $isError = isset($response->isError) && $response->isError;
        if ($isError) {
            return;
        }

        /** @var Payment $payment */
        $payment = $this->getPayment();
        if (isset($response->resources->paymentId)) {
            $payment->setId($response->resources->paymentId);
        }

        if (isset($response->redirectUrl)) {
            $payment->setRedirectUrl($response->redirectUrl);
        }

        parent::handleResponse($response);
    }
    //</editor-fold>

    /**
     * Full cancel of this authorization.
     *
     * @return Cancellation
     */
    public function cancel(): Cancellation
    {
        $cancellation = new Cancellation();
        $this->addCancellation($cancellation);
        $cancellation->setPayment($this->getPayment());
        $cancellation->create();

        return $cancellation;
    }
}
