<?php


namespace LatinScript\Statements\Bodied;


use LatinScript\Statements\StatementInterface;

abstract class AbstractBodiedStatement implements BodiedStatementInterface
{
    /**
     * @var StatementInterface[]
     */
    protected array $statements;

    public function push(StatementInterface $statement): void
    {
        $this->statements[] = $statement;
    }
}