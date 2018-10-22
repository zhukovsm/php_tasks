<?php

interface ICheckable{
    public function toArray();
    public function getState();
    public function checkChar($inputChar);
    public function issetParam($param);
    public function clearState();
}

class DoubleParam implements ICheckable{
    private $firstParametr;
    private $secondParametr;
    private $state=0;
    
    public function __construct($firstParametr, $secondParametr){
        $this->firstParametr = $firstParametr;
        $this->secondParametr = $secondParametr;
    }
    
    public function toArray(){
        return [$this->firstParametr, $this->secondParametr];
    }
    
    public function getState(){
        return $this->state;
    }
    
    public function checkChar($inputChar){
        $this->firstParametr == $inputChar and $this->state++;
        $this->secondParametr == $inputChar and $this->state--;
        return $this->state >= 0;
    }
    
    public function issetParam($param){
        return in_array($param, $this->toArray());
    }
    
    public function clearState(){
        $this->state = 0;
    }
}
class SingleParam implements ICheckable{
    private $parametr;
    private $state = 0;
    
    public function __construct($parametr){
        $this->parametr = $parametr; 
    }
    
    public function toArray(){
        return [$this->parametr];
    }
    
    public function getState(){
        return $this->state;
    }
    
    public function checkChar($inputChar){
        if ($this->parametr == $inputChar){
            $this->state = $this->state > 0 ? 0 : 1;
        }
        return true;
    }
    
    public function issetParam($param){
        return in_array($param, $this->toArray());
    }
    
    public function clearState(){
        $this->state = 0;
    }
}

class StringValidator{
    private $instance;
    private $checkingParams = [];
    private $toClose = [];

    public function __construct(){}

    public function addValidationParam($checkingParams){
        if (is_array($checkingParams)){
            foreach($checkingParams as $checkingParam){
                $this->checkAndAddParam($checkingParam);
            }
        } else {
            $this->checkAndAddParam($checkingParams);
        }
        return false;
    }
    
    private function checkAndAddParam($checkingParam){
        if ($this->checkInstance($checkingParam)){
            foreach ($this->checkingParams as $innerCheckingParam){
                if ($innerCheckingParam->issetParam($checkingParam)){
                    return false;
                }
            }
            $this->checkingParams[] = $checkingParam;
            return true;
        }
        return false;
    }
    
    private function checkInstance($instance){
        return $instance instanceof ICheckable;
    }
    
    public function validateString($inputString){
        $this->clearParams();
        $this->toClose = [];
        for ($i=0;$i<strlen($inputString);$i++){
            foreach ($this->checkingParams as $checkingParam){
                if(!$checkingParam->checkChar($inputString{$i})){
                    return false;
                }
                else {
                    if(!empty($this->toClose)){
                        if($this->searchParamInToClose($checkingParam)){
                            echo "aouaou";
                            if ( !$this->checkParamWithLastFromToClose($chekingParam) ){
                                return false;
                            }
                            if ($checkingParam->getState() < 1){
                                echo 'aou';
                                array_pop($this->toClose);
                            }
                        }
                        else{
                            array_push($this->toClose,$checkingParam);
                        }
                    }
                    else{
                        array_push($this->toClose,$checkingParam);
                    }
                }
            }
            print_r($this->toClose);
            return empty($this->toClose);
        }
        
        return false;
    }
    
    private function searchParamInToClose($param){
        
    }
    
    private function checkParamWithLastFromToClose($param){
        return $this->toClose[count($this->toClose)-1] == $param;
    }
   
    private function clearParams(){
        foreach ($this->checkingParams as $checkingParam){
            $checkingParam->clearState();
        }
    }
}

$firstDoubleParam = new DoubleParam("{", "}");
$secondDoubleParam = new DoubleParam("[", "]");
$lastDoubleParam = new DoubleParam("(", ")");
$firstSingleParam = new SingleParam("~");

$stringValidator = new StringValidator();
$stringValidator->addValidationParam([$firstDoubleParam, $secondDoubleParam, $lastDoubleParam, $firstSingleParam]);

var_dump($stringValidator->validateString("(){()}[])("));
//var_dump($stringValidator->validateString("(){()}[{]}"));
//var_dump($stringValidator->validateString("{{[[sf()}"));
//var_dump($stringValidator->validateString("{{(++_)}"));;
//var_dump($stringValidator->validateString("{{[](safd)}}"));
//var_dump($stringValidator->validateString("~{{[](safd)}}"));
//var_dump($stringValidator->validateString("~{{[](safd)}}~"));