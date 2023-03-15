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

namespace MageMe\WebForms\Model;

use MageMe\WebForms\Api\Data\LogicInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\StoreRepositoryInterface;
use MageMe\WebForms\Api\Utility\Field\FieldLogicValueInterface;
use MageMe\WebForms\Config\Options\Logic\Action;
use MageMe\WebForms\Config\Options\Logic\Aggregation;
use MageMe\WebForms\Config\Options\Logic\Condition;
use Magento\Backend\Model\Auth;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Logic extends AbstractModel implements IdentityInterface, LogicInterface
{
    const VISIBILITY_HIDDEN = 'hidden';
    const VISIBILITY_VISIBLE = 'visible';

    /**
     * Logic cache tag
     */
    const CACHE_TAG = 'webforms_logic';

    /**
     * @var string
     */
    protected $_cacheTag = 'webforms_logic';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'webforms_logic';

    /**
     * @var Auth
     */
    protected $auth;

    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * Logic constructor.
     * @param FieldRepositoryInterface $fieldRepository
     * @param Auth $auth
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreFactory $storeFactory
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        FieldRepositoryInterface $fieldRepository,
        Auth                     $auth,
        StoreRepositoryInterface $storeRepository,
        StoreFactory             $storeFactory,
        Context                  $context,
        Registry                 $registry,
        AbstractResource         $resource = null,
        AbstractDb               $resourceCollection = null,
        array                    $data = []
    )
    {
        parent::__construct($storeRepository, $storeFactory, $context, $registry, $resource, $resourceCollection,
            $data);
        $this->auth            = $auth;
        $this->fieldRepository = $fieldRepository;
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->getId();
    }

    #region DB getters and setters

    /**
     * @inheritDoc
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function setFieldId(int $fieldId): LogicInterface
    {
        return $this->setData(self::FIELD_ID, $fieldId);
    }

    /**
     * @inheritDoc
     */
    public function setLogicCondition(string $logicCondition): LogicInterface
    {
        return $this->setData(self::LOGIC_CONDITION, $logicCondition);
    }

    /**
     * @inheritDoc
     */
    public function setAction(string $action): LogicInterface
    {
        return $this->setData(self::ACTION, $action);
    }

    /**
     * @inheritDoc
     */
    public function setAggregation(string $aggregation): LogicInterface
    {
        return $this->setData(self::AGGREGATION, $aggregation);
    }

    /**
     * @inheritDoc
     */
    public function getValueSerialized(): ?string
    {
        return $this->getData(self::VALUE_SERIALIZED);
    }

    /**
     * @inheritDoc
     */
    public function setValueSerialized(?string $valueSerialized): LogicInterface
    {
        return $this->setData(self::VALUE_SERIALIZED, $valueSerialized);
    }

    /**
     * @inheritDoc
     */
    public function getTargetSerialized(): ?string
    {
        return $this->getData(self::TARGET_SERIALIZED);
    }

    /**
     * @inheritDoc
     */
    public function setTargetSerialized(?string $targetSerialized): LogicInterface
    {
        return $this->setData(self::TARGET_SERIALIZED, $targetSerialized);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(?string $createdAt): LogicInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(?string $updatedAt): LogicInterface
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @inheritDoc
     */
    public function getIsActive(): bool
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    /**
     * @inheritDoc
     */
    public function setIsActive(bool $isActive): LogicInterface
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * @inheritDoc
     */
    public function setTarget(array $target): LogicInterface
    {
        return $this->setData(self::TARGET, $target);
    }

    /**
     * @inheritDoc
     */
    public function setValue(array $value): LogicInterface
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * @param $data
     * @param array $logic_rules
     * @param array $fieldMap
     * @return bool
     */
    public function ruleCheck($data, array $logic_rules, array $fieldMap): bool
    {
        $flag        = false;
        $input       = "";
        $input_value = false;

        if (!empty($data[$this->getFieldId()])) {
            $input = $data[$this->getFieldId()];
        }
        if (!is_array($input)) {
            $input = [$input];
        }

        // get trigger field visibility and set empty value if its not visible
        $trigger_field_id         = $this->getFieldId();
        $trigger_field_visibility = true;
        foreach ($logic_rules as $rule) {
            if (is_array($rule['target']) && in_array('field_' . $trigger_field_id, $rule['target'])) {
                $visibility = self::VISIBILITY_HIDDEN;
                if ($rule['action'] == Action::ACTION_HIDE) {
                    $visibility = self::VISIBILITY_VISIBLE;
                }
                $trigger_field_target = [
                    'id' => 'field_' . $trigger_field_id,
                    'logic_visibility' => $visibility
                ];
                // escape infinite loop
                if (!in_array($trigger_field_target['id'], $rule['target'])) {
                    $trigger_field_visibility = $this->getTargetVisibility($data, $logic_rules, $fieldMap,
                        $trigger_field_target);
                }
            }
        }

        if ($trigger_field_visibility == false) {
            $input = [];
        }

        if (
            $this->getAggregation() == Aggregation::AGGREGATION_ANY ||
            (
                $this->getAggregation() == Aggregation::AGGREGATION_ALL &&
                $this->getLogicCondition() == Condition::CONDITION_NOTEQUAL
            )
        ) {

            if ($this->getLogicCondition() == Condition::CONDITION_EQUAL) {
                foreach ($input as $input_value) {
                    if (in_array($input_value, $this->getFrontendValue($input_value))) {
                        $flag = true;
                    }
                }
            } else {
                $flag = true;
                foreach ($input as $input_value) {
                    if (in_array($input_value, $this->getFrontendValue($input_value))) {
                        $flag = false;
                    }
                }
                if (!count($input)) {
                    $flag = false;
                }
            }
        } else {
            $flag = true;
            foreach ($this->getFrontendValue($input_value) as $trigger_value) {
                if (!in_array($trigger_value, $input)) {
                    $flag = false;
                }
            }
        }

        return $flag;
    }

    /**
     * @inheritDoc
     */
    public function getFieldId(): ?int
    {
        return $this->getData(self::FIELD_ID);
    }

    /**
     * @inheritDoc
     */
    public function getTargetVisibility($data, array $logic_rules, array $fieldMap, array $target): bool
    {
        $isTarget   = false;
        $action     = false;
        $visibility = false;
        foreach ($logic_rules as $logic) {

            foreach ($logic->getTarget() as $t) {
                if ($target["id"] == $t) {
                    $isTarget = true;
                    $action   = $logic->getAction();

                    $flag = $logic->ruleCheck($data, $logic_rules, $fieldMap);
                    if ($flag) {
                        $action     = $logic->getAction();
                        $visibility = true;
                        if ($action == Action::ACTION_HIDE) {
                            $visibility = false;
                        }

                        return $visibility;
                    } else {
                        if ($logic->getLogicCondition() == Condition::CONDITION_NOTEQUAL) {
                            $visibility = false;
                            if ($action == Action::ACTION_HIDE) {
                                $visibility = true;
                            }
                            return $visibility;
                        }
                    }
                }
            }
        }
        if ($target["logic_visibility"] == self::VISIBILITY_VISIBLE) {
            $visibility = true;
        }
        if ($isTarget && $action == Action::ACTION_SHOW) {
            $visibility = false;
        }
        return $visibility;
    }

    #endregion

    /**
     * @inheritDoc
     */
    public function getTarget(): array
    {
        $target = $this->getData(self::TARGET);
        return is_array($target) ? $target : [];
    }

    /**
     * @inheritDoc
     */
    public function getAction(): string
    {
        return $this->getData(self::ACTION) ?? Action::ACTION_SHOW;
    }

    /**
     * @inheritDoc
     */
    public function getLogicCondition(): string
    {
        return $this->getData(self::LOGIC_CONDITION) ?? Condition::CONDITION_EQUAL;
    }

    /**
     * @inheritDoc
     */
    public function getAggregation(): string
    {
        return $this->getData(self::AGGREGATION) ?? Aggregation::AGGREGATION_ANY;
    }

    /**
     * @param bool $input_value
     * @return array
     */
    public function getFrontendValue($input_value = false): array
    {
        if ($this->auth->isLoggedIn()) {
            return $this->getValue();
        }
        try {
            $field = $this->fieldRepository->getById(
                $this->getFieldId(),
                $this->getStoreId()
            );
        } catch (NoSuchEntityException $e) {
            // TODO: log
            return $this->getValue();
        }
        return ($field instanceof FieldLogicValueInterface) ?
            $field->getLogicFrontendValue($this, $input_value) : $this->getValue();
    }

    /**
     * @inheritDoc
     */
    public function getValue(): array
    {
        $value = $this->getData(self::VALUE);
        return is_array($value) ? $value : [];
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Logic::class);
    }
}
