<?php

interface ICheckable{
    public function toArray();
    public function getState();
    public function checkChar($inputChar, $inputCharPosition, &$activeParams);
    public function issetParam($param);
    public function clearState();
    public function setId($id);
    public function getId();
}
abstract class BaseChecker implements ICheckable{
    private $state=0;
    private $id;
    
    abstract public function toArray();
    
    public function getState(){
        return $this->state;
    }

    abstract public function checkChar($inputChar, $inputCharPosition, &$activeParams);

    public function issetParam($param){
        return in_array($param, $this->toArray());
    }
    
    public function clearState(){
        $this->state = 0;
    }
    
    public function setId($id){
        $this->id = $id;
    }
    
    public function getId(){
        return $this->id;
    }
}

class DoubleParamChecker extends BaseChecker{
    private $firstParametr;
    private $secondParametr;

    
    public function __construct($firstParametr, $secondParametr){
        $this->firstParametr = $firstParametr;
        $this->secondParametr = $secondParametr;
    }
    
    public function toArray(){
        return [$this->firstParametr, $this->secondParametr];
    }

    public function checkChar($inputChar, $inputCharPosition, &$activeParams){
        $this->firstParametr == $inputChar and $this->state++;
        $this->secondParametr == $inputChar and $this->state--;
        if ($this->state < 0){
            return false;
        }
        if(!empty($activeParams)){
            if($this->searchParamInToClose($checkingParam)){
                if ( !$this->compare(activeParams[count($this->toClose)-1], $chekingParam) ){
                    return false;
                }
                if ($checkingParam->getState() < 1){
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
    
    public function compare($firstChecker, $secondChecker){
        return $firstChecker->getId() == $secondChecker->getId();
    }

}
class SingleParam extends BaseChecker{
    private $parametr;
    
    public function __construct($parametr){
        $this->parametr = $parametr; 
    }
    
    public function toArray(){
        return [$this->parametr];
    }
    
    public function checkChar($inputChar, $inputCharPosition, &$activeParams){
        if ($this->parametr == $inputChar){
            $this->state = $this->state > 0 ? 0 : 1;
        }
        return true;
    }

}

class StringValidator{
    private $checkers = [];
    private $toClose = [];
    private $lastCheckerId = 0;

    public function __construct(){}

    public function addChecker($checkers){
        if (is_array($checkers)){
            foreach($checkers as $checker){
                $this->validateAndAddChecker($checker);
            }
        } else {
            $this->validateAndAddChecker($checkers);
        }
        return false;
    }
    
    private function validateAndAddChecker($checker){
        if ($this->checkInstance($checker)){
            foreach ($this->checkers as $innerChecker){
                if ($innerChecker->issetParam($checker)){
                    return false;
                }
            }
            $checker->setId($this->lastCheckerId++);
            $this->checkingParams[] = $checker;
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
                if(!$checkingParam->checkChar($inputString{$i}, $i, $this->toClose)){
                    return false;
                }
            }
            return true;
        }
    }
   
    private function clearParams(){
        foreach ($this->checkers as $checker){
            $checker->clearState();
        }
    }
}

$firstDoubleParamChecker = new DoubleParamChecker("{", "}");
$secondDoubleParamChecker = new DoubleParamChecker("[", "]");
$lastDoubleParamChecker = new DoubleParamChecker("(", ")");
$firstSingleParam = new SingleParam("~");

$stringValidator = new StringValidator();
$stringValidator->addChecker([$firstDoubleParamChecker, $secondDoubleParamChecker, $lastDoubleParamChecker, $firstSingleParam]);

var_dump($stringValidator->validateString("(){()}[])("));
//var_dump($stringValidator->validateString("(){()}[{]}"));
//var_dump($stringValidator->validateString("{{[[sf()}"));
//var_dump($stringValidator->validateString("{{(++_)}"));;
//var_dump($stringValidator->validateString("{{[](safd)}}"));
//var_dump($stringValidator->validateString("~{{[](safd)}}"));
//var_dump($stringValidator->validateString("~{{[](safd)}}~"));
