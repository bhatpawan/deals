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

namespace MageMe\WebForms\Model\Field\Type;


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Ui\FieldUiInterface;
use MageMe\WebForms\Api\Utility\Field\AssignCustomerInterface;
use MageMe\WebForms\Api\Utility\Field\FieldBlockInterface;
use MageMe\WebForms\Model\Field\Context;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Validate_Exception;
use Zend_Validate_Regex;

class Email extends Text implements AssignCustomerInterface
{

    /**
     * Attributes
     */
    const IS_FILLED_BY_CUSTOMER_EMAIL = 'is_filled_by_customer_email';
    const IS_ASSIGNED_CUSTOMER_ID_BY_EMAIL = 'is_assigned_customer_id_by_email';
    const MATCH_VALUE_FIELD_ID = 'email_match_value_field_id';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * Email constructor.
     * @param CustomerRegistry $customerRegistry
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     * @param FieldUiInterface $fieldUi
     * @param FieldBlockInterface $fieldBlock
     */
    public function __construct(
        CustomerRegistry      $customerRegistry,
        StoreManagerInterface $storeManager,
        Context               $context,
        FieldUiInterface      $fieldUi,
        FieldBlockInterface   $fieldBlock
    )
    {
        parent::__construct($context, $fieldUi, $fieldBlock);
        $this->storeManager     = $storeManager;
        $this->customerRegistry = $customerRegistry;
    }

    #region type attributes
    /**
     * Get filled by customer data flag
     *
     * @return bool
     */
    public function getIsFilledByCustomerEmail(): bool
    {
        return (bool)$this->getData(self::IS_FILLED_BY_CUSTOMER_EMAIL);
    }

    /**
     * Set filled by customer data flag
     *
     * @param bool $isFilledByCustomerEmail
     * @return $this
     */
    public function setIsFilledByCustomerEmail(bool $isFilledByCustomerEmail): Email
    {
        return $this->setData(self::IS_FILLED_BY_CUSTOMER_EMAIL, $isFilledByCustomerEmail);
    }

    /**
     * Get assign Customer ID automatically flag
     *
     * @return bool
     */
    public function getIsAssignedCustomerIdByEmail(): bool
    {
        return (bool)$this->getData(self::IS_ASSIGNED_CUSTOMER_ID_BY_EMAIL);
    }

    /**
     * Set assign Customer ID automatically flag
     *
     * @param bool $isAssignedCustomerIdByEmail
     * @return $this
     */
    public function setIsAssignedCustomerIdByEmail(bool $isAssignedCustomerIdByEmail): Email
    {
        return $this->setData(self::IS_ASSIGNED_CUSTOMER_ID_BY_EMAIL, $isAssignedCustomerIdByEmail);
    }

    /**
     * Get match value field id
     *
     * @return int
     */
    public function getMatchValueFieldId(): int
    {
        return (int)$this->getData(self::MATCH_VALUE_FIELD_ID);
    }

    /**
     * Set match value field id
     *
     * @param int|null $matchValueFieldId
     * @return $this
     */
    public function setMatchValueFieldId(?int $matchValueFieldId): Email
    {
        return $this->setData(self::MATCH_VALUE_FIELD_ID, $matchValueFieldId);
    }
    #endregion

    /**
     * @inheritDoc
     */
    public function getValidation(): array
    {
        $validation                            = parent::getValidation();
        $validation['rules']['validate-email'] = "'validate-email':true";
        if ($this->getMatchValueFieldId()) {
            $validation['rules']['validate-match-value'] = "'validate-match-value':true";
        }
        return $validation;
    }

    /**
     * @inheritDoc
     */
    public function getPostErrors(array $postData, bool $logicVisibility, array $config = []): array
    {
        $errors = parent::getPostErrors($postData, $logicVisibility);
        if (!$this->_validatePostEmail($postData)) {
            $errors[] = __('Invalid e-mail address specified.');
        }
        if (!$this->_validatePostEmailStopList($postData)) {
            $errors[] = __('E-mail address is blocked: %1', $postData['field'][$this->getId()]);
        }
        if ($this->getMatchValueFieldId()) {
            if (!$this->validateMatchValue($postData)) {
                $errors[] = __('%1 does not match', $this->getName());
            }
        }
        return $errors;
    }

