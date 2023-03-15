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

namespace MageMe\WebForms\Ui\Component\Result\Info\Form\Modifier;

use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Ui\Component\Form;

class Info extends AbstractModifier
{
    protected $infoBlock;

    public function __construct(
        \MageMe\WebForms\Block\Adminhtml\Result\Info $infoBlock,
        SearchCriteriaBuilder                        $searchCriteriaBuilder,
        ScopeConfigInterface                         $scopeConfig,
        FormRepositoryInterface                      $formRepository,
        ResultRepositoryInterface                    $resultRepository,
        TimezoneInterface                            $timezone,
        RequestInterface                             $request)
    {
        parent::__construct($searchCriteriaBuilder, $scopeConfig, $formRepository, $resultRepository, $timezone, $request);
        $this->infoBlock = $infoBlock;
    }

    public function modifyMeta(array $meta): array
    {
        $result       = $this->getResult();
        $content      = $this->infoBlock->setResult($result)->toHtml();
        $meta['info'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Fieldset::NAME,
                        'label' => __('Information'),
                        'collapsible' => true,
                        'opened' => false,
                    ]
                ]
            ],
            'children' => [
                'general' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'label' => false,
                                'content' => $content,
                                'template' => 'MageMe_WebForms/ui/content',
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $meta;
    }
}
