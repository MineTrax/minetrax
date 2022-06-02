<?php


namespace App\Utils\Helpers;


class MinecraftColorUtils
{
    // const REGEX = '/(?:§|&amp;)([0-9a-fklmnor])/i';
    const REGEX = '/(?:§)([0-9a-fklmnor])/i';
    // const REGEX_HEX = '/(?:§|&amp;)(#[0-9a-z]{6})/i';
    const REGEX_HEX = '/(?:§)(#[0-9a-z]{6})/i';
    // const REGEX_ALL = '/(?:§|&amp;)([0-9a-fklmnor]|#[0-9a-z]{6})/i';
    const REGEX_ALL = '/(?:§)([0-9a-fklmnor]|#[0-9a-z]{6})/i';

    const START_TAG_INLINE_STYLED = '<span style="%s">';
    const START_TAG_WITH_CLASS = '<span class="%s">';
    const CLOSE_TAG = '</span>';

    const CSS_COLOR  = 'color: #';
    const EMPTY_TAGS = '/<[^\/>]*>([\s]?)*<\/[^>]*>/';
    const LINE_BREAK = '<br />';

    static private $colors = array(
        '0' => '000000', //Black
        '1' => '0000AA', //Dark Blue
        '2' => '00AA00', //Dark Green
        '3' => '00AAAA', //Dark Aqua
        '4' => 'AA0000', //Dark Red
        '5' => 'AA00AA', //Dark Purple
        '6' => 'FFAA00', //Gold
        '7' => 'AAAAAA', //Gray
        '8' => '555555', //Dark Gray
        '9' => '5555FF', //Blue
        'a' => '55FF55', //Green
        'b' => '55FFFF', //Aqua
        'c' => 'FF5555', //Red
        'd' => 'FF55FF', //Light Purple
        'e' => 'FFFF55', //Yellow
        'f' => 'FFFFFF'  //White
    );

//    static private $colorsLight = array(
//        '0' => '000000', //Black
//        '1' => '0000AA', //Dark Blue
//        '2' => '00AA00', //Dark Green
//        '3' => '00AAAA', //Dark Aqua
//        '4' => 'AA0000', //Dark Red
//        '5' => 'AA00AA', //Dark Purple
//        '6' => 'FFAA00', //Gold
//        '7' => 'AAAAAA', //Gray
//        '8' => '555555', //Dark Gray
//        '9' => '5555FF', //Blue
//        'a' => '55FF55', //Green
//        'b' => '55FFFF', //Aqua
//        'c' => 'FF5555', //Red
//        'd' => 'FF55FF', //Light Purple
//        'e' => 'c7c700', //Yellow
//        'f' => 'FFFFFF'  //White
//    );

    static private $formatting = array(
        'k' => '',                               //Obfuscated
        'l' => 'font-weight: bold;',             //Bold
        'm' => 'text-decoration: line-through;', //Strikethrough
        'n' => 'text-decoration: underline;',    //Underline
        'o' => 'font-style: italic;',            //Italic
        'r' => ''                                //Reset
    );

    static private $css_classnames = array(
        '0' => 'black',
        '1' => 'dark-blue',
        '2' => 'dark-green',
        '3' => 'dark-aqua',
        '4' => 'dark-red',
        '5' => 'dark-purple',
        '6' => 'gold',
        '7' => 'gray',
        '8' => 'dark-gray',
        '9' => 'blue',
        'a' => 'green',
        'b' => 'aqua',
        'c' => 'red',
        'd' => 'light-purple',
        'e' => 'yellow',
        'f' => 'white',
        'k' => 'obfuscated',
        'l' => 'bold',
        'm' => 'line-strikethrough',
        'n' => 'underline',
        'o' => 'italic'
    );

    static private function UFT8Encode($text) {
        //Encode the text in UTF-8, but only if it's not already.
        if (mb_detect_encoding($text) != 'UTF-8')
            $text = utf8_encode($text);

        return $text;
    }

    static public function clean($text) {
        $text = self::UFT8Encode($text);
        $text = htmlspecialchars($text);

        return preg_replace(self::REGEX_ALL, '', $text);
    }

