<?php
declare(strict_types=1);

namespace {

    if (!function_exists('mb_str_starts_with')) {

        function mb_str_starts_with (string $haystack, string $needle): bool
        {
            return $needle === '' || mb_strpos($haystack, $needle) === 0;
        }
    }

    if (!function_exists('mb_str_contains')) {

        function mb_str_contains (string $haystack, string $needle): bool
        {
            return $needle === '' || mb_strpos($haystack, $needle) !== false;
        }
    }

    if (!function_exists('mb_str_ends_with')) {

        function mb_str_ends_with (string $haystack, string $needle): bool
        {
            return $needle === '' || mb_substr($haystack, -mb_strlen($needle)) === $needle;
        }
    }
}

namespace SlimMvcTools\Functions\Str {

    /**
     * Returns "foo-bar-baz" as "fooBarBaz".
     * 
     * @param string $str The dashed word.
     * 
     * @return string The word in camel-caps.
     * 
     * This code originally from the Solar_Inflect class in the SolarPHP framework.
     */
    function dashesToCamel(string $str): string
    {
        $str = ucwords(str_replace('-', ' ', $str));
        $str = str_replace(' ', '', $str);
        $str[0] = strtolower($str[0]);

        return $str;
    }

    /**
     * Returns "foo-bar-baz" as "FooBarBaz".
     * 
     * @param string $str The dashed word.
     * 
     * @return string The word in studly-caps.
     * 
     * This code originally from the Solar_Inflect class in the SolarPHP framework.
     */
    function dashesToStudly(string $str): string
    {
        $str = dashesToCamel($str);
        $str[0] = strtoupper($str[0]);

        return $str;
    }

    /**
     * Returns "foo_bar_baz" as "fooBarBaz".
     * 
     * @param string $str The underscore word.
     * 
     * @return string The word in camel-caps.
     * 
     * This code originally from the Solar_Inflect class in the SolarPHP framework.
     */
    function underToCamel(string $str): string
    {
        $str = ucwords(str_replace('_', ' ', $str));
        $str = str_replace(' ', '', $str);
        $str[0] = strtolower($str[0]);

        return $str;
    }

    /**
     * Returns "foo_bar_baz" as "FooBarBaz".
     * 
     * @param string $str The underscore word.
     * 
     * @return string The word in studly-caps.
     * 
     * This code originally from the Solar_Inflect class in the SolarPHP framework.
     */
    function underToStudly(string $str): string
    {
        $str = underToCamel($str);
        $str[0] = strtoupper($str[0]);

        return $str;
    }

    /**
     * Returns any string, converted to using dashes with only lowercase alphanumerics.
     * 
     * @param string $str The string to convert.
     * 
     * @return string The converted string.
     * 
     * This code originally from the Solar_Inflect class in the SolarPHP framework.
     */
    function toDashes(string $str): string
    {
        $original = $str;
        $str = preg_replace('/[^a-z0-9 _-]/i', '', $str);
        $str = camelToDashes(is_string($str) ? $str : $original);
        $result = preg_replace('/[ _-]+/', '-', $str);

        return is_string($result) ? $result : $str;
    }

    /**
     * Returns "camelCapsWord" and "CamelCapsWord" as "Camel_Caps_Word".
     * 
     * @param string $str The camel-caps word.
     * 
     * @return string The word with underscores in place of camel caps.
     * 
     * This code originally from the Solar_Inflect class in the SolarPHP framework.
     */
    function camelToUnder(string $str): string
    {
        $str = preg_replace('/([a-z])([A-Z])/', '$1 $2', $str);

        return str_replace(' ', '_', ucwords((string) $str));
    }

    /**
     * Returns "camelCapsWord" and "CamelCapsWord" as "camel-caps-word".
     * 
     * @param string $str The camel-caps word.
     * 
     * @return string The word with dashes in place of camel caps.
     * 
     * This code originally from the Solar_Inflect class in the SolarPHP framework.
     */
    function camelToDashes(string $str): string
    {
        $str = preg_replace('/([a-z])([A-Z])/', '$1 $2', $str);
        $str = str_replace(' ', '-', ucwords((string) $str));

        return strtolower($str);
    }

    /**
     * 
     * @param string $string string to be colored
     * 
     * @param string $foreground_color any of
     *                                          'black', 'dark_gray', 'blue',
     *                                          'light_blue', 'green', 'light_green',
     *                                          'cyan', 'light_cyan', 'red', 'light_red',
     *                                          'purple', 'light_purple', 'brown', 'yellow',
     *                                          'light_gray' and 'white'
     * 
     * @param string $background_color any of
     *                                         'black', 'red', 'green', 'yellow', 'blue',
     *                                         'magenta', 'cyan' and 'light_gray'
     * 
     */
    function color_4_console(
        string $string, 
        string $foreground_color = 'white', 
        string $background_color = 'black'
    ): string {

        if( PHP_OS !== 'Linux') {

            //just return the string as is
            return $string;
        }

        $foreground_colors = [];
        $background_colors = [];

        // Set up shell colors
        $foreground_colors['black'] = '0;30';
        $foreground_colors['dark_gray'] = '1;30';
        $foreground_colors['blue'] = '0;34';
        $foreground_colors['light_blue'] = '1;34';
        $foreground_colors['green'] = '0;32';
        $foreground_colors['light_green'] = '1;32';
        $foreground_colors['cyan'] = '0;36';
        $foreground_colors['light_cyan'] = '1;36';
        $foreground_colors['red'] = '0;31';
        $foreground_colors['light_red'] = '1;31';
        $foreground_colors['purple'] = '0;35';
        $foreground_colors['light_purple'] = '1;35';
        $foreground_colors['brown'] = '0;33';
        $foreground_colors['yellow'] = '1;33';
        $foreground_colors['light_gray'] = '0;37';
        $foreground_colors['white'] = '1;37';

        $background_colors['black'] = '40';
        $background_colors['red'] = '41';
        $background_colors['green'] = '42';
        $background_colors['yellow'] = '43';
        $background_colors['blue'] = '44';
        $background_colors['magenta'] = '45';
        $background_colors['cyan'] = '46';
        $background_colors['light_gray'] = '47';

        $colored_string = "";

        // add foreground color
        $colored_string .= 
            "\033[" . 
            (
                $foreground_colors[$foreground_color]
                ?? $foreground_colors['white']
            ) 
            . "m";

        // add background color
        $colored_string .= 
            "\033[" .
            (
                $background_colors[$background_color]
                ?? $background_colors['black']
            )
            . "m";

        // Add string and end coloring
        $colored_string .= $string . "\033[0m";

        return $colored_string;
    }

} //namespace SlimMvcTools\Functions\Str
