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

namespace MageMe\WebForms\Ui\Component\Fieldset\Listing\MassAction\SubAction;


use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Api\Data\FormInterface;

class AjaxUpdateWidthLg extends AbstractAjaxUpdateWidth
{
    protected $controllerUrl = 'webforms/fieldset/ajaxMassWidthLg';
    protected $field = FieldsetInterface::WIDTH_PROPORTION_LG;
    protected $showedFlag = FormInterface::IS_WIDTH_LG_SHOWED;
}