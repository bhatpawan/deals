<?php

/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Export\Customer;

use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManager;

/**
 * Class Additional
 *
 * @package Firebear\ImportExport\Model\Export\Customer
 */
class Additional
{
    /**
     * @var array
     */
    public $fields = ['store_id'];

    /**
     * @var array
     */
    protected $convFields = [
        'store_id' => 'store_id'
    ];

    protected $store;

    /**
     * @var RequestInterface
     */
    public $request;

    /**
     * Additional constructor.
     * @param StoreManager $store
     * @param RequestInterface $request
     */
    public function __construct(
        StoreManager $store,
        RequestInterface $request
    ) {
        $this->store = $store;
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $option = [];
        $option[] = ['label' => __('Store'), 'value' => 'store_id'];
        if ($this->request->getPostValue()['entity'] == 'customer_finance') {
            $option[] = ['label' => __('Email'), 'value' => 'email'];
            $option[] = ['label' => __('Website'), 'value' => 'code'];
            $option[] = ['label' => __('Finance Website'), 'value' => '_finance_website'];
            $option[] = ['label' => __('Store Credit'), 'value' => 'base_store_credit'];
            $option[] = ['label' => __('Reward Points'), 'value' => 'base_reward_points'];
        }

        return $option;
    }

    public function getAdditionalFields()
    {
        $option = [];
        $stores = [];
        foreach ($this->store->getStores() as $id => $store) {
            $stores[] = ['label' => $store->getName(), 'value' => $id];
        }
        $option[] = ['field' => 'store_id', 'type' => 'select', 'select' => $stores];
        if ($this->request->getPostValue()['entity'] == 'customer_finance') {
            $option[] = ['field' => 'email', 'type' => 'text'];
            $option[] = ['field' => 'code',  'type' => 'text'];
            $option[] = ['field' => '_finance_website', 'type' => 'text'];
            $option[] = ['field' => 'base_store_credit', 'type' => 'int'];
            $option[] = ['field' => 'base_reward_points', 'type' => 'int'];
        }

        return $option;
    }

    /**
     * @param $field
     * @return bool|mixed
     */
    public function convertFields($field)
    {
        if (isset($this->convFields[$field])) {
            return $this->convFields[$field];
        }

        return false;
    }
}
