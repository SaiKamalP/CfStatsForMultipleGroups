<?php
    require_once __DIR__."/../classesAndFunctions/verifyReCaptcha.php";
    require_once __DIR__."/../classesAndFunctions/jwsTokenVerification.php";
    require_once __DIR__."/../classesAndFunctions/getUserType.php";
    require_once __DIR__."/../classesAndFunctions/groups.php";
    if(!verifyReCaptcha()){
        header("Location: ../../?m=0"); //getiing back to dashboard
        exit;
    }
    $JWTResult=getJWTAuthResult();
    if($JWTResult==null){
        header('Location: ../?m=0'); //goto login page.
        exit;
    }
    $userhandle=$JWTResult['payload']['sub'];

    $group_id=$_POST['group_id'];
    $handleToBeDemotedFromAdministrator=$_POST['selected_handle'];

    $isAllowedToDemoteFromAdministrator=false;
    $userTypeFetch=getUserType($userhandle);
    if($userTypeFetch['status']=='FAILED'){
        header("Location: ../../group/?g=".$group_id."&m=0"); //getiing back to dashboard
        exit;
    }
    $userType=$userTypeFetch['result'];
    if($userType=='ADMINISTRATOR' && $userhandle!=$handleToBeDemotedFromAdministrator){
        $isAllowedToDemoteFromAdministrator=true;
    }
    
    if(!$isAllowedToDemoteFromAdministrator){
        header("Location: ../../group?g=".$group_id."&m=0");
        exit;
    }

    $demoteFromAdministratorFetch=demoteFromAdministrator($handleToBeDemotedFromAdministrator,$group_id);
    if($demoteFromAdministratorFetch['status']=='SUCCESS'){
        if($demoteFromAdministratorFetch['result']==true){
            header("Location: ../../group?g=".$group_id);
            exit;
        }
        else{
            header("Location: ../../group?g=".$group_id."&m=0");
            exit;
        }
    }
    else{
        header("Location: ../../group?g=".$group_id."&m=0");
        exit;
    }
    
    exit;
    
?>