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

namespace MageMe\WebForms\Ui\Component\Result\Listing\Column\Field;


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Utility\ExportValueConverterInterface;
use MageMe\WebForms\Ui\Component\Result\Listing\Column\Field;

class Gallery extends Field implements ExportValueConverterInterface
{
    /**
     * @inheritdoc
     */
    public function convertExportValue($data)
    {
        /** @var FieldInterface $field */
        $field = $this->getData('field');
        return $field->getValueForExport($data);
    }
}
