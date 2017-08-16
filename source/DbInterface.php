<?php

namespace SeanJA;

interface DbInterface
{
    /**
     * Escape a value
     *
     * @param string $value The value to be escaped
     *
     * @return string
     */
    public function escape($value);

    /**
     * Quote a string
     *
     * @param string $value The value to be quoted
     *
     * @return string
     */
    public function quote($value);

    /**
     * Quote a field
     *
     * @param string $field The field to be quoted
     *
     * @return string
     */
    public function fieldQuote($field);
}
