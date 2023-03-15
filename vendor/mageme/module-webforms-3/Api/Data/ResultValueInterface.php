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


interface ResultValueInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'result_value_id';
    const RESULT_ID = 'result_id';
    const FIELD_ID = 'field_id';
    const VALUE = 'value';
    /**#@-*/

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
     * Get resultId
     *
     * @return int|null
     */
    public function getResultId(): ?int;

    /**
     * Set resultId
     *
     * @param int $resultId
     * @return $this
     */
    public function setResultId(int $resultId): ResultValueInterface;

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
    public function setFieldId(int $fieldId): ResultValueInterface;

    /**
     * Get value
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set value
     *
     * @param mixed $value
     * @return $this
     */
    public function setValue($value): ResultValueInterface;
}
