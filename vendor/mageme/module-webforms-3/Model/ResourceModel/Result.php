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
use MageMe\WebForms\Api\Data\FileDropzoneInterface;
use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\Data\ResultValueInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\FileDropzoneRepositoryInterface;
use MageMe\WebForms\Api\MessageRepositoryInterface;
use MageMe\WebForms\Api\Utility\Field\AssignCustomerInterface;
use MageMe\WebForms\Model\ResourceModel\Field as FieldResource;
use MageMe\WebForms\Model\ResourceModel\ResultValue as ResultValueResource;
use MageMe\WebForms\Setup\Table\ResultTable;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Result resource model
 *
 */
class Result extends AbstractDb
{
    const DB_TABLE = ResultTable::TABLE_NAME;
    const ID_FIELD = ResultInterface::ID;

    /**
     * @inheritdoc
     */
    protected $serializableFields = [
        ResultInterface::SUBMITTED_FROM => [
            self::SERIALIZE_OPTION_SERIALIZED => ResultInterface::SUBMITTED_FROM_SERIALIZED,
            self::SERIALIZE_OPTION_DEFAULT_DESERIALIZED => []
        ]
    ];

    /**
     * @inheritdoc
     */
    protected $nullableFK = [
        ResultInterface::STORE_ID,
        ResultInterface::CUSTOMER_ID
    ];

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * @var MessageRepositoryInterface
     */
    protected $messageRepository;

    /**
     * @var FileDropzoneRepositoryInterface
     */
    protected $fileDropzoneRepository;

    /**
     * Result constructor.
     * @param FileDropzoneRepositoryInterface $fileDropzoneRepository
     * @param MessageRepositoryInterface $messageRepository
     * @param FieldRepositoryInterface $fieldRepository
     * @param ManagerInterface $eventManager
     * @param Context $context
     * @param string|null $connectionName
     */
    public function __construct(
        FileDropzoneRepositoryInterface $fileDropzoneRepository,
        MessageRepositoryInterface      $messageRepository,
        FieldRepositoryInterface        $fieldRepository,
        ManagerInterface                $eventManager,
        Context                         $context,
        ?string                         $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);
        $this->eventManager           = $eventManager;
        $this->fieldRepository        = $fieldRepository;
        $this->messageRepository      = $messageRepository;
        $this->fileDropzoneRepository = $fileDropzoneRepository;
    }

    /**
     * @param int $webformId
     * @param int|null $storeId
     * @return array
     */
    public function getSummaryRatings(int $webformId, ?int $storeId): array
    {
        $connection = $this->getConnection();
        $select     = $connection->select()
            ->from(['results_values' => $this->getTable(ResultValueResource::DB_TABLE)],
                [
                    'sum' => 'SUM(results_values.' . ResultValueInterface::VALUE . ')',
                    'count' => 'COUNT(*)',
                    ResultValueInterface::FIELD_ID
                ])
            ->join(['fields' => $this->getTable(FieldResource::DB_TABLE)],
                'results_values.' . ResultValueInterface::FIELD_ID . ' = fields.' . FieldInterface::ID,
                [])
            ->join(['results' => $this->getTable(self::DB_TABLE)],
                'results_values.' . ResultValueInterface::RESULT_ID . ' = results.' . ResultInterface::ID,
                [])
            ->where('fields.' . FieldInterface::TYPE . ' = "stars"')
            ->where('results.' . ResultInterface::FORM_ID . ' = ' . $webformId)
            ->where('results.' . ResultInterface::STORE_ID . ' = ' . $storeId)
            ->where('results.' . ResultInterface::APPROVED . ' = 1')
            ->group('results_values.' . ResultValueInterface::FIELD_ID);
        return $connection->fetchAll($select);
    }

    /**
     * @param AbstractModel|ResultInterface $object
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if (is_array($object->getData('field')) && count($object->getData('field')) > 0) {
            foreach ($object->getData('field') as $fieldId => $value) {
                $field = $this->fieldRepository->getById($fieldId);
                if (!$object->getCustomerId() &&
                    ($field instanceof AssignCustomerInterface) &&
                    is_string($value)
                ) {
                    $customerId = $field->getCustomerIdByEmail($value, $object->getStoreId());
                    if ($customerId) {
                        $object->setCustomerId($customerId);
                    }
                }
            }
        }

        parent::_beforeSave($object);
    }

    /**
     * @param AbstractModel|ResultInterface $object
     * @return Result
     * @throws NoSuchEntityException
     */
    protected function _afterSave(AbstractModel $object): Result
    {
        $fieldData = $object->getData('field');

        //insert field values
        if (is_array($fieldData) && count($fieldData) > 0) {
            foreach ($fieldData as $fieldId => $value) {
                $field       = $this->fieldRepository->getById($fieldId, $object->getStoreId());
                $value       = $field->getValueForResultAfterSave($value, $object);
                $select      = $this->getConnection()->select()
                    ->from($this->getTable(ResultValueResource::DB_TABLE))
                    ->where(ResultValueInterface::RESULT_ID . ' = ?', $object->getId())
                    ->where(ResultValueInterface::FIELD_ID . ' = ?', $fieldId);
                $resultValue = $this->getConnection()->fetchAll($select);

                if (is_array($value)) {
                    $value = implode("\n", $value);
                }

                if (!empty($resultValue[0])) {

                    $this->getConnection()->update($this->getTable(ResultValueResource::DB_TABLE), [
                        ResultValueInterface::VALUE => $value
                    ],
                        ResultValueInterface::ID . ' = ' . $resultValue[0][ResultValueInterface::ID]
                    );

                } else {
                    $this->getConnection()->insert($this->getTable(ResultValueResource::DB_TABLE), [
                        ResultValueInterface::RESULT_ID => $object->getId(),
                        ResultValueInterface::FIELD_ID => $fieldId,
                        ResultValueInterface::VALUE => $value
                    ]);
                }

                // update object
                $object->setData('field_' . $fieldId, $value);
            }
        }
        $object->setData('field');
        $object->getFieldArray();

        $this->eventManager->dispatch('webforms_result_save', ['result' => $object]);

        return parent::_afterSave($object);
    }

    /**
     * @param AbstractModel|ResultInterface $object
     * @return Result
     */
    protected function _afterLoad(AbstractModel $object): Result
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable(ResultValueResource::DB_TABLE))
            ->where(ResultValueInterface::RESULT_ID . ' = ?', $object->getId());
        $items = $this->getConnection()->fetchAll($select);

        foreach ($items as $item) {
            $fieldId = $item[ResultValueInterface::FIELD_ID];
            $value = $item[ResultValueInterface::VALUE];
            $object->setData('field_' . $fieldId, $value);
        }

        $this->eventManager->dispatch('webforms_result_load', ['result' => $object]);

        return parent::_afterLoad($object);
    }

    /**
     * @param AbstractModel|ResultInterface $object
     * @return Result
     * @throws LocalizedException
     * @throws CouldNotDeleteException
     */
    protected function _beforeDelete(AbstractModel $object): Result
    {
        //clear messages
        /** @var MessageInterface[] $messages */
        $messages = $this->messageRepository->getListByResultId($object->getId())->getItems();
        foreach ($messages as $message) {
            $this->messageRepository->delete($message);
        }

        //delete files
        /** @var FileDropzoneInterface[] $files */
        $files = $this->fileDropzoneRepository->getListByResultId($object->getId())->getItems();
        foreach ($files as $file) {
            $this->fileDropzoneRepository->delete($file);
        }

        $this->eventManager->dispatch('webforms_result_delete', ['result' => $object]);

        return parent::_beforeDelete($object);
    }
}
