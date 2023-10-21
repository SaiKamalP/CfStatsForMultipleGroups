<?php
    require_once __DIR__."/../classesAndFunctions/verifyReCaptcha.php";
    require_once __DIR__."/../classesAndFunctions/jwsTokenVerification.php";
    require_once __DIR__."/../classesAndFunctions/getUserType.php";
    require_once __DIR__."/../classesAndFunctions/groups.php";

    if(!verifyReCaptcha()){
        header("Location: ../../?m=0"); //getting back to dashboard
         exit;
     }
     $JWTResult=getJWTAuthResult();
     if($JWTResult==null){
        header('Location: ../?m=0'); //goto login page.
         exit;
     }

    $userTypeFetch=getUserType($JWTResult['payload']['sub']);
    if($userTypeFetch['status']=='FAILED'){
        header("Location: ../../?m=0"); //getiing back to dashboard
        exit;
    }
    if(!isset($_POST['group_id']) || empty($_POST['group_id'])){
        header("Location: ../../"); //getting back to dashboard
        exit;
    }
    $userType=$userTypeFetch['result'];
    if($userType=="ADMINISTRATOR"){
        $result=removeGroup($_POST['group_id']);
        //result is true or null in either case we are going back to dashboard.
        header("Location: ../../"); //getting back to dashboard
        exit;
    }
    else{
        removeJWTToken();
        header('Location: ../?m=0'); //goto login page.
        exit; 
    }

?>