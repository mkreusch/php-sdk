<?php
/**
 * This represents the basket resource.
 *
 * Copyright (C) 2018 Heidelpay GmbH
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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/resources
 */
namespace heidelpayPHP\Resources;

use heidelpayPHP\Resources\EmbeddedResources\BasketItem;

class Basket extends AbstractHeidelpayResource
{
    /** @var float $amountTotal */
    protected $amountTotal = 0.0;

    /** @var float $amountTotalDiscount */
    protected $amountTotalDiscount;

    /** @var string $currencyCode */
    protected $currencyCode = 'EUR';

    /** @var string $orderId */
    protected $orderId = '';

    /** @var string $note */
    protected $note;

    /** @var array $basketItems */
    private $basketItems;

    /**
     * Basket constructor.
     *
     * @param float  $amountTotal
     * @param string $currencyCode
     * @param string $orderId
     * @param array  $basketItems
     */
    public function __construct(
        string $orderId = '',
        float $amountTotal = 0.0,
        string $currencyCode = 'EUR',
        array $basketItems = []
    ) {
        $this->amountTotal  = $amountTotal;
        $this->currencyCode = $currencyCode;
        $this->orderId      = $orderId;
        $this->basketItems  = $basketItems;

        parent::__construct();
    }

    //<editor-fold desc="Getters/Setters">

    /**
     * @return float|null
     */
    public function getAmountTotal()
    {
        return $this->amountTotal;
    }

    /**
     * @param float $amountTotal
     *
     * @return Basket
     */
    public function setAmountTotal(float $amountTotal): Basket
    {
        $this->amountTotal = $amountTotal;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getAmountTotalDiscount()
    {
        return $this->amountTotalDiscount;
    }

    /**
     * @param float|null $amountTotalDiscount
     *
     * @return Basket
     */
    public function setAmountTotalDiscount($amountTotalDiscount): Basket
    {
        $this->amountTotalDiscount = $amountTotalDiscount;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    /**
     * @param string $currencyCode
     *
     * @return Basket
     */
    public function setCurrencyCode(string $currencyCode): Basket
    {
        $this->currencyCode = $currencyCode;
        return $this;
    }

    /**
     * @return int
     */
    public function getItemCount(): int
    {
        return count($this->basketItems);
    }

    /**
     * @return string|null
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param string|null $note
     *
     * @return Basket
     */
    public function setNote($note): Basket
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     *
     * @return Basket
     */
    public function setOrderId(string $orderId): Basket
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * @return array
     */
    public function getBasketItems(): array
    {
        return $this->basketItems;
    }

    /**
     * @param array $basketItems
     *
     * @return Basket
     */
    public function setBasketItems(array $basketItems): Basket
    {
        $this->basketItems = $basketItems;
        return $this;
    }

    /**
     * @param BasketItem $basketItem
     *
     * @return Basket
     */
    public function addBasketItem(BasketItem $basketItem): Basket
    {
        $this->basketItems[] = $basketItem;
        return $this;
    }

    /**
     * @param int $index
     *
     * @return BasketItem|null
     */
    public function getBasketItemByIndex($index)
    {
        return $this->basketItems[$index] ?? null;
    }

    //</editor-fold>

    //<editor-fold desc="Overridable Methods">

    /**
     * Add the dynamically set meta data.
     * {@inheritDoc}
     */
    public function expose(): array
    {
        $basketItemArrays = [[]];

        /** @var BasketItem $basketItem */
        foreach ($this->getBasketItems() as $basketItem) {
            $basketItemArrays[] = $basketItem->expose();
        }

        $returnArray = parent::expose();
        $returnArray['basketItems'] = array_merge(...$basketItemArrays);

        return $returnArray;
    }

    /**
     * {@inheritDoc}
     */
    protected function getResourcePath(): string
    {
        return 'baskets';
    }
    
    //</editor-fold>
}
