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
    $userhandle=$JWTResult['payload']['sub'];

    $group_id=$_POST['group_id'];
    $handleToBePromoted=$_POST['selected_handle'];

    $isAllowedToPromoteToAdministrator=false;
    $userTypeFetch=getUserType($userhandle);
    if($userTypeFetch['status']=='FAILED'){
        header("Location: ../../"); //getiing back to dashboard
        exit;
    }
    $userType=$userTypeFetch['result'];
    if($userType=='ADMINISTRATOR'){
        $isAllowedToPromoteToAdministrator=true;
    }
    
    if(!$isAllowedToPromoteToAdministrator){
        header("Location: ../../");
        exit;
    }

    $promoteToAdministratorFetch=promoteToAdministrator($handleToBePromoted,$group_id);
    if($promoteToAdministratorFetch['status']=='SUCCESS'){
        if($promoteToAdministratorFetch['result']==true){
            header("Location: ../../");
            exit;
        }
        else{
            header("Location: ../../");
            exit;
        }
    }
    else{
        header("Location: ../../");
        exit;
    }
    
    exit;
    
?>