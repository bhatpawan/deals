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


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\LogicInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\LogicRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Component\Form;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class Value implements ModifierInterface
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
     * Target constructor.
     * @param FieldRepositoryInterface $fieldRepository
     * @param LogicRepositoryInterface $logicRepository
     * @param RequestInterface $request
     */
    public function __construct(
        FieldRepositoryInterface $fieldRepository,
        LogicRepositoryInterface $logicRepository,
        RequestInterface         $request
    )
    {
        $this->request         = $request;
        $this->logicRepository = $logicRepository;
        $this->fieldRepository = $fieldRepository;
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
     * @throws NoSuchEntityException
     * @throws LocalizedException
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
        $field     = $this->fieldRepository->getById($fieldId);
        $fieldMeta = $field->getFieldUi()->getLogicValueMeta();
        if (empty($fieldMeta)) {
            $fieldMeta = $this->getDefaultMeta($field);
        }
        $meta['logic_rule']['children'][LogicInterface::VALUE] = $fieldMeta;
        return $meta;
    }

    /**
     * @param FieldInterface $field
     * @return array
     */
    protected function getDefaultMeta(FieldInterface $field): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'formElement' => Form\Element\MultiSelect::NAME,
                        'visible' => 1,
                        'sortOrder' => 20,
                        'label' => __('Trigger value(s)'),
                        'additionalInfo' => __('Select one or multiple trigger values.<br>Please, configure for each locale <b>Store View</b>.'),
                        'options' => $field->toOptionArray() ?? [],
                        'validation' => [
                            'required-entry' => true,
                        ]
                    ]
                ]
            ]
        ];
    }
}
