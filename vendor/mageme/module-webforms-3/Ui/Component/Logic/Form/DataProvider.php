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

namespace MageMe\WebForms\Ui\Component\Logic\Form;


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\LogicInterface;
use MageMe\WebForms\Api\LogicRepositoryInterface;
use MageMe\WebForms\Helper\UIMetaHelper;
use MageMe\WebForms\Ui\Component\Common\Form\AbstractStoreDataProvider;
use Magento\Framework\App\RequestInterface;
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
    protected $xmlReferenceName = 'webforms_logic_form';
    /**
     * @var LogicRepositoryInterface
     */
    protected $logicRepository;

    /**
     * DataProvider constructor.
     * @param LogicRepositoryInterface $logicRepository
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
        LogicRepositoryInterface $logicRepository,
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
        $this->logicRepository = $logicRepository;
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
        $logicId                    = (int)$this->request->getParam(LogicInterface::ID);
        $data                       = $logicId ? $this->getLogicData($logicId) : $this->getNewLogicData();
        $logicId                    = $logicId ?: '';
        $data                       = $this->applyDataModifiers($data);
        $this->loadedData[$logicId] = $data;
        return $this->loadedData;
    }

    /**
     * @param int $id
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getLogicData(int $id): array
    {
        $logic              = $this->logicRepository->getById($id);
        $logicData          = $logic->getData();
        $logicData['store'] = $this->getScope();
        return $logicData;
    }

    /**
     * @return array
     */
    protected function getNewLogicData(): array
    {
        $fieldId = (int)$this->request->getParam(LogicInterface::FIELD_ID);
        return [
            LogicInterface::IS_ACTIVE => "1",
            LogicInterface::FIELD_ID => $fieldId,
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
            $meta[$key] = $this->addStoreViewInfo($node, $this->getCurrentLogic());
        }
        return $meta;
    }

    /**
     * @return LogicInterface|null
     * @throws NoSuchEntityException
     */
    protected function getCurrentLogic(): ?LogicInterface
    {
        $logicId = (int)$this->request->getParam(LogicInterface::ID);
        if (!$logicId) {
            return null;
        }
        return $this->logicRepository->getById($logicId);
    }
}
