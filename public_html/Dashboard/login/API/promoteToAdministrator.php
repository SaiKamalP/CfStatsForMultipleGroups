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
    $handleToBePromoted=$_POST['selected_handle'];

    $isAllowedToPromoteToAdministrator=false;
    $userTypeFetch=getUserType($userhandle);
    if($userTypeFetch['status']=='FAILED'){
        header("Location: ../../group?g=".$group_id."&m=0"); //getiing back to group page
        exit;
    }
    $userType=$userTypeFetch['result'];
    if($userType=='ADMINISTRATOR'){
        $isAllowedToPromoteToAdministrator=true;
    }
    
    if(!$isAllowedToPromoteToAdministrator){
        header("Location: ../../group?g=".$group_id."&m=0");
        exit;
    }

    $promoteToAdministratorFetch=promoteToAdministrator($handleToBePromoted,$group_id);
    if($promoteToAdministratorFetch['status']=='SUCCESS'){
        if($promoteToAdministratorFetch['result']==true){
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