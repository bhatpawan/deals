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

namespace MageMe\WebForms\Ui\Component\Field\Listing\MassAction\SubAction;


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FormInterface;

class AjaxUpdateWidthSm extends AbstractAjaxUpdateWidth
{
    protected $controllerUrl = 'webforms/field/ajaxMassWidthSm';
    protected $field = FieldInterface::WIDTH_PROPORTION_SM;
    protected $showedFlag = FormInterface::IS_WIDTH_SM_SHOWED;
}