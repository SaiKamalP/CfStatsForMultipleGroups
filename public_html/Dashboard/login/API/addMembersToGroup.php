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
    $handlesListString=$_POST['members-list'];

    $isAllowdToAddMembers=false;
    $userTypeFetch=getUserType($userhandle);
    if($userTypeFetch['status']=='FAILED'){
        header("Location: ../../"); //getiing back to dashboard
        exit;
    }
    $userType=$userTypeFetch['result'];
    if($userType=='ADMINISTRATOR'){
        $isAllowdToAddMembers=true;
    }
    else if($userType=='GROUP_ADMIN'){
        $isGroupAdminOfGroupIdFetch=isGroupAdmin($userhandle,$group_id);
        if($isGroupAdminOfGroupIdFetch['status']=='FAILED'){
            header("Location: ../../"); //getiing back to dashboard
            exit;
        }
        $isGroupAdminOfGroupId=$isGroupAdminOfGroupIdFetch['result'];
        if($isGroupAdminOfGroupId==true){
            $isAllowdToAddMembers=true;
        }
    }
    if(!$isAllowdToAddMembers){
        header("Location: ../../");
        exit;
    }
    $handlesList=preg_split('/\s+/', $handlesListString, -1, PREG_SPLIT_NO_EMPTY);
    $ableToAddAllHandels=true;
    foreach($handlesList as $handle){
        if(isAValidName($handle)){
            $addUserToGroupFetch=addUserToGroup($handle,$group_id);
            if($addUserToGroupFetch['status']=='SUCCESS'){
                if($addUserToGroupFetch['result']){
                    echo "ADDED handle :".$handle."<br>";
                }
                else{
                    echo "Couldn't add handle : ".$handle." may be already in group or other group<br>";

                }

            }
            else{
                echo "Failed to add handle : ".$handle." from server side<br>";

            }
          
        }
        else{
            echo 'Not A Valid Handle : '.$handle.'<br>';
        }
    }
    exit;
    
?>