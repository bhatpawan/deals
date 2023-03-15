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

namespace MageMe\WebFormsPageBuilder\Model\Stage\Renderer;


use Exception;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use Magento\PageBuilder\Model\Stage\RendererInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Renders a widget directive for the stage
 *
 * @api
 */
class WidgetDirective implements RendererInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FormRepositoryInterface
     */
    private $formRepository;

    /**
     * @param FormRepositoryInterface $formRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        FormRepositoryInterface $formRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager   = $storeManager;
        $this->formRepository = $formRepository;
    }

    /**
     * @inheritdoc
     */
    public function render(array $params): array
    {
        $result = [
            'content' => null,
            FormInterface::NAME => null,
            'error' => null,
        ];

        if (empty($params['directive'])) {
            return $result;
        }

        try {
            $formId = (int)$params[FormInterface::ID];

            /** @noinspection PhpCastIsUnnecessaryInspection */
            $storeId                     = (int)$this->storeManager->getStore()->getId();
            $form                        = $this->formRepository->getById($formId, $storeId);
            $result[FormInterface::NAME] = $form->getName();
        } catch (Exception $e) {
            $result['error'] = __($e->getMessage());
        }

        return $result;
    }
}
