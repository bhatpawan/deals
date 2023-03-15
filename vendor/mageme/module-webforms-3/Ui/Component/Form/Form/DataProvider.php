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

namespace MageMe\WebForms\Ui\Component\Form\Form;


use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Helper\UIMetaHelper;
use MageMe\WebForms\Model\Form;
use MageMe\WebForms\Ui\Component\Common\Form\AbstractStoreDataProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Config\DataInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class DataProvider extends AbstractStoreDataProvider
{
    /**
     * @var string
     */
    protected $xmlReferenceName = 'webforms_form_form';
    /**
     * @var AuthorizationInterface
     */
    protected $authorization;
    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * DataProvider constructor.
     * @param FormRepositoryInterface $formRepository
     * @param AuthorizationInterface $authorization
     * @param UIMetaHelper $uiMetaHelper
     * @param DataInterfaceFactory $uiConfigFactory
     * @param ArrayManager $arrayManager
     * @param RequestInterface $request
     * @param PoolInterface $pool
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        FormRepositoryInterface $formRepository,
        AuthorizationInterface  $authorization,
        UIMetaHelper            $uiMetaHelper,
        DataInterfaceFactory    $uiConfigFactory,
        ArrayManager            $arrayManager,
        RequestInterface        $request,
        PoolInterface           $pool,
        string                  $name,
        string                  $primaryFieldName,
        string                  $requestFieldName,
        array                   $meta = [],
        array                   $data = []
    )
    {
        parent::__construct($uiMetaHelper, $uiConfigFactory, $arrayManager, $request, $pool, $name, $primaryFieldName,
            $requestFieldName, $meta, $data);
        $this->authorization  = $authorization;
        $this->formRepository = $formRepository;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getData(): array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $formId                    = (int)$this->request->getParam(FormInterface::ID);
        $data                      = $formId ? $this->getFormData($formId) : $this->getNewFormData();
        $data['isManageDisabled']  = !$this->authorization->isAllowed('MageMe_WebForms::manage_forms');
        $formId                    = $formId ?: '';
        $data['form_id']           = $formId;
        $data['store_id']          = (int)$this->request->getParam(self::PARAM_STORE);
        $data                      = $this->applyDataModifiers($data);
        $this->loadedData[$formId] = $data;
        return $this->loadedData;
    }

    /**
     * @param int $id
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getFormData(int $id): array
    {
        $form              = $this->formRepository->getById($id);
        $formData          = $form->getData();
        $formData['store'] = $this->getScope();
        return $formData;
    }

    /**
     * @return array
     */
    protected function getNewFormData(): array
    {
        $isActive = $this->authorization->isAllowed('MageMe_WebForms::manage_forms');
        return [
            FormInterface::IS_ACTIVE => $isActive ? "1" : "0"
        ];
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function getMeta(): array
    {
        $meta = $this->getFieldsetsMap();
        $meta = $this->applyMetaModifiers($meta);
        foreach ($meta as $key => $node) {
            $meta[$key] = $this->addStoreViewInfo($node, $this->getCurrentForm());
        }
        return $meta;
    }

    /**
     * @return FormInterface|Form|null
     * @throws NoSuchEntityException
     */
    protected function getCurrentForm(): ?FormInterface
    {
        $fieldsetId = (int)$this->request->getParam(FormInterface::ID);
        if (!$fieldsetId) {
            return null;
        }
        return $this->formRepository->getById($fieldsetId);
    }
}
