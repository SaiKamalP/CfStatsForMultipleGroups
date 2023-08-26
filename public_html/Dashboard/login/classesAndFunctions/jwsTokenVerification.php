<?php
    require_once __DIR__."/jwsAuth/JWS.php";
    require_once __DIR__."/dbAndOtherDetails.php";
    
    function getJWTAUTHResult(){
        if (!isset($_COOKIE['jwtToken']) || empty($_COOKIE['jwtToken'])) {
            removeJWTToken();
            return null;
        }
        try{
            $jws = new JWS();
            global $key;
            $jwtResult=$jws->verify($_COOKIE['jwtToken'], $key);
            if (time() > $jwtResult['payload']['iat']) {
                removeJWTToken();
                return null;
            }
            return  $jwtResult;
        }
        catch(Exception $e){
            return null;
        }
    }
    function removeJWTToken(){
        setcookie('jwtToken', '', time() - 3600, '/');

    }
?>