<?php

namespace Tests\SeanJA;

use SeanJA\DbInterface;

/**
 * Class Db
 * @package Tests\SeanJA
 */
class Db implements DbInterface
{
    /**
     * Wrap string in this
     *
     * @var string
     */
    const QUOTE = "'";
    /**
     * Wrap fields in this
     *
     * @var string
     */
    const FIELD_QUOTE = "`";

    /**
     * Escape a value
     *
     * @param string $value The value to be escaped
     *
     * @return string
     */
    function escape($value)
    {
        return $value;
    }

    /**
     * Quote a string
     *
     * @param string $value The value to be quoted
     *
     * @return string
     */
    public function quote($value)
    {
        return static::QUOTE . $value . static::QUOTE;
    }

    /**
     * Quote a field
     *
     * @param string $field The field to be quoted
     *
     * @return string
     */
    public function fieldQuote($field)
    {
        return static::FIELD_QUOTE . $field . static::FIELD_QUOTE;
    }
}
