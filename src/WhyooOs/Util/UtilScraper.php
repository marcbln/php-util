<?php
/**
 * 09/2017
 */


namespace WhyooOs\Util;

include( __DIR__ . '/../HelperClasses/simple_html_dom.php');

class UtilScraper
{

    public static $dom;

    /**
     * 09/2017 helper
     *
     * @param $html
     * @return mixed
     */
    private static function br2nl($html)
    {
        return preg_replace('#<br\s*/?>#i', "\n", $html);
    }



    /**
     * extract single information from loaded dom
     *
     * @param $expression
     * @return string
     */
    public static function extractStringFromDom($expression)
    {
        $text = self::$dom->find($expression, 0)->innertext;

        return self::rectifyScrapedText($text);
    }

    /**
     * @param $html
     */
    public static function loadDom($html)
    {
        self::$dom = str_get_html($html);
    }

    /**
     * @param string $text
     * @return string
     */
    public static function rectifyScrapedText($text)
    {
        if( !is_string($text)) {
            return $text;
        }
        $text = html_entity_decode($text, ENT_QUOTES);
        $text = preg_replace('#\s+#', ' ', $text);
        $text = self::br2nl($text);
        $text = strip_tags($text);

        return trim($text);
    }


}
