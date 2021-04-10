<?php


namespace LatinScript\Types;



use LatinScript\Scope;

class VariableType implements TypeInterface
{
    public function __construct(protected string $var_name){}

    /**
     * @return string
     */
    public function getVarName(): string
    {
        return $this->var_name;
    }

    public function getScopedValue(Scope $scope): TypeInterface
    {
        return $scope->getVariable($this->var_name);
    }

}