    static public function convertToMOTD($text, $sign = '\u00A7', $hex_colors = false) {
        $text = self::UFT8Encode($text);
        $text = str_replace("&", "&amp;", $text);

        if ($hex_colors) {
            $text = preg_replace_callback(
                self::REGEX_HEX,
                function($matches) use ($sign) {
                    return $sign.strtoupper($matches[1]);
                },
                $text
            );

            $text = preg_replace(self::REGEX, $sign.'${1}', $text);
        }

        else {
            $text = preg_replace(self::REGEX_HEX, '', $text);
            $text = preg_replace(self::REGEX, $sign.'${1}', $text);
        }

        $text = str_replace("\n", '\n', $text);
        $text = str_replace("&amp;", "&", $text);

        return $text;
    }

    static public function convertToHTML($text, $line_break_element = false, $css_classes = false, $css_prefix = 'minecraft-formatted--') {
        $text = self::UFT8Encode($text);
        $text = htmlspecialchars($text);

        preg_match_all(self::REGEX_ALL, $text, $offsets);

        $colors      = $offsets[0]; //This is what we are going to replace with HTML.
        $color_codes = $offsets[1]; //This is the color numbers/characters only.

        //No colors? Just return the text.
        if (empty($colors))
            return $text;

        $open_tags = 0;

        foreach ($colors as $index => $color) {
            $color_code = strtolower($color_codes[$index]);

            $html = '';

            $is_reset = $color_code === 'r';
            $is_color = isset(self::$colors[$color_code]);
            $is_hex = strlen($color_code) === 7; //#RRGGBB

            if ($is_reset || $is_color || $is_hex) {
                // New colors or the reset char: reset all other colors and formatting.
                if ($open_tags != 0) {
                    $html = str_repeat(self::CLOSE_TAG, $open_tags);
                    $open_tags = 0;
                }
            }

            if ($css_classes && !$is_reset) {
                //No reason to give HEX colors a CSS class.
                if ($is_hex) {
                    $html .= sprintf(self::START_TAG_INLINE_STYLED, self::CSS_COLOR.ltrim(strtoupper($color_code), '#'));
                    $open_tags++;
                }

                else {
                    $css_classname = $css_prefix.self::$css_classnames[$color_code];
                    $html .= sprintf(self::START_TAG_WITH_CLASS, $css_classname);
                    $open_tags++;
                }
            }

            else {
                if ($is_color) {
                    $html .= sprintf(self::START_TAG_INLINE_STYLED, self::CSS_COLOR.self::$colors[$color_code]);
                    $open_tags++;
                }

                else if ($is_hex) {
                    $html .= sprintf(self::START_TAG_INLINE_STYLED, self::CSS_COLOR.ltrim(strtoupper($color_code), '#'));
                    $open_tags++;
                }

                //Special case for obfuscated, always add a CSS class for this.
                else if ($color_code === 'k') {
                    $css_classname = $css_prefix.self::$css_classnames[$color_code];
                    $html .= sprintf(self::START_TAG_WITH_CLASS, $css_classname);
                    $open_tags++;
                }

                else if (!$is_reset) {
                    $html .= sprintf(self::START_TAG_INLINE_STYLED, self::$formatting[$color_code]);
                    $open_tags++;
                }
            }

            //Replace the color with the HTML code. We use preg_replace because of the limit parameter.
            $text = preg_replace('/'.$color.'/', $html, $text, 1);
        }

        //Still open tags? Close them!
        if ($open_tags != 0)
            $text = $text.str_repeat(self::CLOSE_TAG, $open_tags);

        //Move newline endings outside elements.
        while (strpos($text, "\n".self::CLOSE_TAG) !== false)
            $text = str_replace("\n".self::CLOSE_TAG, self::CLOSE_TAG."\n", $text);

        //Replace \n with <br />
        if ($line_break_element)
            $text = str_replace(array('\n', "\n"), self::LINE_BREAK, $text);

        //Return the text without empty HTML tags. Only to clean up bad color formatting from the user.
        return preg_replace(self::EMPTY_TAGS, '', $text);
    }
}
