<?php

namespace duncan3dc\Helpers;

/**
 * Allow csv files to be read/written easily and throw exceptions for any failures.
 */
class Csv extends \SplFileObject
{

    /**
     * @var string $lineEnding The character to use for line endings.
     */
    public static $lineEnding = "\n";


    /**
     * Convert a multi-dimensional array of rows and fields to a csv string.
     *
     * @param array $data The data to convert
     *
     * @return string The converted csv string
     */
    public static function arrayToString(array $data)
    {
        $tmp = new \SplTempFileObject;
        foreach ($data as $row) {
            $tmp->fputcsv($row);
            if (static::$lineEnding !== "\n") {
                $tmp->fseek(-1, \SEEK_CUR);
                $tmp->fwrite(static::$lineEnding);
            }
        }
        $length = $tmp->ftell();
        $tmp->fseek(0);

        return $tmp->fread($length);
    }


    /**
     * Read the contents of a csv file, and throw a RuntimeException if it cannot be done.
     *
     * @param string $filename The path to the file
     *
     * @return array The data read from the file
     */
    public static function getContents($filename)
    {
        $contents = File::getContents($filename);

        # Remove any trailing blank lines
        $contents = rtrim($contents);

        # Break up the file by newlines
        $data = explode("\n", $contents);

        # Trim each line
        $data = array_map("trim", $data);

        # Convert each line to an array
        $data = array_map("str_getcsv", $data);

        return $data;
    }


    /**
     * Write to a file, and throw a RuntimeException if it cannot be done.
     *
     * @param string $filename The path to the file
     * @param array $data The data to write to the file
     *
     * @return void
     */
    public static function putContents($filename, array $data)
    {
        $contents = static::arrayToString($data);
        File::putContents($filename, $contents);
    }


    /**
     * Append to a file, and throw a RuntimeException if it cannot be done.
     *
     * @param string $filename The path to the file
     * @param string $data The data to write to the file
     *
     * @return void
     */
    public static function appendContents($filename, array $data)
    {
        $contents = static::arrayToString($data);
        File::appendContents($filename, $contents);
    }


    /**
     * Read the contents of a file, and throw a RuntimeException if it cannot be done.
     *
     * @return array The data read from the file
     */
    public function get()
    {
        return static::getContents($this->getPathname());
    }


    /**
     * Write to a file, and throw a RuntimeException if it cannot be done.
     *
     * @param array $data The data to write to the file
     *
     * @return static
     */
    public function put(array $data)
    {
        static::putContents($this->getPathname(), $data);

        return $this;
    }



    /**
     * Append to a file, and throw a RuntimeException if it cannot be done.
     *
     * @param array $data The data to write to the file
     *
     * @return static
     */
    public function append(array $data)
    {
        static::appendContents($this->getPathname(), $data);

        return $this;
    }
}
