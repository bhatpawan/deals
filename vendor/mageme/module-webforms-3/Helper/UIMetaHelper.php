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

namespace MageMe\WebForms\Helper;


use Magento\Framework\Phrase;

class UIMetaHelper
{
    /**
     * Disable __disableTmpl for component's metadata.
     *
     * Will sanitize full component's metadata as well as metadata of it's child components.
     *
     * @param array $meta
     * @return array
     */
    public function disableSanitizeComponentMetadata(array $meta): array
    {
        if (array_key_exists('arguments', $meta)
            && is_array($meta['arguments'])
            && array_key_exists('data', $meta['arguments'])
            && is_array($meta['arguments']['data'])
            && array_key_exists('config', $meta['arguments']['data'])
            && is_array($meta['arguments']['data']['config'])
        ) {
            $meta['arguments']['data']['config'] = $this->disableSanitize($meta['arguments']['data']['config']);
        }
        if (array_key_exists('children', $meta) && is_array($meta['children'])) {
            $meta['children'] = array_map([$this, 'disableSanitizeComponentMetadata'], $meta['children']);
        }

        return $meta;
    }

    /**
     * Disable __disableTmpl in data from a UI data provider.
     *
     * @param array $data
     * @return array
     */
    public function disableSanitize(array $data): array
    {
        $config    = $this->extractConfig($data);
        $toProcess = [];
        array_walk(
            $data,
            function ($datum, string $key) use (&$config, &$toProcess): void {
                if (is_array($datum)) {
                    //Each array must have it's own __disableTmpl property
                    $toProcess[$key] = $datum;
                } elseif ((
                        !is_bool($config) && !array_key_exists($key, $config)
                    )
                    && (is_string($datum) || $datum instanceof Phrase)
                    && preg_match('/{.+}/', (string)$datum)
                ) {
                    //Templating is not disabled for all properties or for this property specifically
                    //Property is a string that contains template syntax so we are disabling it's rendering
                    $config[$key] = false;
                }
            }
        );
        if ($toProcess) {
            //Processing sub-arrays
            $data = array_replace($data, array_map([$this, 'disableSanitize'], $toProcess));
        }
        if ($config !== []) {
            //Some properties require rendering configuration.
            $data['__disableTmpl'] = $config;
        }

        return $data;
    }

    /**
     * Extract rendering config from given UI data.
     *
     * @param array $data
     * @return bool|array
     */
    private function extractConfig(array $data)
    {
        /** @var array|bool $config */
        $config = [];
        if (array_key_exists('__disableTmpl', $data)) {
            //UI data provider has explicitly provided rendering config.
            $config = $data['__disableTmpl'];
            unset($data['__disableTmpl']);
        }

        return $config;
    }
}