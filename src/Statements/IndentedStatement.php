<?php


namespace LatinScript\Statements;


use LatinScript\Scope;

class IndentedStatement implements StatementInterface
{

    protected int $level;

    public function __construct()
    {

    }


    public function execute(Scope $scope): void
    {
        // TODO: Implement execute() method.
    }
}