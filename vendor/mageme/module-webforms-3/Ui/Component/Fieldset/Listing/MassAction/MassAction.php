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

namespace MageMe\WebForms\Ui\Component\Fieldset\Listing\MassAction;

use MageMe\WebForms\Ui\Component\Common\Listing\MassAction\AbstractMassAction;

class MassAction extends AbstractMassAction
{
    /**
     * @inheritdoc
     */
    protected $actionsAcl = [
        'edit' => 'MageMe_WebForms::save_form',
        'delete' => 'MageMe_WebForms::save_form',
        'duplicate' => 'MageMe_WebForms::save_form',
        'status' => 'MageMe_WebForms::save_form',
        'widthLg' => 'MageMe_WebForms::save_form',
        'widthMd' => 'MageMe_WebForms::save_form',
        'widthSm' => 'MageMe_WebForms::save_form'
    ];
}