    /**
     * Check email
     *
     * @param array $postData
     * @return bool
     */
    protected function _validatePostEmail(array $postData): bool
    {
        $fields = $postData['field'];
        if ($this->getIsActive() && !empty($fields[$this->getId()])) {
            if (!filter_var($fields[$this->getId()], FILTER_VALIDATE_EMAIL)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check email stop list
     *
     * @param array $postData
     * @return bool
     */
    protected function _validatePostEmailStopList(array $postData): bool
    {
        $fields = $postData['field'];
        if ($this->getIsActive() && !empty($fields[$this->getId()])) {
            if ($this->isInEmailStoplist($fields[$this->getId()])) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array $postData
     * @return bool
     */
    protected function validateMatchValue(array $postData): bool {
        $fields = $postData['field'];
        if (empty($fields[$this->getId()])) {
            return true;
        }
        if (empty($fields[$this->getMatchValueFieldId()])) {
            return false;
        }
        return $fields[$this->getId()] == $fields[$this->getMatchValueFieldId()];
    }

    /**
     * @param string|null $email
     * @return bool
     */
    protected function isInEmailStoplist(?string $email): bool
    {
        if (!$email) {
            return false;
        }
        $stoplist = preg_split("/[\s\n,;]+/", (string)$this->scopeConfig->getValue('webforms/email/stoplist'));
        $flag     = false;
        foreach ($stoplist as $blocked_email) {
            $pattern = trim($blocked_email);

            // clear global modifier
            if (substr($pattern, 0, 1) == '/' && substr($pattern, -2) == '/g') {
                $pattern = substr($pattern, 0, strlen($pattern) - 1);
            }
            $status = @preg_match($pattern, "Test");
            if ($status !== false) {
                try {
                    $validate = new Zend_Validate_Regex($pattern);
                    if ($validate->isValid($email)) {
                        $flag = true;
                    }
                } catch (Zend_Validate_Exception $e) {
                }
            }
            if ($email == $blocked_email) {
                return true;
            }
        }
        return $flag;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    public function getValueForResultValueRenderer(DataObject $row): string
    {
        $fieldIndex = 'field_' . $this->getId();
        $value      = $row->getData($fieldIndex);
        if (!$value) {
            return '';
        }
        $websiteId = false;
        try {
            $websiteId = $this->storeManager->getStore($row->getStoreId())->getWebsite()->getId();
        } catch (LocalizedException $e) {
        }
        $customer = $this->customerRegistry->retrieveByEmail($value, $websiteId);
        $value    = htmlspecialchars((string)$value);
        if ($customer->getId()) {
            $value .= " [<a href='" . $this->getCustomerUrl($customer->getId()) . "' target='_blank'>" . $customer->getName() . "</a>]";
        }
        return $value;
    }

    /**
     * Get customer URL
     *
     * @param $customerId
     * @return string|null
     */
    protected function getCustomerUrl($customerId): ?string
    {
        return $this->urlBuilder->getUrl('customer/index/edit', ['id' => $customerId, '_current' => false]);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerIdByEmail(string $email, ?int $storeId = null): ?int
    {
        if (!$this->getIsAssignedCustomerIdByEmail()) {
            return null;
        }
        try {
            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
            return (int)$this->customerRegistry->retrieveByEmail($email, $websiteId)->getId();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function isImportPostProcess(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     * @throws CouldNotSaveException
     */
    public function importPostProcess(array $logicMatrix): FieldInterface
    {
        $countryFieldId = isset($logicMatrix['field_' . $this->getMatchValueFieldId()]) ?
            $logicMatrix['field_' . $this->getMatchValueFieldId()] : null;
        $this->setMatchValueFieldId($countryFieldId);
        $this->fieldRepository->save($this);
        return $this;
    }
}
