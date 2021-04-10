<?php
namespace LatinScript;

use Exception;
use LatinScript\Functions\FunctionCallInterface;
use LatinScript\Functions\Verbo;
use LatinScript\Statements\Bodied\BodiedStatementInterface;
use LatinScript\Statements\Bodied\SiOperator;
use LatinScript\Statements\StatementInterface;
use LatinScript\Statements\VarDeclaration;

class Parser{

    const NO_TOKEN = 0;
    const FUNCTION_CALL_TOKEN = 1;
    const COMMENT = 2;
    const COMMENT_KEYWORDS = ['~'];

    const BODY_INDENT_TOKEN = -1;
    const BODY_INDENT = ['    ', ' '];


    protected static array $tokens = [
        'var',
        'si',
    ];

    const BUILTIN_FUNCTIONS = [
        'verbo' => Verbo::class
    ];

    protected string $doing;
    protected int $current_line;
    protected string $php_code = "";
    protected string $file_content;

    protected Scope $main_scope;

    /**
     * @var StatementInterface[]
     */
    protected array $ast;

    public function __construct(protected string $filename)
    {
        if(!file_exists($filename)){
            throw new Exception("File $filename not found");
        }
        $this->file_content = trim(file_get_contents($filename));
        $this->main_scope = new Scope($filename);
    }

    protected static function getFirstWord(string $code_line): string
    {
        //var_dump($code_line);
        preg_match('/^([^\s(]+)[( ]/', $code_line, $matches) ?: throw new Exception("Not found word in $code_line");
        //var_dump($matches);
        return $matches[1];
    }

    protected function getFirstToken(string $code_line): string|int
    {
        if(empty($code_line)) return self::COMMENT;
        foreach (self::BODY_INDENT as $str) {
            if(str_starts_with($code_line, $str)){
                return self::BODY_INDENT_TOKEN;
            }
        }
        $first_word = self::getFirstWord($code_line);
        //var_dump($first_word);
        if(($token_index = array_search($first_word, self::$tokens)) !== false){
            return self::$tokens[$token_index];
        }
        // is a function call
        elseif(isset(self::BUILTIN_FUNCTIONS[$first_word])){
            return self::FUNCTION_CALL_TOKEN;
        }
        else return in_array($code_line[0], self::COMMENT_KEYWORDS) ? self::COMMENT : self::NO_TOKEN;
        //else throw new \Exception("Error while parsing offset $offset at line {$this->current_line}: $line_segment");

    }

    protected static function getFunctionCallInstance(string $code_line): FunctionCallInterface
    {
        $function_name = self::getFirstWord($code_line);
        $function_class = self::BUILTIN_FUNCTIONS[$function_name] ?? throw new Exception("not found function $function_name");
        return new $function_class($code_line);

    }

    /**
     * @throws \Exception
     */
    public function parse(){
        $line = 1;
        $file_content = $this->file_content;
        $lines_content = explode(PHP_EOL, $file_content);
        $scope = $this->main_scope;

        /** @var \LatinScript\Statements\Bodied\BodiedStatementInterface[] $bodied_statements */
        $bodied_statements = [];
        foreach($lines_content as $line_content){
            //var_dump($this->getFirstToken($line_content));
            $token = $this->getFirstToken($line_content);
            $statement = match ($token){
                'var' => new VarDeclaration($line_content),
                'si' => new SiOperator($line_content),
                self::FUNCTION_CALL_TOKEN => self::getFunctionCallInstance($line_content),
                self::COMMENT => null,
                self::BODY_INDENT_TOKEN => self::BODY_INDENT_TOKEN,
                self::NO_TOKEN => throw new Exception("no token ($line_content)"),
                default => throw new Exception("error: $token returned")
            };

            var_dump($statement);

            if($statement instanceof StatementInterface){
                if($statement instanceof BodiedStatementInterface){
                    $bodied_statements[] = $statement;
                }
                elseif(!empty($bodied_statements)){
                    end($bodied_statements)->push($statement);
                }
                else $this->ast[] = $statement;
            }
            elseif($statement === self::BODY_INDENT_TOKEN){
                if(!empty($bodied_statements)){
                    $bodied_statement = array_pop($bodied_statements);
                    //(end($bodied_statements) ?: $scope)->push($bodied_statement);
                    empty($bodied_statements) ? $this->ast[] = $bodied_statement : end($bodied_statements)->push($bodied_statement);
                }
                else throw new \Exception('unmotivated indent');
            }

            $line++;
            //break;
        }
        var_dump($this->ast);
        foreach ($this->ast as $statement) {
            //var_dump($statement);
            $statement->execute($scope);
        }
        //var_dump($scope);
    }

    protected function parseStatementBody(){

    }

