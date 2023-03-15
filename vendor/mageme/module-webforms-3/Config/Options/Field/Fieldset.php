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

namespace MageMe\WebForms\Config\Options\Field;


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\FieldsetRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\Store;

class Fieldset implements OptionSourceInterface
{
    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;
    /**
     * @var FieldsetRepositoryInterface
     */
    protected $fieldsetRepository;
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Fieldset constructor.
     * @param FieldRepositoryInterface $fieldRepository
     * @param FieldsetRepositoryInterface $fieldsetRepository
     * @param RequestInterface $request
     */
    public function __construct(
        FieldRepositoryInterface    $fieldRepository,
        FieldsetRepositoryInterface $fieldsetRepository,
        RequestInterface            $request
    )
    {
        $this->request            = $request;
        $this->fieldsetRepository = $fieldsetRepository;
        $this->fieldRepository    = $fieldRepository;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        $options = [];
        $formId  = $this->request->getParam(FormInterface::ID);
        if (!$formId) {
            $fieldId = $this->request->getParam(FieldInterface::ID);
            if ($fieldId) {
                $field  = $this->fieldRepository->getById($fieldId);
                $formId = $field->getFormId();
            }
        }
        if ($formId) {
            $fieldsets = $this->fieldsetRepository->getListByWebformId($formId, $this->getScope())->getItems();

            /** @var \MageMe\WebForms\Model\Fieldset $fieldset */
            foreach ($fieldsets as $fieldset) {
                $options[] = [
                    'label' => $fieldset->getName(),
                    'value' => (int)$fieldset->getId(),
                ];
            }
        }
        return $options;
    }

    /**
     * Get current store view scope.
     *
     * @return mixed
     */
    public function getScope()
    {
        return $this->request->getParam('store', Store::DEFAULT_STORE_ID);
    }
}
