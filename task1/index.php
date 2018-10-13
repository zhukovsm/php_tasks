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
