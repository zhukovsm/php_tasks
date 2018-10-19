<?php

var_dump(validateString("{{]])+-}"));
var_dump(validateString("{{[[sf()}"));
var_dump(validateString("{{(++_)}"));;
var_dump(validateString("{{[](safd)}}"));



function validateString($inputString){
    $searchChars = array("{" => 0, "}" => 0, "[" => 0, "]" => 0, "(" => 0, ")" => 0);
    //$searchChars = ["{" => 0, "}" => 0, "[" => 0, "]" => 0, "(" => 0, ")" => 0];
    
    for ($i=0;$i<strlen($inputString);$i++){
        !array_key_exists($inputString{$i}, $searchChars) or $searchChars[$inputString{$i}]++;
    }

    return $searchChars["{"] == $searchChars["}"] &&
           $searchChars["["] == $searchChars["]"] &&
           $searchChars["("] == $searchChars[")"];
}

interface ICheckable{
    public function toArray();
    public function getState();
}

class DoubleParams implements Icheckable{
    private $firstParametr;
    private $secondParametr;
    private $state;
    public function toArray(){
        return [$this->firstParametr, $this->secondParametr];
    }
    public function getState(){
        return $this->state;
    }
    
}
class SingleParams implements Icheckable{
    private $parametr;
    private $state;
    public function toArray(){
        return [$this->parametr];
    }
    public function getState(){
        return $this->state;
    }
}

class StringValidator{
    private $instance;
    private $checkingParams = [];
    private function __construct(){}
    public function getInstance(){
        if (!isset($this->instance)){
            $this->instance = new StringValidator();
        }
        return $this->instance;
    }
    public function addValidationParam($checkingParams){
        if (type_of($checkingParams) = "IChekable"){
            if(count($this->checkingParams)>0){

            }
            else {
                $this->checkingParams += $checkingParams;
            }
            return true;
        }
        return false;
    }
    
    public function validateString($inputString){
        
    }
}
