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


use Exception;
use MageMe\WebForms\Api\Ui\FieldUiInterface;
use MageMe\WebForms\Api\Utility\Field\FieldBlockInterface;
use MageMe\WebForms\Helper\HtmlHelper;
use MageMe\WebForms\Model\Field\AbstractField;
use MageMe\WebForms\Model\Field\Context;

class Html extends AbstractField
{
    /**
     * Attributes
     */
    const HTML = 'html';
    /**
     * @var HtmlHelper
     */
    protected $htmlHelper;

    /**
     * Html constructor.
     * @param HtmlHelper $htmlHelper
     * @param Context $context
     * @param FieldUiInterface $fieldUi
     * @param FieldBlockInterface $fieldBlock
     */
    public function __construct(
        HtmlHelper          $htmlHelper,
        Context             $context,
        FieldUiInterface    $fieldUi,
        FieldBlockInterface $fieldBlock
    )
    {
        parent::__construct($context, $fieldUi, $fieldBlock);
        $this->htmlHelper = $htmlHelper;
    }

    /**
     * Always return true cause this type has no label
     *
     * @return bool
     */
    public function getIsLabelHidden(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getFilteredFieldValue()
    {
        $value = trim($this->getHtml());
        if (!empty($value)) {
            $value = $this->filterProvider->getBlockFilter()->filter($value);
        }
        return $value;
    }

    /**
     * Get field html
     *
     * @return string
     */
    public function getHtml(): string
    {
        return (string)$this->getData(self::HTML);
    }

    /**
     * Set field html
     *
     * @param string $html
     * @return $this
     */
    public function setHtml(string $html): Html
    {
        return $this->setData(self::HTML, $html);
    }
    #endregion

    /**
     * @inheritDoc
     */
    public function getValueForResultTemplate($value, ?int $resultId = null, array $options = [])
    {
        return $this->getHtml();
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getValueForResultHtml($value, array $options = [])
    {
        return $this->getHtml();
    }
}
