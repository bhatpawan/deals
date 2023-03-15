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


use MageMe\WebForms\Api\Ui\FieldUiInterface;
use MageMe\WebForms\Api\Utility\Field\FieldBlockInterface;
use MageMe\WebForms\Helper\HtmlHelper;
use MageMe\WebForms\Model\Field\AbstractField;
use MageMe\WebForms\Model\Field\Context;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Framework\DataObject;

class Wysiwyg extends AbstractField
{
    /**
     * @var Config
     */
    protected $wysiwygConfig;
    /**
     * @var HtmlHelper
     */
    protected $htmlHelper;

    /**
     * Wysiwyg constructor.
     * @param HtmlHelper $htmlHelper
     * @param Config $wysiwygConfig
     * @param Context $context
     * @param FieldUiInterface $fieldUi
     * @param FieldBlockInterface $fieldBlock
     */
    public function __construct(
        HtmlHelper          $htmlHelper,
        Config              $wysiwygConfig,
        Context             $context,
        FieldUiInterface    $fieldUi,
        FieldBlockInterface $fieldBlock
    )
    {
        parent::__construct($context, $fieldUi, $fieldBlock);
        $this->wysiwygConfig = $wysiwygConfig;
        $this->htmlHelper    = $htmlHelper;
    }

    /**
     * @inheritDoc
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    public function getValueForResultValueRenderer(DataObject $row): string
    {
        $fieldIndex = 'field_' . $this->getId();
        $value      = (string)$row->getData($fieldIndex);
        if (strlen(strip_tags($value)) <= 200 || mb_substr_count($value, "\n") <= 11) {
            return $value;
        }
        $div_id         = 'x_' . $this->getId() . '_' . $row->getId();
        $preview_div_id = 'preview_x_' . $this->getId() . '_' . $row->getId();
        $onclick        = "$('$preview_div_id').hide(); $('$div_id').style.display='block'; this.style.display='none';  return false;";
        $html           = '<div style="min-width:400px" id="' . $preview_div_id . '">' . $this->htmlHelper->htmlCut($value, 200) . '</div>';
        $html           .= '<div id="' . $div_id . '" style="display:none;min-width:400px">' . $value . '</div>';
        $html           .= '<a onclick="' . $onclick . '" style="text-decoration:none;float:right">[' . __('Read more') . ']</a>';
        return $html;
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultDefaultTemplate(string $value, array $options = []): string
    {
        return $this->getValueForResultHtml($value, $options);
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultHtml($value, array $options = [])
    {
        return $this->htmlHelper->sanitizeHtml($value);
    }
}
