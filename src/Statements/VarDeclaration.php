<?php


namespace LatinScript\Statements;

use Exception;
use LatinScript\Scope;
use LatinScript\Types\StringType;
use LatinScript\Types\TypeInterface;

class VarDeclaration implements StatementInterface
{
    const VAR_NAME = 0;
    const VAR_ASSIGNMENT = 1;
    const VAR_VALUE = 2;

    const BLANK = ' ';

    const DIRECT_ASSIGNMENT = 0;

    protected string $name = '';
    protected string $assignment_kind = '';
    protected TypeInterface $value;

    public function __construct(protected string $line_content){
        // fixme magari si può fare un explode
        $status = self::VAR_NAME;
        $var_name = &$this->name;
        $var_assignment_kind = &$this->assignment_kind;
        $var_assignment = '';
        $var_value = '';
        //$_var_value = &$this->value;
        foreach (str_split(substr($this->line_content, 4)) as $character) {
            if($status === self::VAR_NAME){
                if($this->isValidVarNameCharacter($character)){
                    $var_name .= $character;
                }
                elseif($character === self::BLANK){
                    if(trim($var_name) === '') continue;
                    $status++;
                }
                else{
                    throw new Exception("not valid var name (invalid character: $character)");
                }
            }
            elseif($status === self::VAR_ASSIGNMENT){
                if($character === self::BLANK){
                    if(trim($var_assignment) === '') continue;
                    $var_assignment_kind = self::getVarAssignmentKind($var_assignment);
                    $status++;
                }
                else{
                    $var_assignment .= $character;
                }
            }
            elseif($status === self::VAR_VALUE){
                $character === self::BLANK ?: $var_value .= $character;
            }
            else{
                throw new Exception('wtf?');
            }

            //var_dump($character);
        }
        $this->value = self::normalizeVarValue($var_value);
    }

    public static function isValidVarNameCharacter(string $character): bool
    {
        return preg_match('/^\w|_$/', $character) === 1;
    }

    public static function isValidVarName(string $character): bool
    {
        return preg_match('/^(?:\w|_)+$/', $character) === 1;
    }

    public static function getVarAssignmentKind(string $keyword): int
    {
        return $keyword === 'aequat' ? self::DIRECT_ASSIGNMENT : throw new Exception("invalid keyword $keyword"); // fixme
    }

    public static function normalizeVarValue(string $value): TypeInterface
    {
        return preg_match('/^(?:"|\')([^"\']+)(?:"|\')$/', $value, $matches) === 1 ? new StringType($matches[1]) : throw new Exception("invalid value $value"); // fixme
    }

    public function execute(Scope $scope): void
    {
        $scope->addVariable($this->name, $this->value);
    }
}