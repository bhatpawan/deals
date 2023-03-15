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


use InvalidArgumentException;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;

abstract class AbstractDb extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const DB_TABLE = '';
    const ID_FIELD = 'id';

    /**
     * Serializable field options
     */
    const SERIALIZE_OPTION_SERIALIZED = 'serialized';
    const SERIALIZE_OPTION_DEFAULT_SERIALIZED = 'defaultSerialized';
    const SERIALIZE_OPTION_DEFAULT_DESERIALIZED = 'defaultDeserialized';

    /**
     * Name of scope for error messages
     *
     * @var string
     */
    protected $messagesScope = 'webforms/session';

    /**
     * Serializable fields declaration
     * Structure: [
     *     {deserializedFieldName} => [
     *         SERIALIZE_OPTION_SERIALIZED => {serializedFieldName},
     *         SERIALIZE_OPTION_DEFAULT_SERIALIZED => {defaultValueForSerialization} #optional,
     *         SERIALIZE_OPTION_DEFAULT_DESERIALIZED => {defaultValueForDeserialization} #optional
     *     ]
     * ]
     *
     * @var array
     */
    protected $serializableFields = [];

    /**
     * Nullable foreign keys
     *
     * @var array
     */
    protected $nullableFK = [];

    /**
     * Set error messages scope
     *
     * @param string $scope
     * @return void
     */
    public function setMessagesScope(string $scope)
    {
        $this->messagesScope = $scope;
    }

    /**
     * @return array
     */
    public function getSerializableFields(): array
    {
        return $this->serializableFields ?? [];
    }

    /**
     * @param array $fields
     */
    public function setSerializableFields(array $fields) {
        $this->serializableFields = $fields;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    protected function _construct()
    {
        if (!static::DB_TABLE) {
            throw new LocalizedException(
                __('(%1) No table initialized for resource', self::class)
            );
        }
        $this->_init(static::DB_TABLE, static::ID_FIELD);
    }

    /**
     * @inheritdoc
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $this->fixEmptyDBKeys($object);
        $this->serializeFieldsToJSON($object);
        return parent::_beforeSave($object);
    }

    /**
     * Fix error with empty keys
     *
     * @param DataObject $object
     */
    protected function fixEmptyDBKeys(DataObject $object)
    {
        $keys   = $this->nullableFK;
        $keys[] = static::ID_FIELD;
        foreach ($keys as $field) {
            $value = $object->getData($field);
            $value = empty($value) ? null : $value;
            $object->setData($field, $value);
        }
    }

    /**
     * @param DataObject $object
     */
    public function serializeFieldsToJSON(DataObject $object)
    {
        foreach ($this->getSerializableFields() as $key => $data) {
            if (!isset($data[self::SERIALIZE_OPTION_SERIALIZED])) {
                throw new InvalidArgumentException(__('Wrong data for serializeFieldToJSON'));
            }
            $defaultValue = $data[self::SERIALIZE_OPTION_DEFAULT_SERIALIZED] ?? null;
            $this->serializeFieldToJSON($object, $key, $data[self::SERIALIZE_OPTION_SERIALIZED], $defaultValue);
        }
    }

    /**
     * @param DataObject $object
     * @param string $deserializedFieldName
     * @param string $serializedFieldName
     * @param mixed $defaultValue
     */
    protected function serializeFieldToJSON(
        DataObject $object,
        string     $deserializedFieldName,
        string     $serializedFieldName,
                   $defaultValue = null
    )
    {
        $value = $object->getData($deserializedFieldName);
        $json  = json_encode($value ?: $defaultValue);
        if ($json === false) {
            throw new InvalidArgumentException("Unable to serialize value. Error: " . json_last_error_msg());
        }
        $object->setData($serializedFieldName, $json);
    }

    /**
     * @inheritdoc
     */
    protected function _afterLoad(AbstractModel $object)
    {
        parent::_afterLoad($object);
        $this->deserializeFieldsFromJSON($object);
        $object->setHasDataChanges(false);
        return $this;
    }

    /**
     * @param DataObject $object
     */
    public function deserializeFieldsFromJSON(DataObject $object)
    {
        foreach ($this->getSerializableFields() as $key => $data) {
            if (!isset($data[self::SERIALIZE_OPTION_SERIALIZED])) {
                throw new InvalidArgumentException(__('Wrong data for deserializeFieldFromJSON'));
            }
            $defaultValue = $data[self::SERIALIZE_OPTION_DEFAULT_DESERIALIZED] ?? null;
            $this->deserializeFieldFromJSON($object, $key, $data[self::SERIALIZE_OPTION_SERIALIZED], $defaultValue);
        }
    }

    /**
     * @param DataObject $object
     * @param string $deserializedFieldName
     * @param string $serializedFieldName
     * @param mixed $defaultValue
     */
    protected function deserializeFieldFromJSON(
        DataObject $object,
        string     $deserializedFieldName,
        string     $serializedFieldName,
                   $defaultValue = null
    )
    {
        $value = json_decode((string)$object->getData($serializedFieldName), true);
        $error = json_last_error();
        if ($error !== JSON_ERROR_NONE) {
            if ($error == JSON_ERROR_SYNTAX) {
                $value = $defaultValue;
            } else {
                throw new InvalidArgumentException("Unable to deserialize value. Error: " . json_last_error_msg());
            }
        }
        $object->setData($deserializedFieldName, $value);
    }

    /**
     * @inheritdoc
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    protected function _afterSave(AbstractModel $object)
    {
        parent::_afterSave($object);
        if ($object->isObjectNew()
            && method_exists($object, 'getCreatedAt')
            && !$object->getCreatedAt()) {
            $date = date('Y-m-d H:i:s');
            $object->setCreatedAt($date);
            $object->setUpdatedAt($date);
        }
        if ($object->hasDataChanges()) {
            $this->updateParents($object);
        }
        return $this;
    }

    /**
     * Update parents after changes
     *
     * @param AbstractModel $object
     */
    protected function updateParents(AbstractModel $object)
    {

    }

    /**
     * @inheritdoc
     */
    protected function _afterDelete(AbstractModel $object)
    {
        parent::_afterDelete($object);
        $this->updateParents($object);
        return $this;
    }

    /**
     * @param string $table
     * @param string $value
     * @param string $indexField
     * @param int|string $id
     */
    protected function updateUpdatedAt(string $table, string $value, string $indexField, int $id)
    {
        $this->getConnection()->update($this->getTable($table), [
            'updated_at' => $value
        ],
            $indexField . ' = ' . $id
        );
    }
}
