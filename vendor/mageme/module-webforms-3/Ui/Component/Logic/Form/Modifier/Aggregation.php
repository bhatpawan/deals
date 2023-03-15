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

namespace MageMe\WebForms\Ui\Component\Logic\Form\Modifier;


use MageMe\WebForms\Api\Data\LogicInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\LogicRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\Component\Form;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class Aggregation implements ModifierInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var LogicRepositoryInterface
     */
    protected $logicRepository;
    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;
    /**
     * @var \MageMe\WebForms\Config\Options\Logic\Aggregation
     */
    protected $aggregation;

    /**
     * Aggregation constructor.
     * @param \MageMe\WebForms\Config\Options\Logic\Aggregation $aggregation
     * @param FieldRepositoryInterface $fieldRepository
     * @param LogicRepositoryInterface $logicRepository
     * @param RequestInterface $request
     */
    public function __construct(
        \MageMe\WebForms\Config\Options\Logic\Aggregation $aggregation,
        FieldRepositoryInterface                          $fieldRepository,
        LogicRepositoryInterface                          $logicRepository,
        RequestInterface                                  $request
    )
    {
        $this->request         = $request;
        $this->logicRepository = $logicRepository;
        $this->fieldRepository = $fieldRepository;
        $this->aggregation     = $aggregation;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data): array
    {
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta): array
    {
        $logicId = (int)$this->request->getParam(LogicInterface::ID);
        $fieldId = (int)$this->request->getParam(LogicInterface::FIELD_ID);
        if ($logicId) {
            $logic   = $this->logicRepository->getById($logicId);
            $fieldId = $logic->getFieldId();
        }
        if (!$fieldId) return $meta;
        $field = $this->fieldRepository->getById($fieldId);
        if (!$field->getIsMultiselect()) return $meta;
        $meta['logic_rule']['children'][LogicInterface::AGGREGATION] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'formElement' => Form\Element\Select::NAME,
                        'visible' => 1,
                        'sortOrder' => 50,
                        'label' => __('Logic aggregation'),
                        'additionalInfo' => __('Select one or multiple target elements'),
                        'options' => $this->aggregation->toOptionArray(),
                    ]
                ]
            ]
        ];
        return $meta;
    }
}
