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
    $handelToBeDemotedFromGroupAdmin=$_POST['selected_handle'];

    $isAllowedToDemoteGroupAdmin=false;
    $userTypeFetch=getUserType($userhandle);
    if($userTypeFetch['status']=='FAILED'){
        header("Location: ../../"); //getiing back to dashboard
        exit;
    }
    $userType=$userTypeFetch['result'];
    if($userType=='ADMINISTRATOR'){
        $isAllowedToDemoteGroupAdmin=true;
    }
    else if($userType=='GROUP_ADMIN'){
        $isGroupAdminOfGroupIdFetch=isGroupAdmin($userhandle,$group_id);
        if($isGroupAdminOfGroupIdFetch['status']=='FAILED'){
            header("Location: ../../"); //getiing back to dashboard
            exit;
        }
        $isGroupAdminOfGroupId=$isGroupAdminOfGroupIdFetch['result'];
        if($isGroupAdminOfGroupId==true){
            $isAllowedToDemoteGroupAdmin=true;
        }
    }
    if(!$isAllowedToDemoteGroupAdmin){
        header("Location: ../../");
        exit;
    }

    $demoteFromGroupAdminFetch=demoteFromGroupAdmin($handelToBeDemotedFromGroupAdmin,$group_id);
    if($demoteFromGroupAdminFetch['status']=='SUCCESS'){
        if($demoteFromGroupAdminFetch['result']==true){
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
