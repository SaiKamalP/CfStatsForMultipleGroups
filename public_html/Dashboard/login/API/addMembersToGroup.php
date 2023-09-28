<?php
    require_once __DIR__."/../classesAndFunctions/verifyReCaptcha.php";
    require_once __DIR__."/../classesAndFunctions/jwsTokenVerification.php";
    require_once __DIR__."/../classesAndFunctions/getUserType.php";
    require_once __DIR__."/../classesAndFunctions/groups.php";
    if(!verifyReCaptcha()){
        //recaptcha failed
        header("Location: ../../?m=0"); //getiing back to dashboard
        exit;
    }
    $JWTResult=getJWTAuthResult();
    if($JWTResult==null){
        //JWT auth failed
        header('Location: ../?m=0'); //goto login page.
        exit;
    }
    $userhandle=$JWTResult['payload']['sub'];

    $group_id=$_POST['group_id'];
    $handlesListString=$_POST['members-list'];

    $isAllowdToAddMembers=false;
    $userTypeFetch=getUserType($userhandle);
    if($userTypeFetch['status']=='FAILED'){
        header("Location: ../../group/?g=".$group_id."&m=0"); //getting back to groups page
        exit;
    }
    $userType=$userTypeFetch['result'];
    if($userType=='ADMINISTRATOR'){
        $isAllowdToAddMembers=true;
    }
    else if($userType=='GROUP_ADMIN'){
        $isGroupAdminOfGroupIdFetch=isGroupAdmin($userhandle,$group_id);
        if($isGroupAdminOfGroupIdFetch['status']=='FAILED'){
            header("Location: ../../group/?g=".$group_id."&m=0"); //getting back to groups page
        }
        $isGroupAdminOfGroupId=$isGroupAdminOfGroupIdFetch['result'];
        if($isGroupAdminOfGroupId==true){
            $isAllowdToAddMembers=true;
        }
    }
    if(!$isAllowdToAddMembers){
        header("Location: ../../group/?g=".$group_id."&m=0");
        exit;
    }
    $handlesList=preg_split('/\s+/', $handlesListString, -1, PREG_SPLIT_NO_EMPTY);
    $ableToAddAllHandels=true;
    foreach($handlesList as $handle){
        $handle=strtolower($handle);
        if(isAValidName($handle)){
            $addUserToGroupFetch=addUserToGroup($handle,$group_id);
            if($addUserToGroupFetch['status']=='SUCCESS'){
                if($addUserToGroupFetch['result']){
                    //able to successfully add the handle.
                }
                else{
                    echo "Couldn't add handle : ".$handle." may be already in group or other group or not registered<br>";
                    $ableToAddAllHandels=false;

                }

            }
            else{
                echo "Failed to add handle : ".$handle." from server side<br>";
                $ableToAddAllHandels=false;

            }
          
        }
        else{
            echo 'Not A Valid Handle : '.$handle.'<br>';
            $ableToAddAllHandels=false;
        }
    }
    if($ableToAddAllHandels){
        header("Location: ../../group?g=".$group_id);
        exit;
    }
    exit;
    
?>