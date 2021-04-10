<?php


namespace LatinScript\Statements\Bodied;


use LatinScript\Scope;
use LatinScript\Statements\StatementInterface;

interface BodiedStatementInterface extends StatementInterface
{
    public function execute(Scope $scope): void;

    public function push(StatementInterface $statement): void;
}