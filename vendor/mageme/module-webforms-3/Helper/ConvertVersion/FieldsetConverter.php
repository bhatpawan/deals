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

namespace MageMe\WebForms\Helper\ConvertVersion;


use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Api\FieldsetRepositoryInterface;
use MageMe\WebForms\Model\FieldsetFactory;
use MageMe\WebForms\Setup\Table\FieldsetTable;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class FieldsetConverter
{
    /**#@+
     * V2 constants
     */
    const ID = 'id';
    const WEBFORM_ID = 'webform_id';
    const NAME = 'name';
    const RESULT_DISPLAY = 'result_display';
    const CSS_CLASS = 'css_class';
    const CSS_STYLE = 'css_style';
    const POSITION = 'position';
    const CREATED_TIME = 'created_time';
    const UPDATE_TIME = 'update_time';
    const IS_ACTIVE = 'is_active';
    const WIDTH_LG = 'width_lg';
    const WIDTH_MD = 'width_md';
    const WIDTH_SM = 'width_sm';
    const ROW_LG = 'row_lg';
    const ROW_MD = 'row_md';
    const ROW_SM = 'row_sm';
    /**#@-*/

    const TABLE_FIELDSETS = 'webforms_fieldsets';

    /**
     * @var FieldsetFactory
     */
    private $fieldsetFactory;

    /**
     * @var FieldsetRepositoryInterface
     */
    private $fieldsetRepository;

    /**
     * FieldsetConverter constructor.
     * @param FieldsetRepositoryInterface $fieldsetRepository
     * @param FieldsetFactory $fieldsetFactory
     */
    public function __construct(
        FieldsetRepositoryInterface $fieldsetRepository,
        FieldsetFactory             $fieldsetFactory
    )
    {
        $this->fieldsetFactory    = $fieldsetFactory;
        $this->fieldsetRepository = $fieldsetRepository;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws CouldNotSaveException
     */
    public function convert(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $select     = $connection->select()->from($setup->getTable(self::TABLE_FIELDSETS));
        $query      = $select->query()->fetchAll();
        foreach ($query as $oldData) {
            $connection->insertOnDuplicate($setup->getTable($setup->getTable(FieldsetTable::TABLE_NAME)), [
                FieldsetInterface::ID => $oldData[self::ID],
                FieldsetInterface::FORM_ID => $oldData[self::WEBFORM_ID],
                FieldsetInterface::NAME => $oldData[self::NAME]
            ]);
            $fieldset = $this->fieldsetFactory->create();
            $fieldset->setData($this->convertV2Data($oldData));
            $this->fieldsetRepository->save($fieldset);
        }
    }

    /**
     * @param array $oldData
     * @return array
     */
    public function convertV2Data(array $oldData): array
    {
        return [
            FieldsetInterface::ID => $oldData[self::ID] ?? null,
            FieldsetInterface::FORM_ID => $oldData[self::WEBFORM_ID] ?? null,
            FieldsetInterface::NAME => $oldData[self::NAME] ?? null,
            FieldsetInterface::POSITION => $oldData[self::POSITION] ?? null,
            FieldsetInterface::IS_ACTIVE => $oldData[self::IS_ACTIVE] ?? null,
            FieldsetInterface::CREATED_AT => $oldData[self::CREATED_TIME] ?? null,
            FieldsetInterface::UPDATED_AT => $oldData[self::UPDATE_TIME] ?? null,

            FieldsetInterface::WIDTH_PROPORTION_LG => $oldData[self::WIDTH_LG] ?? null,
            FieldsetInterface::WIDTH_PROPORTION_MD => $oldData[self::WIDTH_MD] ?? null,
            FieldsetInterface::WIDTH_PROPORTION_SM => $oldData[self::WIDTH_SM] ?? null,
            FieldsetInterface::IS_DISPLAYED_IN_NEW_ROW_LG => $oldData[self::ROW_LG] ?? null,
            FieldsetInterface::IS_DISPLAYED_IN_NEW_ROW_MD => $oldData[self::ROW_MD] ?? null,
            FieldsetInterface::IS_DISPLAYED_IN_NEW_ROW_SM => $oldData[self::ROW_SM] ?? null,

            FieldsetInterface::CSS_CLASS => $oldData[self::CSS_CLASS] ?? null,
            FieldsetInterface::CSS_STYLE => $oldData[self::CSS_STYLE] ?? null,

            FieldsetInterface::IS_NAME_DISPLAYED_IN_RESULT => isset($oldData[self::RESULT_DISPLAY]) ? ($oldData[self::RESULT_DISPLAY] == 'on') : null,
        ];
    }

    /**
     * Convert V2 store data
     *
     * @param array $storeData
     * @return array
     */
    public function convertV2StoreData(array $storeData): array
    {
        $newData = [];
        foreach ($this->convertV2Data($storeData) as $key => $value) {
            if (!is_null($value)) {
                $newData[$key] = $value;
            }
        }
        return $newData;
    }
}
