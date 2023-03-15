<?php
/**
 * MageMe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageMe.com license that is
 * available through the world-wide-web at this URL:
 * https://mageme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to a newer
 * version in the future.
 *
 * Copyright (c) MageMe (https://mageme.com)
 **/

namespace MageMe\WebForms\Ui\Component\Logic\Listing;


use MageMe\WebForms\Model\Field\Type\Select;
use MageMe\WebForms\Ui\Component\Common\Listing\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    /**
     * @inheritdoc
     */
    public function getData(): array
    {
        $data = parent::getData();

        /* Add multiselect flag for logic aggregation */
        $data[Select::IS_MULTISELECT] = (bool)$this->request->getParam(Select::IS_MULTISELECT);
        return $data;
    }
}
