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

namespace MageMe\WebForms\Ui\Component\Field\Form;


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Config\Config;
use MageMe\WebForms\Helper\UIMetaHelper;
use MageMe\WebForms\Model\Field\AbstractField;
use MageMe\WebForms\Model\ResourceModel\Field as FieldResource;
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
    // Fieldset names
    const INFORMATION_FIELDSET = 'information';
    const DESIGN_FIELDSET = 'design';
    const LOGIC_FIELDSET = 'logic';
    const VALIDATION_FIELDSET = 'validation';
    const FIELDSETS = [
        self::INFORMATION_FIELDSET,
        self::DESIGN_FIELDSET,
        self::LOGIC_FIELDSET,
        self::VALIDATION_FIELDSET,
    ];

    /**
     * @var string
     */
    protected $xmlReferenceName = 'webforms_field_form';
    /**
     * @var AuthorizationInterface
     */
    protected $authorization;
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var FieldResource
     */
    protected $fieldResource;
    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * DataProvider constructor.
     * @param FieldRepositoryInterface $fieldRepository
     * @param FieldResource $fieldResource
     * @param Config $config
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
        FieldRepositoryInterface $fieldRepository,
        FieldResource            $fieldResource,
        Config                   $config,
        AuthorizationInterface   $authorization,
        UIMetaHelper             $uiMetaHelper,
        DataInterfaceFactory     $uiConfigFactory,
        ArrayManager             $arrayManager,
        RequestInterface         $request,
        PoolInterface            $pool,
        string                   $name,
        string                   $primaryFieldName,
        string                   $requestFieldName,
        array                    $meta = [],
        array                    $data = []
    )
    {
        parent::__construct($uiMetaHelper, $uiConfigFactory, $arrayManager, $request, $pool, $name, $primaryFieldName,
            $requestFieldName, $meta, $data);
        $this->authorization   = $authorization;
        $this->config          = $config;
        $this->fieldResource   = $fieldResource;
        $this->fieldRepository = $fieldRepository;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $fieldId                    = (int)$this->request->getParam(FieldInterface::ID);
        $data                       = $fieldId ? $this->getFieldData($fieldId) : $this->getNewFieldData();
        $data['logic_types']        = $this->config->getLogicTypes();
        $fieldId                    = $fieldId ?: '';
        $data                       = $this->applyDataModifiers($data);
        $this->loadedData[$fieldId] = $data;
        return $this->loadedData;
    }

    /**
     * @param int $id
     * @return array|mixed|null
     * @throws NoSuchEntityException
     */
    protected function getFieldData(int $id)
    {
        $field                                 = $this->fieldRepository->getById($id, $this->getScope());
        $fieldData                             = $field->getData();
        $fieldData[FieldInterface::MIN_LENGTH] = $fieldData[FieldInterface::MIN_LENGTH] ?: null;
        $fieldData[FieldInterface::MAX_LENGTH] = $fieldData[FieldInterface::MAX_LENGTH] ?: null;
        $fieldData['store']                    = $this->getScope();
        return $fieldData;
    }

    /**
     * Get default data for new field
     *
     * @return array
     * @throws LocalizedException
     */
    protected function getNewFieldData(): array
    {
        $formId   = (int)$this->request->getParam(FieldInterface::FORM_ID);
        $isActive = $this->authorization->isAllowed('MageMe_WebForms::manage_forms');
        return [
            FieldInterface::IS_ACTIVE => $isActive ? "1" : "0",
            FieldInterface::FORM_ID => $formId,
            FieldInterface::POSITION => $this->fieldResource->getNextPosition($formId)
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
            $meta[$key] = $this->addStoreViewInfo($node, $this->getCurrentField());
        }
        return $meta;
    }

    /**
     * Get current field.
     *
     * @return FieldInterface|AbstractField|null
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function getCurrentField(): ?FieldInterface
    {
        $fieldId = (int)$this->request->getParam(FieldInterface::ID);
        if (!$fieldId) {
            return null;
        }
        return $this->fieldRepository->getById($fieldId, $this->getScope());
    }
}
