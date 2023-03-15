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


use DOMDocument;
use Exception;

class HtmlHelper
{
    /**
     * @param string $text
     * @param int $max_length
     * @return string
     */
    public function htmlCut(string $text, int $max_length): string
    {
        $tags             = [];
        $result           = "";
        $is_open          = false;
        $grab_open        = false;
        $is_close         = false;
        $in_double_quotes = false;
        $in_single_quotes = false;
        $tag              = "";
        $i                = 0;
        $stripped         = 0;
        $stripped_text    = strip_tags($text);

        while ($i < strlen($text)
            && $stripped < strlen($stripped_text)
            && $stripped < $max_length) {
            $symbol = $text[$i];
            $result .= $symbol;

            switch ($symbol) {
                case '<':
                {
                    $is_open   = true;
                    $grab_open = true;
                    break;
                }
                case '"':
                {
                    if ($in_double_quotes) {
                        $in_double_quotes = false;
                    } else {
                        $in_double_quotes = true;
                    }
                    break;
                }
                case "'":
                {
                    if ($in_single_quotes) {
                        $in_single_quotes = false;
                    } else {
                        $in_single_quotes = true;
                    }
                    break;
                }
                case '/':
                {
                    if ($is_open && !$in_double_quotes && !$in_single_quotes) {
                        $is_close  = true;
                        $is_open   = false;
                        $grab_open = false;
                    }
                    break;
                }
                case ' ':
                {
                    if ($is_open) {
                        $grab_open = false;
                    } else {
                        $stripped++;
                    }
                    break;
                }
                case '>':
                {
                    if ($is_open) {
                        $is_open   = false;
                        $grab_open = false;
                        array_push($tags, $tag);
                        $tag = "";
                    } else {
                        if ($is_close) {
                            $is_close = false;
                            array_pop($tags);
                            $tag = "";
                        }
                    }
                    break;
                }
                default:
                {
                    if ($grab_open || $is_close) {
                        $tag .= $symbol;
                    }
                    if (!$is_open && !$is_close) {
                        $stripped++;
                    }
                }
            }
            $i++;
        }
        while ($tags) {
            $result .= "</" . array_pop($tags) . ">";
        }
        return $result;
    }

    /**
     * Remove danger tags from raw html
     *
     * @param $html
     * @return string
     */
    public function sanitizeHtml($html): string
    {
        if(!$html) return '';

        try {
            $dom = new DOMDocument();
            $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
            $tags_to_remove = ['script', 'style', 'iframe', 'link'];
            foreach ($tags_to_remove as $tag) {
                $element = $dom->getElementsByTagName($tag);
                foreach ($element as $item) {
                    $item->parentNode->removeChild($item);
                }
            }
            foreach ($dom->getElementsByTagname('*') as $element) {
                foreach (iterator_to_array($element->attributes) as $name => $attribute) {
                    if (substr_compare((string)$name, 'on', 0, 2, true) === 0) {
                        $element->removeAttribute($name);
                    }
                }
            }

            return $dom->saveHTML();
        } catch (Exception $exception) {
            return htmlentities((string)$html);
        }
    }
}
