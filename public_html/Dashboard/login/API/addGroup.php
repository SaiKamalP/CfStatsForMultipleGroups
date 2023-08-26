<?php
    require_once __DIR__."/../classesAndFunctions/verifyReCaptcha.php";
    require_once __DIR__."/../classesAndFunctions/jwsTokenVerification.php";
    require_once __DIR__."/../classesAndFunctions/getUserType.php";
    require_once __DIR__."/../classesAndFunctions/groups.php";
    if(!verifyReCaptcha()){
        echo "RECAPTCHA verification failed";
       header("Location: ../../"); //getiing back to dashboard
        exit;
    }
    $JWTResult=getJWTAuthResult();
    if($JWTResult==null){
        echo "JWT AUTH FAILED";
       header('Location: ../'); //goto login page.
        exit;
    }
    $userTypeFetch=getUserType($JWTResult['payload']['sub']);
    if($userTypeFetch['status']=='FAILED'){
        echo "RECAPTCHA verification failed";
        header("Location: ../../"); //getiing back to dashboard
    }
    $userType=$userTypeFetch['result'];
    if($userType=="ADMINISTRATOR"){
        $result=addGroup($_POST['group-name']);
        //result is true or null in either case we are going back to dashboard.
        header("Location: ../../"); //getiing back to dashboard
        exit;
    }
    else{
        echo "USER IS NOT AN ADMINISTRATOR";
        removeJWTToken();
        header('Location: ../'); //goto login page.
        exit; 
    }
    


    
?>