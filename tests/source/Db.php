<?php

namespace Tests\SeanJA;

use SeanJA\DbInterface;

class Db implements DbInterface
{
    /**
     * @var string
     */
    const QUOTE = "'";
    /**
     * @var string
     */
    const FIELD_QUOTE = "`";

    /**
     * Escape a value
     * @param $value
     * @return mixed
     */
    function escape($value)
    {
        return $value;
    }

    /**
     * Quote a string
     * @param $value
     * @return mixed
     */
    public function quote($value)
    {
        return static::QUOTE . $value . static::QUOTE;
    }

    /**
     * Quote a field
     * @param $value
     * @return mixed
     */
    public function fieldQuote($value)
    {
        return static::FIELD_QUOTE . $value . static::FIELD_QUOTE;
    }
}
