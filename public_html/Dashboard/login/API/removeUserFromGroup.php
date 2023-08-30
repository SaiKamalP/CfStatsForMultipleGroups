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
    $handleToBeRemoved=$_POST['selected_handle'];

    $isAllowedToRemoveUserFromGroup=false;
    $userTypeFetch=getUserType($userhandle);
    if($userTypeFetch['status']=='FAILED'){
        header("Location: ../../group?g=".$group_id."&m=0"); //getiing back to group page
        exit;
    }
    $userType=$userTypeFetch['result'];
    if($userType=='ADMINISTRATOR'){
        $isAllowedToRemoveUserFromGroup=true;
    }
    else if($userType=='GROUP_ADMIN'){
        $isGroupAdminOfGroupIdFetch=isGroupAdmin($userhandle,$group_id);
        if($isGroupAdminOfGroupIdFetch['status']=='FAILED'){
            header("Location: ../../group?g=".$group_id."&m=0"); //getiing back to group page
            exit;
        }
        $isGroupAdminOfGroupId=$isGroupAdminOfGroupIdFetch['result'];
        if($isGroupAdminOfGroupId==true){
            $isAllowedToRemoveUserFromGroup=true;
        }
    }
    if(!$isAllowedToRemoveUserFromGroup){
        header("Location: ../../group?g=".$group_id."&m=0");
        exit;
    }

    $removeFromGroupFetch=removeUserFromGroup($handleToBeRemoved,$group_id);
    if($removeFromGroupFetch['status']=='SUCCESS'){
        if($removeFromGroupFetch['result']==true){
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
