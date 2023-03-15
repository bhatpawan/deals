<?php

namespace MageMe\WebForms\Block\Form\Element\Field\Type;

use MageMe\WebForms\Block\Form\Element\Field\AbstractField;

class GoogleMap extends AbstractField
{
    /**
     * Block's template
     * @var string
     */
    protected $_template = self::TEMPLATE_PATH . 'google_map.phtml';

    /**
     * @inheritDoc
     */
    public function getFieldClass(): string
    {
        return 'mm-map ' . parent::getFieldClass();
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->getField()->getApiKey();
    }

    /**
     * @return int
     */
    public function getZoom(): int
    {
        return $this->getField()->getZoom() ?: 4;
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->getField()->getLat();
    }

    /**
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->getField()->getLng();
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->getField()->getAddress();
    }
}