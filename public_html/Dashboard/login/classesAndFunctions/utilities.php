<?php

    function isAValidNameWithSpace($string,$maxlen=30){
        if(strlen($string)<=0 || strlen($string)>$maxlen){
            return false;
        }
        $validCharacters="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#_.- ";
        for($i=0;$i<strlen($string);$i++){
            if(strpos($validCharacters,$string[$i])===false){
                return false;
            }
        }
        return true;
    }

    function isAValidName($string,$maxlen=30){
        if(strlen($string)<=0 || strlen($string)>$maxlen){
            return false;
        }
        $validCharacters="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#_.-";
        for($i=0;$i<strlen($string);$i++){
            if(strpos($validCharacters,$string[$i])===false){
                return false;
            }
        }
        return true;
    }

?>