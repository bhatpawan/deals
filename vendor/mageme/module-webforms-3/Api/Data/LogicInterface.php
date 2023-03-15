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

namespace MageMe\WebForms\Api\Data;


interface LogicInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'logic_id';
    const FIELD_ID = 'field_id';
    const LOGIC_CONDITION = 'logic_condition';
    const ACTION = 'action';
    const AGGREGATION = 'aggregation';
    const IS_ACTIVE = 'is_active';
    const VALUE_SERIALIZED = 'value_serialized';
    const TARGET_SERIALIZED = 'target_serialized';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**#@-*/

    /**#@+
     * Additional constants for keys of data array.
     */
    const TARGET = 'target';
    const VALUE = 'value';
    /**#@-*/

    /**
     * Get target visibility
     *
     * @param mixed $data
     * @param LogicInterface[] $logic_rules
     * @param array $fieldMap
     * @param array $target
     * @return bool
     */
    public function getTargetVisibility($data, array $logic_rules, array $fieldMap, array $target): bool;

    /**
     * Get id
     *
     * @return mixed
     */
    public function getId();

    /**
     * Set id
     *
     * @param mixed $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get fieldId
     *
     * @return int|null
     */
    public function getFieldId(): ?int;

    /**
     * Set fieldId
     *
     * @param int $fieldId
     * @return $this
     */
    public function setFieldId(int $fieldId): LogicInterface;

    /**
     * Get logicCondition
     *
     * @return string
     */
    public function getLogicCondition(): string;

    /**
     * Set logicCondition
     *
     * @param string $logicCondition
     * @return $this
     */
    public function setLogicCondition(string $logicCondition): LogicInterface;

    /**
     * Get action
     *
     * @return string
     */
    public function getAction(): string;

    /**
     * Set action
     *
     * @param string $action
     * @return $this
     */
    public function setAction(string $action): LogicInterface;

    /**
     * Get aggregation
     *
     * @return string
     */
    public function getAggregation(): string;

    /**
     * Set aggregation
     *
     * @param string $aggregation
     * @return $this
     */
    public function setAggregation(string $aggregation): LogicInterface;

    /**
     * Get isActive
     *
     * @return bool
     */
    public function getIsActive(): bool;

    /**
     * Set isActive
     *
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive(bool $isActive): LogicInterface;

    /**
     * Get valueSerialized
     *
     * @return string|null
     */
    public function getValueSerialized(): ?string;

    /**
     * Set valueSerialized
     *
     * @param string|null $valueSerialized
     * @return $this
     */
    public function setValueSerialized(?string $valueSerialized): LogicInterface;

    /**
     * Get targetSerialized
     *
     * @return string|null
     */
    public function getTargetSerialized(): ?string;

    /**
     * Set targetSerialized
     *
     * @param string|null $targetSerialized
     * @return $this
     */
    public function setTargetSerialized(?string $targetSerialized): LogicInterface;

    /**
     * Get createdTime
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set createdTime
     *
     * @param string|null $createdAt
     * @return $this
     */
    public function setCreatedAt(?string $createdAt): LogicInterface;

    /**
     * Get updateTime
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * Set updateTime
     *
     * @param string|null $updatedAt
     * @return $this
     */
    public function setUpdatedAt(?string $updatedAt): LogicInterface;

    /**
     * Get value
     *
     * @return array
     */
    public function getValue(): array;

    /**
     * Set value
     *
     * @param array $value
     * @return $this
     */
    public function setValue(array $value): LogicInterface;

    /**
     * Get target
     *
     * @return array
     */
    public function getTarget(): array;

    /**
     * Set target
     *
     * @param array $target
     * @return $this
     */
    public function setTarget(array $target): LogicInterface;

    /**
     * Check logic rules on field map
     *
     * @param mixed $data
     * @param LogicInterface[] $logic_rules
     * @param array $fieldMap
     * @return bool
     */
    public function ruleCheck($data, array $logic_rules, array $fieldMap): bool;

    /**
     * Get logic value from frontend
     *
     * @param mixed $input_value
     * @return array
     */
    public function getFrontendValue($input_value = false): array;
}
