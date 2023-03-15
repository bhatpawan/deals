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

namespace MageMe\WebForms\Ui\Component\Fieldset\Form;


use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Api\FieldsetRepositoryInterface;
use MageMe\WebForms\Helper\UIMetaHelper;
use MageMe\WebForms\Model\Fieldset;
use MageMe\WebForms\Model\ResourceModel\Fieldset as FieldsetResource;
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
    protected $xmlReferenceName = 'webforms_fieldset_form';
    /**
     * @var AuthorizationInterface
     */
    protected $authorization;
    /**
     * @var FieldsetRepositoryInterface
     */
    protected $fieldsetRepository;
    /**
     * @var FieldsetResource
     */
    protected $fieldsetResource;

    /**
     * DataProvider constructor.
     * @param FieldsetResource $fieldsetResource
     * @param FieldsetRepositoryInterface $fieldsetRepository
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
        FieldsetResource            $fieldsetResource,
        FieldsetRepositoryInterface $fieldsetRepository,
        AuthorizationInterface      $authorization,
        UIMetaHelper                $uiMetaHelper,
        DataInterfaceFactory        $uiConfigFactory,
        ArrayManager                $arrayManager,
        RequestInterface            $request,
        PoolInterface               $pool,
        string                      $name,
        string                      $primaryFieldName,
        string                      $requestFieldName,
        array                       $meta = [],
        array                       $data = []
    )
    {
        parent::__construct($uiMetaHelper, $uiConfigFactory, $arrayManager, $request, $pool, $name, $primaryFieldName,
            $requestFieldName, $meta, $data);
        $this->authorization      = $authorization;
        $this->fieldsetRepository = $fieldsetRepository;
        $this->fieldsetResource   = $fieldsetResource;
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
        $fieldsetId                    = (int)$this->request->getParam(FieldsetInterface::ID);
        $data                          = $fieldsetId ? $this->getFieldsetData($fieldsetId) : $this->getNewFieldsetData();
        $fieldsetId                    = $fieldsetId ?: '';
        $data                          = $this->applyDataModifiers($data);
        $this->loadedData[$fieldsetId] = $data;
        return $this->loadedData;
    }

    /**
     * @param int $id
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getFieldsetData(int $id): array
    {
        $fieldset              = $this->fieldsetRepository->getById($id);
        $fieldsetData          = $fieldset->getData();
        $fieldsetData['store'] = $this->getScope();
        return $fieldsetData;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    protected function getNewFieldsetData(): array
    {
        $formId   = (int)$this->request->getParam(FieldsetInterface::FORM_ID);
        $isActive = $this->authorization->isAllowed('MageMe_WebForms::manage_forms');
        return [
            FieldsetInterface::IS_ACTIVE => $isActive ? "1" : "0",
            FieldsetInterface::FORM_ID => $formId,
            FieldsetInterface::POSITION => $this->fieldsetResource->getNextPosition($formId),
            FieldsetInterface::IS_NAME_DISPLAYED_IN_RESULT => "1"
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
            $meta[$key] = $this->addStoreViewInfo($node, $this->getCurrentFieldset());
        }
        return $meta;
    }

    /**
     * @return FieldsetInterface|Fieldset|null
     * @throws NoSuchEntityException
     */
    protected function getCurrentFieldset()
    {
        $fieldsetId = (int)$this->request->getParam(FieldsetInterface::ID);
        if (!$fieldsetId) {
            return null;
        }
        return $this->fieldsetRepository->getById($fieldsetId);
    }
}
