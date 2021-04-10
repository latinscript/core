<?php


namespace LatinScript;


use LatinScript\Types\TypeInterface;

class Scope
{

    public function __construct(protected string $filename)
    {
    }

    /**
     * @var TypeInterface[]
     */
    protected array $variables = [];

    /**
     * @return TypeInterface[]
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @param string $name
     * @param \LatinScript\Types\TypeInterface $value
     * @throws \Exception
     */
    public function addVariable(string $name, TypeInterface $value): void
    {
        if(!isset($this->variables[$name])){
            $this->variables[$name] = $value;
        }
        else{
            throw new \Exception("Variable $name already defined");
        }
    }

    /**
     * @throws \Exception
     */
    public function getVariable(string $var_name): TypeInterface
    {
        return $this->getVariables()[$var_name] ?? throw new \Exception("not found var $var_name");
    }

}