    public function parseFile()
    {
        $file_content = $this->file_content;
        $last_offset = 0;
        /*while ($last_offset < strlen($file_content)) {
            $token = $this->getNextToken($file_content, $last_offset);
            var_dump($token);
            $last_offset += strlen($token)+1;
            if($token === "var"){
                $this->doing = "declaring_var";
                #$var = new DeclareVariable(substr($file_content, strpos($file_content, PHP_EOL, $last_offset)));

            }
        }
        */
        $this->current_line = 1;
        foreach(explode(PHP_EOL, $file_content) as $code_line){

            if(empty($code_line)){
                continue;
            }
            $token = $this->getNextToken($code_line, $last_offset);
            #var_dump($token);
            match($token){
                'var' => $this->declareVariable($code_line),
                default => throw new Exception("Error while parsing at line {$this->current_line}: $code_line")
            };
            #break;
            continue;
            $last_offset += strlen($token)+1;
            if($token === "var"){
                $this->doing = "declaring_var";
                if(preg_match_all('/([\w\d]+) aequat ((?:"|\').+(?:"|\'))/', substr($code_line, $last_offset), $matches) !== 0){
                    $this->addPHPLine("\${$matches[1][0]} = {$matches[2][0]};");
                }
                elseif(preg_match_all('/([\w\d]+) enumero ((?:"|\').+(?:"|\'))/', substr($code_line, $last_offset), $matches) !== 0){
                    $this->addPHPLine("\${$matches[1][0]} = [{$matches[2][0]}];");
                }

            }
            if($token === "si"){
                $this->doing = "if";
                if(preg_match_all('/([\w\d]+) (.+) (\w+) est:/', substr($code_line, $last_offset), $matches) !== 0){
                    $this->addPHPLine("if(\${$matches[1][0]} === {$matches[2][0]}){}"); // FIXME
                }

            }
            $last_offset = 0;
            $this->current_line++;
        }
    }

    protected function declareVariable(string $code_line)
    {
        $offset = 0;
        $doing = null;
        while ($offset < strlen($code_line)) {
            $line_segment = substr($code_line, $offset);
            if(!isset($doing)){
                $token = $this->getNextToken($code_line, 0);
                $offset += strlen($token)+1;
                if($token === 'var'){
                    $doing = "looking_for_variable_name";
                }
            }
            elseif($doing === "looking_for_variable_name"){
                $variable_name = $this->getNextIdentifier($code_line, $offset);
                $offset += strlen($variable_name)+1;
                $doing = "looking_for_definer";
            }
            elseif($doing === "looking_for_definer"){
                $token = $this->lookForTokenOrThrowException($code_line, $offset, ['aequat', 'enumero']);
                $offset += strlen($token)+1;
                $doing = 'looking_for_'.match($token){
                    'aequat' => 'variable',
                    'enumero' => 'array',
                };
            }
            elseif($doing === "looking_for_variable"){ // int or string
                if(preg_match('/^(".+")|(\'.+\')|(\d+)$/', $line_segment)){
                    $variable_content = $line_segment;
                    $offset += strlen($line_segment)+1;
                    $this->addPHPVariableDeclaration($variable_name, $variable_content);
                    break;
                }
                else throw new Exception("Could not declare variable, invalid value $line_segment at: $code_line");
            }
            elseif($doing === "looking_for_array"){ // array
                // annecto by reference
                // transcribo copia
                if(preg_match('/^(?:(?:\d+|(?:annecto|transcribo) +\w+|\"[^"]*"|\'[^\']*\')\, *)+(?:\d+|(?:annecto|transcribo) +\w+|\"[^"]*"|\'[^\']*\')$/', $line_segment)){
                    $variable_content = $line_segment;
                    $variable_content = preg_replace('/annecto +(\w+)/', '&\$$1', $variable_content);
                    $variable_content = preg_replace('/transcribo +(\w+)/', '\$$1', $variable_content);
                    #$offset += strlen($line_segment)+1;
                    $this->addPHPVariableDeclaration($variable_name, "[$variable_content]");
                    break;
                }
                else throw new Exception("Could not declare variable, invalid value $line_segment at: $code_line");
            }
            else{
                throw new Exception("Could not declare variable at: $code_line");
            }
        }
    }

    protected function addPHPVariableDeclaration(string $name, string $value){
        $this->addPHPLine("\$$name = $value;");
    }

    public function getNextToken(string $line_content, int $offset = 0): string
    {
        $content = substr($line_content, $offset);
        if(strlen($content) === 0){
            return false;
        }
        foreach(self::$tokens as $token){
            if(str_starts_with($content, $token.' ')){
                return $token;
            }
        }
        throw new Exception("Error while parsing offset $offset at line {$this->current_line}: $line_content");

    }

    public function getNextIdentifier(string $code_line, int $offset = 0): ?string
    {
        $line_segment = substr($code_line, $offset);
        if(strlen($line_segment) === 0){
            return null;
        }
        #exit;
        if(preg_match('/^([a-z][\w\_]+)/', $line_segment, $matches)){
            return $matches[0];
        }
        else throw new Exception("Error while parsing offset $offset at line {$this->current_line}: $line_segment");

    }

    public function lookForTokenOrThrowException(string $code_line, int $offset, string|array $tokens): string
    {
        if(is_string($tokens)){
            $tokens = [$tokens];
        }
        $line_segment = substr($code_line, $offset);
        foreach($tokens as $token){
            if(str_starts_with($line_segment, $token.' ')){
                return $token;
            }
        }
        throw new Exception("Error while parsing offset $offset at line {$this->current_line}: expected ".self::arrayToString($tokens).". Got: $line_segment");

    }

    protected function addPHPLine(string $code_line)
    {
        $this->php_code .= $code_line.PHP_EOL;
    }

    public static function arrayToString(array $array){
        return implode(" or ", $array);
    }

    public function __destruct()
    {
        if(empty($this->php_code)) return;
        var_dump($this->php_code);
        eval($this->php_code);
    }
}

?>
