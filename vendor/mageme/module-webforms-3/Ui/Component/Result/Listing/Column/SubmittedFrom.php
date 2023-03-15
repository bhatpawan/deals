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

namespace MageMe\WebForms\Ui\Component\Result\Listing\Column;

use MageMe\WebForms\Api\Utility\ExportValueConverterInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class SubmittedFrom extends Column implements ExportValueConverterInterface
{
    const SHORT_URL_LENGTH = 40;

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (!empty($item)) {
                    $submittedFrom = $item[$fieldName];
                    $html          = '<div>';
                    if (!empty($submittedFrom['title'])) {
                        $html .= '<div>' . $submittedFrom['title'] . '</div>';
                    }
                    if (!empty($submittedFrom['url'])) {
                        $html .= $this->getUrlHtml($submittedFrom['url']);
                    }
                    $html             .= '</div>';
                    $item[$fieldName] = $html;
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param string $url
     * @return string
     */
    private function getUrlHtml(string $url): string
    {
        $shortUrl = strlen($url) > static::SHORT_URL_LENGTH ? substr($url, 0, static::SHORT_URL_LENGTH) . '...' : $url;
        return '<div><a title="' . $url . '" href="javascript:void(0)" onclick="window.open(\'' . $url . '\',\'_blank\')" >' . $shortUrl . '</a></div>';
    }

    /**
     * @inheritdoc
     */
    public function convertExportValue($data)
    {
        if (empty($data)) {
            return '';
        }
        $title = $data['title'] ?? '';
        $url   = $data['url'] ?? '';
        return $title . ' | ' . $url;
    }
}
