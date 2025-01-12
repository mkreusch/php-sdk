<?php
/**
 * This is the controller for the Paylater Installment example.
 * It is called when the pay button on the index page is clicked.
 *
 * Copyright (C) 2020 - today Unzer E-Com GmbH
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
 * @link  https://docs.unzer.com/
 *
 * @package  UnzerSDK\examples
 */

/** Require the constants of this example */
require_once __DIR__ . '/Constants.php';

/** @noinspection PhpIncludeInspection */
/** Require the composer autoloader file */
require_once __DIR__ . '/../../../../autoload.php';

use UnzerSDK\examples\ExampleDebugHandler;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\Basket;
use UnzerSDK\Resources\CustomerFactory;
use UnzerSDK\Resources\EmbeddedResources\Address;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;
use UnzerSDK\Unzer;

session_start();
session_unset();

$clientMessage = 'Something went wrong. Please try again later.';
$merchantMessage = 'Something went wrong. Please try again later.';

function redirect($url, $merchantMessage = '', $clientMessage = '')
{
    $_SESSION['merchantMessage'] = $merchantMessage;
    $_SESSION['clientMessage'] = $clientMessage;
    header('Location: ' . $url);
    die();
}

// You will need the id of the payment type created in the frontend (index.php)
if (!isset($_POST['paymentTypeId'])) {
    redirect(FAILURE_URL, 'Payment type id is missing!', $clientMessage);
}

if (!isset($_POST['orderAmount'])) {
    redirect(FAILURE_URL, 'No order amount is provided!', $clientMessage);
}

$paymentTypeId = $_POST['paymentTypeId'];
$orderAmount = $_POST['orderAmount'];

// Catch API errors, write the message to your log and show the ClientMessage to the client.
/** @noinspection BadExceptionsProcessingInspection */
try {
    // Create an Unzer object using your private key and register a debug handler if you want to.
    $unzer = new Unzer(UNZER_PAPI_PRIVATE_KEY);
    $unzer->setDebugMode(true)->setDebugHandler(new ExampleDebugHandler());

    // Use the quote or order id from your shop
    $orderId = 'o' . str_replace(['0.', ' '], '', microtime(false));

    /** @var \UnzerSDK\Resources\PaymentTypes\PaylaterInstallment $paymentType */
    $paymentType = $unzer->fetchPaymentType($paymentTypeId);

    // A customer with matching addresses is mandatory for Installment payment type
    $address = (new Address())
        ->setName('Linda Heideich')
        ->setStreet('Vangerowstr. 18')
        ->setCity('Heidelberg')
        ->setZip('69155')
        ->setCountry('DE');
    $customer = CustomerFactory::createCustomer('Linda', 'Heideich')
        ->setBirthDate('2000-02-12')
        ->setBillingAddress($address)
        ->setShippingAddress($address)
        ->setEmail('linda.heideich@test.de');

    // A Basket is mandatory for Paylater Installment payment type
    $basketItem = (new BasketItem())
        ->setAmountPerUnitGross($orderAmount)
        ->setVat(19)
        ->setQuantity(1)
        ->setBasketItemReferenceId('item1')
        ->setTitle('Hat');

    $currency = 'EUR';
    $basket = new Basket($orderId);
    $basket->setTotalValueGross($orderAmount)
        ->addBasketItem($basketItem)
        ->setCurrencyCode($currency);

    // initialize the payment
    $authorize = new \UnzerSDK\Resources\TransactionTypes\Authorization($orderAmount, $currency, RETURN_CONTROLLER_URL);

    $unzer->performAuthorization(
        $authorize,
        $paymentType,
        $customer,
        null,
        $basket
    );

    // You'll need to remember the shortId to show it on the success or failure page
    $_SESSION['PaymentId'] = $authorize->getPaymentId();
    $_SESSION['ShortId'] = $authorize->getShortId();
    $_SESSION['additionalPaymentInformation'] = sprintf("Descriptor: %s", $authorize->getDescriptor());

    // Redirect to the success or failure depending on the state of the transaction
    if ($authorize->isSuccess()) {
        redirect(SUCCESS_URL);
    }

    // Check the result message of the transaction to find out what went wrong.
    $merchantMessage = $authorize->getMessage()->getCustomer();
} catch (UnzerApiException $e) {
    $merchantMessage = $e->getMerchantMessage();
    $clientMessage = $e->getClientMessage();
} catch (RuntimeException $e) {
    $merchantMessage = $e->getMessage();
}
redirect(FAILURE_URL, $merchantMessage, $clientMessage);
