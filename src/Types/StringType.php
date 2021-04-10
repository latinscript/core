<?php


namespace LatinScript\Types;


class StringType implements TypeInterface
{
    public function __construct(protected string $value){}

    public function __toString(): string
    {
        return $this->value;
    }
}