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
use MageMe\WebForms\Api\Data\LogicInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\Ui\FieldUiInterface;
use MageMe\WebForms\Api\Utility\Field\ContactInterface;
use MageMe\WebForms\Api\Utility\Field\FieldBlockInterface;
use MageMe\WebForms\Api\Utility\Field\FieldLogicValueInterface;
use MageMe\WebForms\Mail\AdminNotification;
use MageMe\WebForms\Model\Field\Context;
use Magento\Framework\Exception\LocalizedException;

class SelectContact extends Select implements FieldLogicValueInterface, ContactInterface
{
    /**
     * @var AdminNotification
     */
    protected $adminNotification;

    /**
     * SelectContact constructor.
     * @param AdminNotification $adminNotification
     * @param Context $context
     * @param FieldUiInterface $fieldUi
     * @param FieldBlockInterface $fieldBlock
     */
    public function __construct(
        AdminNotification   $adminNotification,
        Context             $context,
        FieldUiInterface    $fieldUi,
        FieldBlockInterface $fieldBlock
    )
    {
        parent::__construct($context, $fieldUi, $fieldBlock);
        $this->adminNotification = $adminNotification;
    }

    /**
     * @inheritDoc
     */
    public function getValueForSubject($value)
    {
        $contact = $this->getContactArray($value);
        if (!empty($contact["name"])) {
            $value = $contact["name"];
        }
        return parent::getValueForSubject($value);
    }

    /**
     * @inheritDoc
     */
    public function getContactArray(?string $value): array
    {
        preg_match('/(\w.+) <([^<]+?)>/u', (string)$value, $matches);
        if (!empty($matches[1]) && !empty($matches[2])) {
            return ["name" => trim((string)$matches[1]), "email" => trim((string)$matches[2])];
        }
        return ["name" => trim((string)$value), "email" => ""];
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultHtml($value, array $options = [])
    {
        return $this->getValueForResultTemplate(htmlentities((string)$value));
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultTemplate($value, ?int $resultId = null, array $options = [])
    {
        $contact = $this->getContactArray($value);
        return empty($contact["name"]) ? htmlentities((string)$value) : $contact["name"];
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultAfterSave($value, ResultInterface $result)
    {
        $value = parent::getValueForResultAfterSave($value, $result);
        return is_numeric($value) ? $this->getContactValueById($value) : $value;
    }

    /**
     * TODO: comment
     *
     * @param $id
     * @return bool|mixed
     */
    public function getContactValueById($id)
    {
        $options = $this->getOptionsArray();
        if (!empty($options[$id]['value'])) {
            return $options[$id]['value'];
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getLogicFrontendValue(LogicInterface $logic, $inputValue)
    {
        if ($inputValue && !is_numeric($inputValue)) {
            return $logic->getValue();
        }
        $return  = [];
        $options = $this->getOptionsArray();
        foreach ($options as $i => $option) {
            foreach ($logic->getValue() as $trigger) {
                $contact         = $this->getContactArray($option['value']);
                $trigger_contact = $this->getContactArray($trigger);
                if ($contact == $trigger_contact) {
                    $value = $option['value'];
                    if ($option['null']) {
                        $value = '';
                    }
                    if ($contact['email']) {
                        $return[] = $i;
                    } else {
                        $return[] = $value;
                    }
                }
            }
        }
        return $return;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function processNewResult(ResultInterface $result): FieldInterface
    {
        $form        = $this->getForm();
        $logic_rules = $form->getLogic();
        foreach ($result->getData() as $key => $value) {
            if ($key == 'field_' . $this->getId() && strlen((string)$value)) {
                $target_field = [
                    'id' => 'field_' . $this->getId(),
                    'logic_visibility' => $this->getData('logic_visibility')
                ];

                if ($form->getTargetVisibility($target_field, $logic_rules,
                    $result->getData('field'))) {
                    $contactInfo = $this->getContactArray($value);
                    if (strstr((string)$contactInfo['email'], ',')) {
                        $contactEmails = explode(',', (string)$contactInfo['email']);
                        foreach ($contactEmails as $cEmail) {
                            $this->adminNotification->sendEmail($result,
                                ['name' => $contactInfo['name'], 'email' => $cEmail]);
                        }
                    } else {
                        $this->adminNotification->sendEmail($result, $contactInfo);
                    }
                }
            }
        }
        return $this;
    }

    public function convertRawValue($value, array $config = [])
    {
        if (!empty($config['preserveFrontend'])) {
            $contactArray = $this->getContactArray($this->getOptions());
            for ($i = 0; $i < count($contactArray); $i++) {
                if ($this->getContactValueById($i) == $value) {
                    return $i;
                }
            }
        }

        return $value;
    }
}
