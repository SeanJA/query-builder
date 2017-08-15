<?php
namespace SeanJA;

interface DbInterface
{
    /**
     * Escape a value
     * @param $value
     * @return string
     */
    public function escape($value);

    /**
     * Quote a string
     * @param $value
     * @return mixed
     */
    public function quote($value);

    /**
     * Quote a field
     * @param $value
     * @return mixed
     */
    public function fieldQuote($value);
}
