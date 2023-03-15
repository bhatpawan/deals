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

use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Api\StoreRepositoryInterface;
use MageMe\WebForms\Setup\Table\FieldsetTable;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Fieldset resource model
 *
 */
class Fieldset extends AbstractResource
{
    const ENTITY_TYPE = 'fieldset';
    const DB_TABLE = FieldsetTable::TABLE_NAME;
    const ID_FIELD = FieldsetInterface::ID;

    /**
     * @var Logic
     */
    protected $logicResource;

    /**
     * Fieldset constructor.
     * @param Logic $logicResource
     * @param StoreRepositoryInterface $storeRepository
     * @param Context $context
     * @param string|null $connectionName
     */
    public function __construct(
        Logic                    $logicResource,
        StoreRepositoryInterface $storeRepository,
        Context                  $context,
        ?string                  $connectionName = null
    )
    {
        parent::__construct($storeRepository, $context, $connectionName);
        $this->logicResource = $logicResource;
    }

    /**
     * Get next fieldset position on form
     *
     * @param int|null $webformId
     * @return int
     * @throws LocalizedException
     */
    public function getNextPosition(?int $webformId): int
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable(), FieldsetInterface::POSITION)
            ->where(FieldsetInterface::FORM_ID . ' = ?', $webformId)
            ->order(FieldsetInterface::POSITION . ' DESC');

        $position = (int)$this->getConnection()->fetchOne($select);
        return $position + 10;
    }

    /**
     * @param AbstractModel|FieldsetInterface $object
     * @return Fieldset
     * @throws AlreadyExistsException
     */
    protected function _afterDelete(AbstractModel $object): Fieldset
    {
        $logic = $object->getForm()->getLogic();
        foreach ($logic as $logicRule) {
            $this->logicResource->save($logicRule);
        }

        return parent::_afterDelete($object);
    }

    /**
     * @param AbstractModel|FieldsetInterface $object
     */
    protected function updateParents(AbstractModel $object)
    {
        parent::updateParents($object);
        $date = date('Y-m-d H:i:s');
        $this->updateUpdatedAt(
            Form::DB_TABLE,
            $date,
            Form::ID_FIELD,
            $object->getFormId()
        );
    }
}
