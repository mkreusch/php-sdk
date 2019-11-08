<?php
/**
 * This class is the base class for all integration tests of this SDK.
 *
 * Copyright (C) 2018 heidelpay GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/test/integration
 */
namespace heidelpayPHP\test;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\Basket;
use heidelpayPHP\Resources\EmbeddedResources\BasketItem;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\Card;
use heidelpayPHP\Resources\PaymentTypes\Paypal;
use heidelpayPHP\Resources\PaymentTypes\SepaDirectDebit;
use heidelpayPHP\Resources\Recurring;
use heidelpayPHP\Resources\TransactionTypes\AbstractTransactionType;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\Services\EnvironmentService;
use heidelpayPHP\test\Fixtures\CustomerFixtureTrait;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use function strlen;

class BasePaymentTest extends TestCase
{
    use CustomerFixtureTrait;

    /** @var Heidelpay $heidelpay */
    protected $heidelpay;

    const RETURN_URL = 'http://dev.heidelpay.com';

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    protected function setUp()
    {
        $privateKey = (new EnvironmentService())->getTestPrivateKey();
        $this->heidelpay = (new Heidelpay($privateKey))->setDebugHandler(new TestDebugHandler())->setDebugMode(true);
    }

    //<editor-fold desc="Custom asserts">

    /**
     * @param Payment $payment
     * @param float   $expectedRemaining
     * @param float   $expectedCharged
     * @param float   $expectedTotal
     * @param float   $expectedCanceled
     *
     * @throws Exception
     */
    protected function assertAmounts(
        $payment,
        $expectedRemaining,
        $expectedCharged,
        $expectedTotal,
        $expectedCanceled
    ) {
        $amount = $payment->getAmount();
        $this->assertEquals($expectedRemaining, $amount->getRemaining(), 'The remaining amount does not match.');
        $this->assertEquals($expectedCharged, $amount->getCharged(), 'The charged amount does not match.');
        $this->assertEquals($expectedTotal, $amount->getTotal(), 'The total amount does not match.');
        $this->assertEquals($expectedCanceled, $amount->getCanceled(), 'The canceled amount does not match.');
    }

    /**
     * @param mixed $transactionType
     *
     * @throws AssertionFailedError
     */
    public function assertTransactionResourceHasBeenCreated($transactionType)
    {
        $this->assertNotNull($transactionType);
        $this->assertNotEmpty($transactionType->getId());
        $this->assertNotEmpty($transactionType->getUniqueId());
        $this->assertNotEmpty($transactionType->getShortId());
    }

    /**
     * Asserts whether the given transaction was successful.
     *
     * @param AbstractTransactionType $transaction
     *
     * @throws AssertionFailedError
     */
    protected function assertSuccess(AbstractTransactionType $transaction)
    {
        $this->assertTrue($transaction->isSuccess());
        $this->assertFalse($transaction->isPending());
        $this->assertFalse($transaction->isError());
    }

    /**
     * Asserts whether the given transaction was a failure.
     *
     * @param AbstractTransactionType $transaction
     *
     * @throws AssertionFailedError
     */
    protected function assertError(AbstractTransactionType $transaction)
    {
        $this->assertFalse($transaction->isSuccess());
        $this->assertFalse($transaction->isPending());
        $this->assertTrue($transaction->isError());
    }

    /**
     * Asserts whether the given transaction is pending.
     *
     * @param AbstractTransactionType|Recurring $transaction
     *
     * @throws AssertionFailedError
     */
    protected function assertPending($transaction)
    {
        $this->assertFalse($transaction->isSuccess());
        $this->assertTrue($transaction->isPending());
        $this->assertFalse($transaction->isError());
    }

    //</editor-fold>

    //<editor-fold desc="Helpers">

    /**
     * Creates a Basket resource and returns it.
     *
     * @return Basket
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function createBasket(): Basket
    {
        $orderId = $this->generateRandomId();
        $basket = new Basket($orderId, 123.4, 'EUR');
        $basket->setNote('This basket is creatable!');
        $basketItem = (new BasketItem('myItem', 123.4, 123.4, 1))
            ->setBasketItemReferenceId('refId')
            ->setImageUrl('https://hpp-images.s3.amazonaws.com/7/bsk_0_6377B5798E5C55C6BF8B5BECA59529130226E580B050B913EAC3606DA0FF4F68.jpg');
        $basket->addBasketItem($basketItem);
        $this->heidelpay->createBasket($basket);
        return $basket;
    }

    /**
     * Mask a credit card number.
     *
     * @param $number
     * @param string $maskSymbol
     *
     * @return string
     */
    protected function maskNumber($number, $maskSymbol = '*'): string
    {
        return substr($number, 0, 6) . str_repeat($maskSymbol, strlen($number) - 10) . substr($number, -4);
    }

    /**
     * Creates a Card object for tests.
     *
     * @return Card
     *
     * @throws RuntimeException
     */
    protected function createCardObject(): Card
    {
        $card = new Card('5453010000059543', '03/20');
        $card->setCvc('123');
        return $card;
    }

    /**
     * Creates and returns an Authorization object with the API which can be used in test methods.
     *
     * @param float $amount
     *
     * @return Authorization
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function createCardAuthorization($amount = 100.0): Authorization
    {
        $card          = $this->heidelpay->createPaymentType($this->createCardObject());
        $orderId       = microtime(true);
        $authorization = $this->heidelpay->authorize($amount, 'EUR', $card, self::RETURN_URL, null, $orderId, null, null, false);
        return $authorization;
    }

    /**
     * Creates and returns an Authorization object with the API which can be used in test methods.
     *
     * @return Authorization
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function createPaypalAuthorization(): Authorization
    {
        /** @var Paypal $paypal */
        $paypal = $this->heidelpay->createPaymentType(new Paypal());
        $orderId = microtime(true);
        $authorization = $this->heidelpay->authorize(100.0, 'EUR', $paypal, self::RETURN_URL, null, $orderId, null, null, false);
        return $authorization;
    }

    /**
     * Creates and returns a Charge object with the API which can be used in test methods.
     *
     * @param float $amount
     *
     * @return Charge
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function createCharge($amount = 100.0): Charge
    {
        $card = $this->heidelpay->createPaymentType(new SepaDirectDebit('DE89370400440532013000'));
        return $this->heidelpay->charge($amount, 'EUR', $card, self::RETURN_URL);
    }

    /**
     * Creates and returns an order id.
     *
     * @return float
     */
    public function generateRandomId(): float
    {
        return (string)microtime(true);
    }

    //</editor-fold>

    //<editor-fold desc="DataProviders">

    /**
     * Provides valid keys.
     *
     * @return array
     */
    public function validKeysDataProvider(): array
    {
        return [
            'private sandbox key' => ['s-priv-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n'],
            'private production key' => ['p-priv-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n']
        ];
    }

    /**
     * Provides invalid keys.
     *
     * @return array
     */
    public function invalidKeysDataProvider(): array
    {
        return [
            'public sandbox key' => ['s-pub-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n'],
            'public production key' => ['p-pub-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n'],
            'invalid environment' => ['t-priv-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n'],
            'invalid key type' => ['s-xyz-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n'],
            'invalid format 1' => ['spriv-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n'],
            'invalid format 2' => ['2a102ZMq3gV4I3zJ888J7RR6u75oqK3n']
        ];
    }

    //</editor-fold>
}
