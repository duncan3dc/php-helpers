<?php

namespace duncan3dc\Helpers;

/**
 * Allow files to be read/written easily and throw exceptions for any failures.
 */
class File extends \SplFileObject
{

    /**
     * Read the contents of a file, and throw a RuntimeException if it cannot be done.
     *
     * @param string $filename The path to the file
     *
     * @return string The data read from the file
     */
    public static function getContents($filename)
    {
        $contents = file_get_contents($filename);
        if ($contents === false) {
            throw new \RuntimeException("Cannot read the file: " . $filename);
        }

        return $contents;
    }


    /**
     * Write to a file, and throw a RuntimeException if it cannot be done.
     *
     * @param string $filename The path to the file
     * @param string $contents The data to write to the file
     *
     * @return void
     */
    public static function putContents($filename, $contents)
    {
        $result = file_put_contents($filename, $contents, \LOCK_EX | \LOCK_NB);
        if ($result === false) {
            throw new \RuntimeException("Cannot write the file: " . $filename);
        }
    }


    /**
     * Append to a file, and throw a RuntimeException if it cannot be done.
     *
     * @param string $filename The path to the file
     * @param string $contents The data to write to the file
     *
     * @return void
     */
    public static function appendContents($filename, $contents)
    {
        $result = file_put_contents($filename, $contents, \LOCK_EX | \LOCK_NB | \FILE_APPEND);
        if ($result === false) {
            throw new \RuntimeException("Cannot append to the file: " . $filename);
        }
    }


    /**
     * Read the contents of a file, and throw a RuntimeException if it cannot be done.
     *
     * @return string The data read from the file
     */
    public function get()
    {
        return static::getContents($this->getPathname());
    }


    /**
     * Write to a file, and throw a RuntimeException if it cannot be done.
     *
     * @param string $contents The data to write to the file
     *
     * @return static
     */
    public function put($contents)
    {
        static::putContents($this->getPathname(), $contents);

        return $this;
    }



    /**
     * Append to a file, and throw a RuntimeException if it cannot be done.
     *
     * @param string $contents The data to write to the file
     *
     * @return static
     */
    public function append($contents)
    {
        static::appendContents($this->getPathname(), $contents);

        return $this;
    }
}
