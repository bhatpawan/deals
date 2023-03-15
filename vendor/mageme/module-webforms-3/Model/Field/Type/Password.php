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
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\Ui\FieldUiInterface;
use MageMe\WebForms\Api\Utility\Field\FieldBlockInterface;
use MageMe\WebForms\Controller\Adminhtml\Result\Save;
use MageMe\WebForms\Model\Field\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\CouldNotSaveException;

class Password extends Text
{
    /**
     * Attributes
     */
    const IS_ENCRYPT = 'is_encrypt';
    const IS_COMPLEXITY_ENABLED = 'is_complexity_enabled';
    const MIN_PASSWORD_LENGTH = 'min_password_length';
    const COMPLEXITY_SYMBOLS_COUNT = 'complexity_symbols_count';
    const MATCH_VALUE_FIELD_ID = 'password_match_value_field_id';

    const PASSWORD_PLACEHOLDER = '••••••';

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * Password constructor.
     * @param EncryptorInterface $encryptor
     * @param Context $context
     * @param FieldUiInterface $fieldUi
     * @param FieldBlockInterface $fieldBlock
     */
    public function __construct(
        EncryptorInterface  $encryptor,
        Context             $context,
        FieldUiInterface    $fieldUi,
        FieldBlockInterface $fieldBlock
    )
    {
        parent::__construct($context, $fieldUi, $fieldBlock);
        $this->encryptor = $encryptor;
    }

    #region type attributes
    /**
     * Get encrypt password flag
     *
     * @return bool
     */
    public function getIsEncrypt(): bool
    {
        return (bool)$this->getData(self::IS_ENCRYPT);
    }

    /**
     * Set encrypt password flag
     *
     * @param bool $isEncrypt
     * @return $this
     */
    public function setIsEncrypt(bool $isEncrypt): Password
    {
        return $this->setData(self::IS_ENCRYPT, $isEncrypt);
    }

    /**
     * Get complexity flag
     *
     * @return bool
     */
    public function getIsComplexityEnabled(): bool
    {
        return (bool)$this->getData(self::IS_COMPLEXITY_ENABLED);
    }

    /**
     * Set complexity flag
     *
     * @param bool $isComplexityEnabled
     * @return $this
     */
    public function setIsComplexityEnabled(bool $isComplexityEnabled): Password
    {
        return $this->setData(self::IS_COMPLEXITY_ENABLED, $isComplexityEnabled);
    }

    /**
     * Get minimum password length
     *
     * @return int
     */
    public function getMinPasswordLength(): int
    {
        return (int)$this->getData(self::MIN_PASSWORD_LENGTH);
    }

    /**
     * Set minimum password length
     *
     * @param int $minPasswordLength
     * @return $this
     */
    public function setMinPasswordLength(int $minPasswordLength): Password
    {
        return $this->setData(self::MIN_PASSWORD_LENGTH, $minPasswordLength);
    }

    /**
     * Get complexity symbols count
     *
     * @return int
     */
    public function getComplexitySymbolsCount(): int
    {
        return (int)$this->getData(self::COMPLEXITY_SYMBOLS_COUNT);
    }

    /**
     * Set complexity symbols count
     *
     * @param int $complexitySymbolsCount
     * @return $this
     */
    public function setComplexitySymbolsCount(int $complexitySymbolsCount): Password
    {
        return $this->setData(self::COMPLEXITY_SYMBOLS_COUNT, $complexitySymbolsCount);
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
    public function setMatchValueFieldId(?int $matchValueFieldId): Password
    {
        return $this->setData(self::MATCH_VALUE_FIELD_ID, $matchValueFieldId);
    }
    #endregion

    /**
     * @inheritdoc
     */
    public function getValidation(): array
    {
        $validation = parent::getValidation();
        if ($this->getMinPasswordLength() || $this->getComplexitySymbolsCount()) {
            $validation['rules']['validate-customer-password'] = "'validate-customer-password':true";
        }
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
        if (isset($config[Save::ADMIN_SAVE]) && $config[Save::ADMIN_SAVE]) {
            $this->setIsRequired(false);
        }

        $errors = parent::getPostErrors($postData, $logicVisibility);
        if (!$this->validatePasswordSpaces($postData)) {
            $errors[] = __("The password can't begin or end with a space. Verify the password and try again.");
        }
        if ($this->getMinPasswordLength()) {
            if (!$this->validateMinPasswordLength($postData)) {
                $errors[] = __('The password needs at least %1 characters. Create a new password and try again.', $this->getMinPasswordLength());
            }
        }
        if ($this->getIsComplexityEnabled()) {
            if (!$this->validateComplexity($postData)) {
                $errors[] = __(
                    'Minimum of different classes of characters in password is %1.' .
                    ' Classes of characters: Lower Case, Upper Case, Digits, Special Characters.',
                    $this->getComplexitySymbolsCount()
                );
            }
        }
        if ($this->getMatchValueFieldId()) {
            if (!$this->validateMatchValue($postData)) {
                $errors[] = __('%1 does not match', $this->getName());
            }
        }
        return $errors;
    }

    /**
     * @param array $postData
     * @return bool
     */
    protected function validatePasswordSpaces(array $postData): bool
    {
        $fields = $postData['field'];
        if (empty($fields[$this->getId()])) {
            return true;
        }
        $value = (string)$fields[$this->getId()];
        return strlen($value) == strlen(trim($value));
    }

    /**
     * @param array $postData
     * @return bool
     */
    protected function validateMinPasswordLength(array $postData): bool {
        $fields = $postData['field'];
        if (empty($fields[$this->getId()])) {
            return true;
        }
        $value = (string)$fields[$this->getId()];
        return strlen($value) >= $this->getMinPasswordLength();
    }

    /**
     * @param array $postData
     * @return bool
     */
    protected function validateComplexity(array $postData): bool {
        $fields = $postData['field'];
        if (empty($fields[$this->getId()])) {
            return true;
        }
        $counter = 0;
        $value = (string)$fields[$this->getId()];
        if (preg_match('/[0-9]+/', $value)) {
            $counter++;
        }
        if (preg_match('/[A-Z]+/', $value)) {
            $counter++;
        }
        if (preg_match('/[a-z]+/', $value)) {
            $counter++;
        }
        if (preg_match('/[^a-zA-Z0-9]+/', $value)) {
            $counter++;
        }
        return $counter >= $this->getComplexitySymbolsCount();
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
     * @param array $config
     * @inheritdoc
     */
    public function preparePostData(
        array &$postData,
        array $config = [],
        ResultInterface $result = null,
        bool $isAdmin = false
    ): FieldInterface
    {
        if ($isAdmin && $result && $result->getId()) {
            $postData['field'][$this->getId()] = $result->getFieldArray()[$this->getId()] ?? '';
            return $this;
        }
        if ($this->getIsEncrypt()) {
            $postData['field'][$this->getId()] = $this->encryptor->getHash($postData['field'][$this->getId()], true);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getValueForResultAdminhtml($value, array $options = []): string
    {
        return $this->getValueForResultHtml($value, $options);
    }

    /**
     * @inheritdoc
     */
    public function getValueForResultHtml($value, array $options = []): string
    {
        return self::PASSWORD_PLACEHOLDER;
    }

    /**
     * @param string $value
     * @param array $options
     * @return string
     */
    public function getValueForResultDefaultTemplate(
        string $value,
        array  $options = []
    ): string
    {
        return $this->getValueForResultHtml($value, $options);
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

    /**
     * @inheritdoc
     */
    public function getValueForSubject($value)
    {
        return self::PASSWORD_PLACEHOLDER;
    }
}
