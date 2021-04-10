<?php

namespace LatinScript\Functions;

use Exception;
use LatinScript\Scope;
use LatinScript\VarDeclaration;

class Verbo extends AbstractFunctionCall implements FunctionCallInterface
{
    protected array $args;

    public static array $signature = [self::STRING_ARG];


    public function execute_(Scope $scope): void
    {
        // TODO: Implement execute() method.
        echo $this->args[0], PHP_EOL;
    }

}