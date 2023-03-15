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
use MageMe\WebForms\Api\FileDropzoneRepositoryInterface;
use MageMe\WebForms\Api\FileGalleryRepositoryInterface;
use MageMe\WebForms\Api\StoreRepositoryInterface;
use MageMe\WebForms\Setup\Table\FieldTable;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Field resource model
 *
 */
class Field extends AbstractResource
{
    const ENTITY_TYPE = 'field';
    const DB_TABLE = FieldTable::TABLE_NAME;
    const ID_FIELD = FieldInterface::ID;

    /**
     * @inheritdoc
     */
    protected $nullableFK = [
        FieldInterface::FIELDSET_ID
    ];

    /**
     * @var FileDropzoneRepositoryInterface
     */
    protected $fileDropzoneRepository;

    /**
     * @var FileGalleryRepositoryInterface
     */
    protected $fileGalleryRepository;

    /**
     * @var Logic
     */
    protected $logicResource;

    /**
     * Field constructor.
     * @param Logic $logicResource
     * @param FileGalleryRepositoryInterface $fileGalleryRepository
     * @param FileDropzoneRepositoryInterface $fileDropzoneRepository
     * @param StoreRepositoryInterface $storeRepository
     * @param Context $context
     * @param string|null $connectionName
     */
    public function __construct(
        Logic                           $logicResource,
        FileGalleryRepositoryInterface  $fileGalleryRepository,
        FileDropzoneRepositoryInterface $fileDropzoneRepository,
        StoreRepositoryInterface        $storeRepository,
        Context                         $context,
        ?string                         $connectionName = null
    )
    {
        parent::__construct($storeRepository, $context, $connectionName);
        $this->fileDropzoneRepository = $fileDropzoneRepository;
        $this->fileGalleryRepository  = $fileGalleryRepository;
        $this->logicResource          = $logicResource;
    }

    /**
     * Get next field position on form
     *
     * @param int|null $webformId
     * @return int
     * @throws LocalizedException
     */
    public function getNextPosition(?int $webformId): int
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable(), FieldInterface::POSITION)
            ->where(FieldInterface::FORM_ID . ' = ?', $webformId)
            ->order(FieldInterface::POSITION . ' DESC');

        $position = (int)$this->getConnection()->fetchOne($select);
        return $position + 10;
    }

    /**
     * Get field type by id
     *
     * @param int $id
     * @return string
     * @throws LocalizedException
     */
    public function getType(int $id): string
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable(), FieldInterface::TYPE)
            ->where(FieldInterface::ID . ' = ?', $id);
        return $this->getConnection()->fetchOne($select);
    }

    /**
     * @param DataObject|FieldInterface $object
     */
    public function serializeFieldsToJSON(DataObject $object)
    {
        $object->setTypeAttributesSerialized($object->getTypeAttributesAsJSON());
        parent::serializeFieldsToJSON($object);
    }

    /**
     * @param DataObject|FieldInterface $object
     */
    public function deserializeFieldsFromJSON(DataObject $object)
    {
        $object->loadTypeAttributesFormJSON($object->getTypeAttributesSerialized());
        parent::deserializeFieldsFromJSON($object);
    }

    /**
     * @param AbstractModel|FieldInterface $object
     * @return $this|AbstractResource|AbstractDb
     * @throws LocalizedException
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    protected function _afterLoad(AbstractModel $object)
    {
        parent::_afterLoad($object);

        $storeData = $object->getStoreData();
        if (!empty($storeData['value']) && is_array($storeData['value'])) {
            foreach ($storeData['value'] as $key => $value) {
                $storeData['value_' . $key] = $value;
            }
        }
        $object->setStoreData($storeData);

        return $this;
    }

    /**
     * @param AbstractModel|FieldInterface $object
     * @return AbstractResource|AbstractDb
     * @throws LocalizedException
     * @throws CouldNotDeleteException
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        //delete files
        $files = $this->fileDropzoneRepository->getListByFieldId($object->getId())->getItems();
        foreach ($files as $file) {
            $this->fileDropzoneRepository->delete($file);
        }

        // delete gallery files
        $files = $this->fileGalleryRepository->getListByFieldId($object->getId())->getItems();
        foreach ($files as $file) {
            $this->fileGalleryRepository->delete($file);
        }

        return parent::_beforeDelete($object);
    }

    /**
     * @param AbstractModel|FieldInterface $object
     * @return Field
     * @throws AlreadyExistsException|LocalizedException
     */
    protected function _afterDelete(AbstractModel $object): Field
    {
        $logic = $object->getForm()->getLogic();
        foreach ($logic as $logicRule) {
            $this->logicResource->save($logicRule);
        }

        return parent::_afterDelete($object);
    }

    /**
     * @param AbstractModel|FieldInterface $object
     */
    protected function updateParents(AbstractModel $object)
    {
        parent::updateParents($object);
        $date = date('Y-m-d H:i:s');
        if ($object->getFieldsetId()) {
            $this->updateUpdatedAt(
                Fieldset::DB_TABLE,
                $date,
                Fieldset::ID_FIELD,
                $object->getFieldsetId()
            );
        }
        $this->updateUpdatedAt(
            Form::DB_TABLE,
            $date,
            Form::ID_FIELD,
            $object->getFormId()
        );
    }

}
