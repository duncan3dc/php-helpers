<?php

namespace duncan3dc\Helpers;

/**
 * Allow csv files to be read/written easily and throw exceptions for any failures.
 */
class Csv
{
    /**
     * @var string $delimiter The character to use for delimitation.
     */
    protected $delimiter = ",";

    /**
     * @var string $lineEnding The character to use for line endings.
     */
    protected $lineEnding = "\n";

    /**
     * @var array[] $data An array of arrays each representing 1 row in the csv file.
     */
    protected $data = [];

    /**
     * @var array $fields A map of field names to their positions in the row.
     */
    protected $fields;


    /**
     * Create a new csv file.
     *
     * Instantiating this class is for writing csv files only.
     *
     * @param string $delimiter The character to use for delimitation
     * @param string $lineEnding The character to use for line endings
     */
    public function __construct($delimiter = null, $lineEnding = null)
    {
        if ($delimiter !== null) {
            $this->delimiter = $delimiter;
        }
        if ($lineEnding !== null) {
            $this->lineEnding = $lineEnding;
        }
    }


    /**
     * Set the character to use for delimitation.
     *
     * @param string $delimiter The character to use for delimitation
     *
     * @return static
     */
    public function setDelimiter($delimiter)
    {
        if (!is_string($delimiter) || strlen($delimiter) < 1) {
            throw new \InvalidArgumentException("Invalid delimiter specified, must be a string at least 1 character long");
        }

        $this->delimiter = $delimiter;

        return $this;
    }


    /**
     * Set the character to use for line endings.
     *
     * @param string $lineEnding The character to use for line endings
     *
     * @return static
     */
    public function setLineEnding($lineEnding)
    {
        if (!is_string($lineEnding) || strlen($lineEnding) < 1) {
            throw new \InvalidArgumentException("Invalid line ending specified, must be a string at least 1 character long");
        }

        $this->lineEnding = $lineEnding;

        return $this;
    }


    /**
     * Define the name of the fields in the csv file.
     *
     * @param array $fields An enumerated array of field names
     *
     * @return static
     */
    public function defineFields(array $fields)
    {
        if (count($fields) < 1) {
            throw new \InvalidArgumentException("No fields were specified in the array");
        }

        $this->fields = $fields;

        return $this;
    }


    /**
     * Remove any previously added rows.
     *
     * @return static
     */
    public function clear()
    {
        $this->data = [];

        return $this;
    }


    /**
     * Add a row to the csv.
     *
     * @param array $row Either an enumerated array or an associative array if the fields have been defined using Csv::defineFields()
     *
     * @return static
     */
    public function addRow(array $row)
    {
        if (is_array($this->fields)) {
            $assoc = $row;
            $row = [];
            foreach ($this->fields as $field) {
                $row[] = isset($assoc[$field]) ? $assoc[$field] : "";
            }
        }

        $this->data[] = $row;

        return $this;
    }


    /**
     * Write the csv file to disk.
     *
     * @return static
     */
    public function write($filename)
    {
        $data = $this->asString();
        File::putContents($filename, $data);
    }



    /**
     * Get the csv file as a string.
     *
     * @return string
     */
    public function asString()
    {
        $tmp = new \SplTempFileObject;

        foreach ($this->data as $row) {
            $tmp->fputcsv($row, $this->delimiter);

            if ($this->lineEnding !== "\n") {
                $tmp->fseek(-1, \SEEK_CUR);
                $tmp->fwrite($this->lineEnding);
            }
        }

        # Find out how much data we have written
        $length = $tmp->ftell();
        if ($length < 1) {
            return "";
        }

        # Reset the internal pointer and return all the data we have written
        $tmp->fseek(0);
        return $tmp->fread($length);
    }


    /**
     * Handle the object being cast to a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->asString();
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
        $file = fopen($filename, "r");

        if ($file === false) {
            throw new \RuntimeException("Cannot read the file: {$filename}");
        }

        $data = [];
        while ($row = fgetcsv($file)) {
            $data[] = $row;
        }

        fclose($file);

        return $data;
    }


    /**
     * Write a csv file, and throw a RuntimeException if it cannot be done.
     *
     * @param string $filename The path to the file
     * @param array $data The data to write to the file
     *
     * @return void
     */
    public static function putContents($filename, array $data)
    {
        $csv = new static();

        foreach ($data as $row) {
            $csv->addRow($row);
        }

        $csv->write($filename);
    }
}
