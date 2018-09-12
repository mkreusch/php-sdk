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

namespace heidelpay\NmgPhpSdk\PaymentTypes;

use heidelpay\NmgPhpSdk\Exceptions\IllegalTransactionTypeException;
use heidelpay\NmgPhpSdk\TransactionTypes\Authorization;
use heidelpay\NmgPhpSdk\TransactionTypes\Cancellation;
use heidelpay\NmgPhpSdk\TransactionTypes\Charge;

interface PaymentTypeInterface
{
    /**
     * @param null $amount
     * @param null $currency
     * @return Charge
     */
    public function charge($amount = null, $currency = null): Charge;

    /**
     * @param float $amount
     * @param string $currency
     * @param string $returnUrl
     * @return Authorization
     */
    public function authorize($amount, $currency, $returnUrl): Authorization;

    /**
     * @return Cancellation
     * @throws IllegalTransactionTypeException
     */
    public function cancel(): Cancellation;

    /**
     * @return bool
     */
    public function isChargeable(): bool;

    /**
     * @return bool
     */
    public function isAuthorizable(): bool;

    /**
     * @return bool
     */
    public function isCancelable(): bool;
}