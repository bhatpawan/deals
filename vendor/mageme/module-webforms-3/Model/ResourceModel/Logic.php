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

namespace MageMe\WebForms\Model\ResourceModel;

use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Api\Data\LogicInterface;
use MageMe\WebForms\Model\ResourceModel\Field as FieldResource;
use MageMe\WebForms\Model\ResourceModel\Fieldset as FieldsetResource;
use MageMe\WebForms\Setup\Table\LogicTable;
use Magento\Framework\Model\AbstractModel;

/**
 * Logic resource model
 *
 */
class Logic extends AbstractResource
{
    const ENTITY_TYPE = 'logic';
    const DB_TABLE = LogicTable::TABLE_NAME;
    const ID_FIELD = LogicInterface::ID;

    /**
     * @inheritdoc
     */
    protected $serializableFields = [
        LogicInterface::VALUE => [
            self::SERIALIZE_OPTION_SERIALIZED => LogicInterface::VALUE_SERIALIZED,
            self::SERIALIZE_OPTION_DEFAULT_DESERIALIZED => []
        ],
        LogicInterface::TARGET => [
            self::SERIALIZE_OPTION_SERIALIZED => LogicInterface::TARGET_SERIALIZED,
            self::SERIALIZE_OPTION_DEFAULT_DESERIALIZED => []
        ]
    ];

    /**
     * @param AbstractModel|LogicInterface $object
     * @return Logic
     */
    protected function _beforeSave(AbstractModel $object): Logic
    {
        $object->setTarget(array_filter($object->getTarget(), function ($value) {
            if (strstr((string)$value, 'field_')) {
                $fieldId = (int)str_replace('field_', '', (string)$value);
                return $this->fieldExists($fieldId);
            }
            if (strstr((string)$value, 'fieldset_')) {
                $fieldsetId = (int)str_replace('fieldset_', '', (string)$value);
                return $this->fieldsetExists($fieldsetId);
            }
            if ($value == 'submit') {
                return true;
            }
            return false;
        }));

        return parent::_beforeSave($object);
    }

    /**
     * Check field exists
     *
     * @param int $fieldId
     * @return bool
     */
    protected function fieldExists(int $fieldId): bool
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable(FieldResource::DB_TABLE), [FieldInterface::ID])
            ->where(FieldInterface::ID . ' = ?', $fieldId);
        return (bool)$this->getConnection()->fetchOne($select);
    }

    /**
     * Check fieldset exists
     *
     * @param int $fieldsetId
     * @return bool
     */
    protected function fieldsetExists(int $fieldsetId): bool
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable(FieldsetResource::DB_TABLE), [FieldsetInterface::ID])
            ->where(FieldsetInterface::ID . ' = ?', $fieldsetId);
        return (bool)$this->getConnection()->fetchOne($select);
    }

    /**
     * @param AbstractModel|LogicInterface $object
     */
    protected function updateParents(AbstractModel $object)
    {
        parent::updateParents($object);
        $fieldId   = $object->getFieldId();
        $select    = $this->getConnection()->select()
            ->from($this->getTable(FieldResource::DB_TABLE), [
                FieldInterface::ID,
                FieldInterface::FORM_ID,
                FieldInterface::FIELDSET_ID
            ])
            ->where(FieldInterface::ID . ' = ?', $fieldId);
        $fieldData = $this->getConnection()->fetchRow($select);
        $date      = date('Y-m-d H:i:s');
        $this->updateUpdatedAt(
            Field::DB_TABLE,
            $date,
            Field::ID_FIELD,
            $fieldId
        );
        if (!empty($fieldData[FieldInterface::FIELDSET_ID])) {
            $this->updateUpdatedAt(
                Fieldset::DB_TABLE,
                $date,
                Fieldset::ID_FIELD,
                $fieldData[FieldInterface::FIELDSET_ID]
            );
        }
        $this->updateUpdatedAt(
            Form::DB_TABLE,
            $date,
            Form::ID_FIELD,
            $fieldData[FieldInterface::FORM_ID]
        );
    }
}
