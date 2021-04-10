<?php


namespace LatinScript\Statements;


use LatinScript\Scope;

interface StatementInterface
{
    public function execute(Scope $scope): void;
}