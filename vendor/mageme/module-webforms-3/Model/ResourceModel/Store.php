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
use MageMe\WebForms\Api\Data\StoreInterface;
use MageMe\WebForms\Setup\Table\StoreTable;
use Magento\Framework\Model\AbstractModel;

/**
 * Store resource model
 *
 */
class Store extends AbstractDb
{
    const DB_TABLE = StoreTable::TABLE_NAME;
    const ID_FIELD = StoreInterface::ID;

    /**
     * @inheritdoc
     */
    protected $serializableFields = [
        StoreInterface::STORE_DATA => [
            self::SERIALIZE_OPTION_SERIALIZED => StoreInterface::STORE_DATA_SERIALIZED,
            self::SERIALIZE_OPTION_DEFAULT_DESERIALIZED => []
        ]
    ];

    /**
     * @param AbstractModel|StoreInterface $object
     */
    protected function updateParents(AbstractModel $object)
    {
        parent::updateParents($object);
        switch ($object->getEntityType()) {
            case Form::ENTITY_TYPE:
            {
                $this->updateFormUpdatedAt($object->getEntityId());
            }
            case Fieldset::ENTITY_TYPE:
            {
                $this->updateFieldsetUpdatedAt($object->getEntityId());
            }
            case Field::ENTITY_TYPE:
            {
                $this->updateFieldUpdatedAt($object->getEntityId());
            }
            case Logic::ENTITY_TYPE:
            {
                $this->updateLogicUpdatedAt($object->getEntityId());
            }
        }
    }

    #region UpdateAt

    /**
     * @param int|string $id
     * @param string|null $date
     */
    private function updateFormUpdatedAt(int $id, ?string $date = null)
    {
        $date = $date ?? date('Y-m-d H:i:s');
        $this->updateUpdatedAt(
            Form::DB_TABLE,
            $date,
            Form::ID_FIELD,
            $id
        );
    }

    /**
     * @param int|string $id
     * @param string|null $date
     */
    private function updateFieldsetUpdatedAt(int $id, ?string $date = null)
    {
        $date         = $date ?? date('Y-m-d H:i:s');
        $select       = $this->getConnection()->select()
            ->from($this->getTable(Fieldset::DB_TABLE), [
                FieldsetInterface::ID,
                FieldsetInterface::FORM_ID
            ])
            ->where(FieldsetInterface::ID . ' = ?', $id);
        $fieldsetData = $this->getConnection()->fetchRow($select);
        $this->updateUpdatedAt(
            Fieldset::DB_TABLE,
            $date,
            Fieldset::ID_FIELD,
            $id
        );
        if (isset($fieldsetData[FieldsetInterface::FORM_ID]))
            $this->updateUpdatedAt(
                Form::DB_TABLE,
                $date,
                Form::ID_FIELD,
                $fieldsetData[FieldsetInterface::FORM_ID]
            );
    }

    /**
     * @param int|string $id
     * @param string|null $date
     */
    private function updateFieldUpdatedAt(int $id, ?string $date = null)
    {
        $date      = $date ?? date('Y-m-d H:i:s');
        $select    = $this->getConnection()->select()
            ->from($this->getTable(Field::DB_TABLE), [
                FieldInterface::ID,
                FieldInterface::FORM_ID,
                FieldInterface::FIELDSET_ID
            ])
            ->where(FieldInterface::ID . ' = ?', $id);
        $fieldData = $this->getConnection()->fetchRow($select);
        $this->updateUpdatedAt(
            Field::DB_TABLE,
            $date,
            Field::ID_FIELD,
            $id
        );
        if (!empty($fieldData[FieldInterface::FIELDSET_ID])) {
            $this->updateUpdatedAt(
                Fieldset::DB_TABLE,
                $date,
                Fieldset::ID_FIELD,
                $fieldData[FieldInterface::FIELDSET_ID]
            );
        }
        if (isset($fieldData[FieldInterface::FORM_ID]))
            $this->updateUpdatedAt(
                Form::DB_TABLE,
                $date,
                Form::ID_FIELD,
                $fieldData[FieldInterface::FORM_ID]
            );
    }

    /**
     * @param int|string $id
     * @param string|null $date
     */
    private function updateLogicUpdatedAt(int $id, ?string $date = null)
    {
        $date      = $date ?? date('Y-m-d H:i:s');
        $select    = $this->getConnection()->select()
            ->from($this->getTable(Logic::DB_TABLE), [
                LogicInterface::ID,
                LogicInterface::FIELD_ID
            ])
            ->where(LogicInterface::ID . ' = ?', $id);
        $logicData = $this->getConnection()->fetchRow($select);
        $this->updateUpdatedAt(
            Logic::DB_TABLE,
            $date,
            Logic::ID_FIELD,
            $id
        );
        if (isset($logicData[LogicInterface::FIELD_ID]))
            $this->updateFieldUpdatedAt($logicData[LogicInterface::FIELD_ID], $date);
    }
    #endregion
}
