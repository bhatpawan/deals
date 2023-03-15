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

namespace MageMe\WebForms\Controller\Adminhtml;


use Exception;

abstract class AbstractStoreInlineEdit extends AbstractInlineEdit
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $error    = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $items = $this->getRequest()->getParam('items', []);
            if (!count($items)) {
                $messages[] = __('Please correct the data sent.');
                $error      = true;
            } else {
                foreach ($items as $id => $data) {
                    try {
                        $model = $this->repository->getById($id);
                        if (!empty($data['store'])) {
                            $storeData = $this->getStoreData($data['use_default'], $data);
                            $model->saveStoreData($data['store'], $storeData);
                        } else {
                            $model->setData(array_merge($model->getData(), $data));
                            $this->repository->save($model);
                        }
                    } catch (Exception $e) {
                        $messages[] = '[Row ID: ' . $id . '] ' . __($e->getMessage());
                        $error      = true;
                    }
                }
            }
        }

        return $this->jsonFactory->create()->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Get data for store scope
     *
     * @param $storeValues
     * @param $data
     * @return array
     */
    protected function getStoreData($storeValues, $data): array
    {
        $storeData = [];
        if (is_array($storeValues)) {
            $values = array_filter($storeValues, function ($value) {
                return !$value;
            });
            foreach ($values as $key => $value) {
                if (isset($data[$key])) {
                    $storeData[$key] = $data[$key];
                }
            }
        }
        return $storeData;
    }
}