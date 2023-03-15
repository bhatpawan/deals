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

namespace MageMe\WebForms\Config;


use Magento\Framework\Config\DataInterface;

class Config
{
    /**
     * @var DataInterface
     */
    protected $dataStorage;

    /**
     * Config constructor.
     * @param DataInterface $dataStorage
     */
    public function __construct(
        DataInterface $dataStorage
    )
    {
        $this->dataStorage = $dataStorage;
    }

    /**
     * Get logic types
     *
     * @return array
     */
    public function getLogicTypes(): array
    {
        $logicTypes = [];
        foreach ($this->getFieldTypes() as $id => $data) {
            if ($data['logic']) {
                $logicTypes[] = $id;
            }
        }
        return $logicTypes;
    }

    /**
     * Get list of field types
     *
     * @return array
     * @api
     */
    public function getFieldTypes(): array
    {
        return $this->dataStorage->get('field_types');
    }

    /**
     * Get field type attributes by type id
     *
     * @param string $id
     * @return array
     */
    public function getTypeAttributes(string $id): array
    {
        $type = $this->getFieldTypeById($id);
        return $type['attributes'] ?? [];
    }

    /**
     * Get field type by id
     *
     * @param string $id
     * @return array|null
     */
    public function getFieldTypeById(string $id): ?array
    {
        if (isset($this->getFieldTypes()[$id])) {
            return $this->getFieldTypes()[$id];
        }
        return null;
    }

    /**
     * Get field value field by type id
     *
     * @param string $id
     * @return string|null
     */
    public function getTypeValue(string $id): ?string
    {
        $type = $this->getFieldTypeById($id);
        return $type['value'] ?? null;
    }

}
