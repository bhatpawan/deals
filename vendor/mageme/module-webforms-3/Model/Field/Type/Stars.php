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


use Magento\Framework\DataObject;

class Stars extends Select
{

    const INIT_STARS = 'init_stars';
    const MAX_STARS = 'max_stars';
    const STAR_WIDTH = 24;

    /**
     * @inheritDoc
     */
    public function getFilteredFieldValue()
    {
        $customer_value = $this->getCustomerValue();
        if ($customer_value) {
            $val = explode('/', (string)$customer_value);
            if (isset($val[0])) {
                return (int)$val[0];
            }
        }
        return $this->getInitStars();
    }

    /**
     * Get initial stars
     *
     * @return int
     */
    public function getInitStars(): int
    {
        $result = (int)$this->getData(self::INIT_STARS) ?: 3;
        return $result > $this->getMaxStars() ? 0 : $result;
    }

    /**
     * Get maximum stars
     *
     * @return int
     */
    public function getMaxStars(): int
    {
        return (int)$this->getData(self::MAX_STARS) ?: 5;
    }

    /**
     * Set initial stars
     *
     * @param int $initStars
     * @return $this
     */
    public function setInitStars(int $initStars): Stars
    {
        return $this->setData(self::INIT_STARS, $initStars);
    }

    /**
     * Set maximum stars
     *
     * @param int $maxStars
     * @return $this
     */
    public function setMaxStars(int $maxStars): Stars
    {
        return $this->setData(self::MAX_STARS, $maxStars);
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultHtml($value, array $options = [])
    {
        return $this->getValueForResultTemplate($value);
    }
    #endregion

    /**
     * @inheritDoc
     */
    public function getValueForResultTemplate($value, ?int $resultId = null, array $options = [])
    {
        return htmlentities((string)$value) . ' / ' . $this->getMaxStars();
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultValueRenderer(DataObject $row): string
    {
        $fieldIndex = 'field_' . $this->getId();
        $value      = (int)$row->getData($fieldIndex);
        if (!is_int($value)) return '';
        $blockWidth = ($this->getMaxStars() * 16) . 'px';
        $width      = round(100 * $value / $this->getMaxStars()) . '%';
        return "<div class='stars' style='width:$blockWidth'><ul class='stars-bar'><li class='stars-value' style='width:$width'></li></ul></div>";
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultDefaultTemplate(string $value, array $options = []): string
    {
        $blockWidth = ($this->getMaxStars() * self::STAR_WIDTH) . 'px';
        $width      = round(100 * intval($value) / $this->getMaxStars()) . '%';
        return "<div class='stars' style='width:$blockWidth'><ul class='stars-bar'><li class='stars-value' style='width:$width'></li></ul></div>";
    }

    /**
     * @return array
     */
    public function getStarsOptions(): array
    {
        $count   = $this->getMaxStars();
        $options = [];
        for ($i = 0; $i <= $count; $i++) {
            $options[$i] = $i;
        }
        return $options;
    }

    /**
     * @inheritdoc
     */
    public function getValueForResultAdminhtml($value, array $options = []): string
    {
        return $this->getValueForResultDefaultTemplate((string)$value,$options);
    }

    /**
     * @inheritdoc
     */
    public function getValueForResultAdminGrid($value, array $options = [])
    {
        return $this->getValueForResultAdminhtml($value, $options);
    }
}
