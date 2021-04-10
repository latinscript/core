<?php


namespace LatinScript\Functions;

use LatinScript\Statements\StatementInterface;

interface FunctionCallInterface extends StatementInterface
{
    const BLANK = ' ';
    const STRING_ARG = 0;
    const VARIABLE_ARG = 1;
    //const BOOL_ARG = 0;
}