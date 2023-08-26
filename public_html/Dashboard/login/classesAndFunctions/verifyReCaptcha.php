<?php
    require_once __DIR__."/dbAndOtherDetails.php";
    function verifyReCaptcha(){
        if(!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])){
            return false;
        }
        global $reCaptchaSecretKey;

        $reCaptchaAPIUrl="https://www.google.com/recaptcha/api/siteverify";

        $reCaptchaVerificationPostData=array(
            'secret'=>$reCaptchaSecretKey,
            'response'=>$_POST['g-recaptcha-response']
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $reCaptchaAPIUrl);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $reCaptchaVerificationPostData);

        $reCaptchaVerificationResponce=curl_exec($ch);
        if($reCaptchaVerificationResponce==false){
            return false;
        }

        $reCaptchaVerificationResponceJson=json_decode($reCaptchaVerificationResponce,true);
        if($reCaptchaVerificationResponceJson['success']==false || $reCaptchaVerificationResponceJson['score']<0.6){
 
            return false;

        }
       return true;
       
    }

?>