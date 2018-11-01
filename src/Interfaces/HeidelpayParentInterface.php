<?php
/**
 * This interface defines the methods for a parent heidelpay object.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/interfaces
 */
namespace heidelpay\MgwPhpSdk\Interfaces;

use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Resources\AbstractHeidelpayResource;

interface HeidelpayParentInterface
{
    /**
     * Returns the heidelpay root object.
     *
     * @return Heidelpay
     *
     * @throws HeidelpaySdkException
     */
    public function getHeidelpayObject(): Heidelpay;

    /**
     * Returns the url string for this resource.
     *
     * @return string
     */
    public function getUri(): string;

    /**
     * Fetches the Resource if necessary.
     *
     * @param AbstractHeidelpayResource $resource
     *
     * @return AbstractHeidelpayResource
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function getResource(AbstractHeidelpayResource $resource): AbstractHeidelpayResource;
}