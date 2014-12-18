<?php

namespace duncan3dc\Helpers;

class Debug
{
    /**
     * @var boolean $on Whether debugging is currently on or not
     */
    protected static $on = false;

    /**
     * @var int $time The time we started debugging
     */
    protected static $time = 0;

    /**
     * @var string $mode The mode of the output (html/text)
     */
    protected static $mode = "text";

    /**
     * @var int $indent The current indentation level of output
     */
    protected static $indent = 0;


    /**
     * Turn debugging on (so any content is output)
     *
     * @param string $mode The mode to output in
     *
     * @return void
     */
    public static function on($mode = "text")
    {
        if (static::$on) {
            return;
        }
        static::$on = true;
        static::$time = microtime(true);
        static::$mode = $mode;
    }


    /**
     * Turn debugging off (so any content is not output)
     *
     * @return void
     */
    public static function off()
    {
        static::$on = false;
        static::$time = 0;
    }


    /**
     * Get the time elapsed since the we started debugging
     *
     * @return void
     */
    protected static function getTime()
    {
        return number_format(microtime(true) - static::$time, 3);
    }


    /**
     * Output a message, in the current mode, if we are currently debugging
     *
     * @param string $message The message to output
     * @param mixed $data Additional data to output
     *
     * @return void
     */
    public static function output($message, $data = null)
    {
        if (static::$mode == "html") {
            static::html($message, $data);
        } else {
            static::text($message, $data);
        }
    }


    /**
     * Output some indentation based on the current level
     *
     * @return void
     */
    protected static function indent()
    {
        $char = (static::$mode == "html") ? "&nbsp;" : " ";

        for ($i = 0; $i < static::$indent; $i++) {
            for ($y = 0; $y < 4; $y++) {
                echo $char;
            }
        }
    }


    /**
     * Output a message, in text mode, if we are currently debugging
     *
     * @param string $message The message to output
     * @param mixed $data Additional data to output
     *
     * @return void
     */
    public static function text($message, $data = null)
    {
        if (!static::$on) {
            return;
        }

        echo "--------------------------------------------------------------------------------\n";
        static::indent();
        echo static::getTime() . " - " . $message . "\n";
        if (is_array($data)) {
            print_r($data);
        } elseif ($data) {
            static::indent();
            echo "    " . $data . "\n";
        }
    }


    /**
     * Output a message, in html mode, if we are currently debugging
     *
     * @param string $message The message to output
     * @param mixed $data Additional data to output
     *
     * @return void
     */
    public static function html($message, $data = null)
    {
        if (!static::$on) {
            return;
        }

        echo "<hr>";
        echo "<i>";
            static::indent();
            echo "<b>" . static::getTime() . " - " . $message . "</b>";
            if (is_array($data)) {
                Html::print_r($data);
            } elseif ($data) {
                static::indent();
                echo "&nbsp;&nbsp;&nbsp;&nbsp;" . $data . "<br>";
            }
        echo "</i>";
    }


    /**
     * Output a start time, run a callable, then out the endtime
     *
     * @param string $message The message to output
     * @param callable $func The function to execute
     *
     * @return void
     */
    public static function call($message, callable $func)
    {
        $time = microtime(true);

        static::output($message . " [START]");

        static::$indent++;

        $func();

        static::$indent--;

        static::output($message . " [END] (" . number_format(microtime(true) - $time, 3) . ")");
    }
}
