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

namespace MageMe\WebForms\Block\Adminhtml\Field\Edit\Button;


/**
 * Class Generic
 * @package MageMe\WebForms\Block\Adminhtml\Field\Edit\Button
 */
abstract class Generic extends \MageMe\WebForms\Block\Adminhtml\Common\Button\Generic
{

    /**
     * Get store scope id
     *
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->request->getParam('store');
    }
}
