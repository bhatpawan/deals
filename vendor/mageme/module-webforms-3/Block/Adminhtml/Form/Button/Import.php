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

namespace MageMe\WebForms\Block\Adminhtml\Form\Button;


use MageMe\WebForms\Block\Adminhtml\Common\Button\AclGeneric;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;

class Import extends AclGeneric
{
    /**
     *
     */
    const ADMIN_RESOURCE = 'MageMe_WebForms::add_form';

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @param FormKey $formKey
     * @param AuthorizationInterface $authorization
     * @param RequestInterface $request
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        FormKey                $formKey,
        AuthorizationInterface $authorization,
        RequestInterface       $request,
        Registry               $registry,
        Context                $context
    )
    {
        parent::__construct($authorization, $request, $registry, $context);
        $this->formKey = $formKey;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function getButtonData(): array
    {
        if (!$this->isAllowed()) {
            return [];
        }
        $import_url  = $this->getUrl('webforms/form/import');
        $import_form = '
		<form action="' . $import_url . '" style="display:none" method="post" enctype="multipart/form-data">
		    <input name="form_key" type="hidden" value="' . $this->getFormKey() . '" />
		    <input type="file" id="import_form" name="import_form" accept="application/json" onchange="this.form.submit()"/>
        </form>';

        return [
            'before_html' => $import_form,
            'label' => __('Import Form'),
            'on_click' => "javascript: document.getElementById('import_form').click()",
            'class' => 'action-secondary',
            'sort_order' => 10
        ];
    }

    /**
     * Retrieve Session Form Key
     *
     * @return string
     * @throws LocalizedException
     */
    public function getFormKey(): string
    {
        return $this->formKey->getFormKey();
    }
}
