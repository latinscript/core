<?php


namespace LatinScript\Functions;


use Exception;
use LatinScript\Scope;
use LatinScript\Statements\VarDeclaration;
use LatinScript\Types\TypeInterface as TypeInterface;
use LatinScript\Types\VariableType;

abstract class AbstractFunctionCall implements FunctionCallInterface
{
    protected static array $signature;

    protected bool $has_variable_args = false;

    /**
     * @var TypeInterface[]
     */
    protected array $args;

    /**
     * @throws \Exception
     */
    public function __construct(string $line_content){
        $arguments_string = substr($line_content, strpos($line_content, '(')+1, -1);
        //var_dump($arguments_string);
        $args = [];
        $arg_type = null;
        $arg_value = '';
        foreach (str_split($arguments_string) as $character) {
            if(!isset($arg_type)){
                if(self::isCharacterStringDelimiter($character)){
                    $arg_type = self::STRING_ARG;
                }
                elseif(VarDeclaration::isValidVarNameCharacter($character)){
                    $arg_type = self::VARIABLE_ARG;
                    $this->has_variable_args = true;
                }
                elseif($character !== self::BLANK && $character !== ',') throw new Exception("not valid character $character between 2 args");
                
                if($arg_type !== self::VARIABLE_ARG && isset(static::$signature)){
                    if(static::$signature[count($args)] ?? null !== $arg_type){
                        throw new Exception("Wrong type of arg ".(count($args)+1)." in function call");
                    }
                }

                $arg_value .= $character;
            }
            else{
                if($arg_type === self::STRING_ARG){
                    $arg_value .= $character;
                    if(self::isCharacterStringDelimiter($character)){
                        $args[] = VarDeclaration::normalizeVarValue($arg_value);
                        $arg_type = null;
                        $arg_value = '';
                    }
                }
                elseif($arg_type === self::VARIABLE_ARG){
                    //$arg_value .= $character;
                    if(VarDeclaration::isValidVarNameCharacter($character)){
                        $arg_value .= $character;
                    }
                    elseif($character === ','){
                        $args[] = new VariableType($arg_value);
                        $arg_type = null;
                        $arg_value = '';
                    }
                    else throw new Exception("not valid character $character after variable in arguments list");
                }
            }
        }
        if(isset($arg_type)){
            if($arg_type === self::VARIABLE_ARG){
                $args[] = new VariableType($arg_value);
            }
        }
        $this->args = $args;
    }

    public static function isCharacterStringDelimiter(string $character): bool
    {
        return $character === '"' || $character === '\'';
    }


    public function execute(Scope $scope): void
    {
        if($this->has_variable_args){
            foreach ($this->args as &$arg) {
                if($arg instanceof VariableType){
                    $arg = $arg->getScopedValue($scope);
                }
            }
        }
        $this->execute_($scope);
    }

    abstract protected function execute_(Scope $scope);
}