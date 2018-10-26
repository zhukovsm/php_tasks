<?php

interface ICheckable {

    public function toArray();

    public function getState();

    public function setState($state);

    public function checkChar($inputChar, $inputCharPosition, &$activeParams);

    public function issetParam($param);

    public function clearState();

    public function setId($id);

    public function getId();
}

abstract class BaseChecker implements ICheckable {

    private $state = 0;
    protected $id;

    abstract public function toArray();

    public function getState() {
        return $this->state;
    }

    public function setState($state) {
        $this->state = $state;
    }

    abstract public function checkChar($inputChar, $inputCharPosition, &$activeParams);

    public function issetParam($param) {
        return in_array($param, $this->toArray());
    }

    public function clearState() {
        $this->setState(0);
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }
    
    public function compare($firstChecker, $secondChecker) {
        return $firstChecker->getId() == $secondChecker->getId();
    }

}

class DoubleParamChecker extends BaseChecker {

    private $firstParametr;
    private $secondParametr;

    public function __construct($firstParametr, $secondParametr) {
        $this->firstParametr = $firstParametr;
        $this->secondParametr = $secondParametr;
    }

    public function toArray() {
        return [$this->firstParametr, $this->secondParametr];
    }

    public function checkChar($inputChar, $inputCharPosition, &$activeParams) {
        $this->firstParametr == $inputChar and $this->incState();
        $this->secondParametr == $inputChar and $this->decState();
        print_r("state = ".$this->getId(). " ");
        if ($this->getState() < 0) {
            return false;
        }
        if (!empty($activeParams)) {
            if (!$this->compare(end($activeParams),$this)) {
                return false;
            }
            array_pop($activeParams);
        } else {
            array_push($activeParams, $this);
        }
        print_r($activeParams);
        return true;
    }

    public function incState(){
        $state = $this->getState();
        $state++;
        $this->setState($state);
    }
    public function decState(){
        $state = $this->getState();
        $state--;
        $this->setState($state);
    }
}

class SingleParam extends BaseChecker {

    private $parametr;

    public function __construct($parametr) {
        $this->parametr = $parametr;
    }

    public function toArray() {
        return [$this->parametr];
    }

    public function checkChar($inputChar, $inputCharPosition, &$activeParams) {
        if ($this->parametr == $inputChar) {
            if ($this->getState()) {
                if (!empty($activeParams)) {
                    if (!$this->compare(end($activeParams),$this)) {
                        return false;
                    }
                    array_pop($activeParams);
                }
            } else {
                array_push($activeParams, $this);
            }
            $state = $this->getState() > 0 ? 0 : 1;
            $this->setState($state);
        }
        return true;
    }

}

class StringValidator {

    private $checkers = [];
    private $mustBeClosed = [];
    private $lastCheckerId = 0;

    public function __construct() {
        
    }

    public function addChecker($checkers) {
        if (is_array($checkers)) {
            foreach ($checkers as $checker) {
                $this->validateAndAddChecker($checker);
            }
        } else {
            $this->validateAndAddChecker($checkers);
        }
        return false;
    }

    private function validateAndAddChecker($checker) {
        if ($this->checkInstance($checker)) {
            foreach ($this->checkers as $innerChecker) {
                if ($innerChecker->issetParam($checker)) {
                    return false;
                }
            }
            $checker->setId($this->lastCheckerId++);
            $this->checkers[] = $checker;
            return true;
        }
        return false;
    }

    private function checkInstance($instance) {
        return $instance instanceof ICheckable;
    }

    public function validateString($inputString) {
        $this->clearParams();
        $this->mustBeClosed = [];
        for ($i = 0; $i < strlen($inputString); $i++) {
            foreach ($this->checkers as $checkingParam) {
                if (!$checkingParam->checkChar($inputString{$i}, $i, $this->mustBeClosed)) {
                    print_r($i);
                    return false;
                }
            }
        }

        return count($this->mustBeClosed) == 0;
    }

    private function clearParams() {
        foreach ($this->checkers as $checker) {
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
//$stringValidator->addChecker([$firstDoubleParamChecker]);

var_dump($stringValidator->validateString("~(){()}[])("));
var_dump($stringValidator->validateString("~(){()}[])(~"));
var_dump($stringValidator->validateString("{}"));
var_dump($stringValidator->validateString("(){()}[{]}"));
var_dump($stringValidator->validateString("{{[[sf()}"));
//var_dump($stringValidator->validateString("{{(++_)}"));;
//var_dump($stringValidator->validateString("{{[](safd)}}"));
//var_dump($stringValidator->validateString("~{{[](safd)}}"));
var_dump($stringValidator->validateString("~{{[](safd)}}~"));
