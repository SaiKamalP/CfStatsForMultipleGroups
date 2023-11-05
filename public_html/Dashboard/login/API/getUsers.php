<?php
    require_once __DIR__."/../classesAndFunctions/jwsTokenVerification.php";
    require_once __DIR__."/../classesAndFunctions/getUsers.php";
    require_once __DIR__."/../classesAndFunctions/getUserType.php";
    header('Content-Type: application/json');

    function returnFailedStatus()
    {
        $responce = array(
            'status' => 'FAILED'
        );
        echo json_encode($responce);
        exit;
    }
    

    $jwtResult =getJWTAuthResult();
    if ($jwtResult==null) { returnFailedStatus();}

    $userTypeFetch=getUserType($jwtResult['payload']['sub']);
    if($userTypeFetch['status']=='FAILED'){
        returnFailedStatus();
    }
    $canGetNonAdminMembers=false; //This will prevent a normal user from getting all members of the gorups.
    $userType=$userTypeFetch['result'];
    if($userType=='ADMINISTRATOR' || $userType=='GROUP_ADMIN' ){
        $canGetNonAdminMembers=true;
    }

    $userDetailsArrayFetch = getUsersArray();
    if($userDetailsArrayFetch['status']=='FAILED'){
        returnFailedStatus();
    }
    $userDetailsArray=$userDetailsArrayFetch['result'];
    $responce=array("users"=>array());
    if(!isset($_GET['group_id']) || empty($_GET['group_id']) || $_GET['group_id']=='0'){
        foreach($userDetailsArray as $user){
            if($canGetNonAdminMembers || $user['user_type']=="ADMINISTRATOR" || $user['user_type']=="GROUP_ADMIN"){
                array_push($responce["users"],array(
                    "id"=>$user['id'],
                    "name"=>$user['name'],
                    "cf_handle"=>$user['cf_handle'],
                    "rating"=>$user['rating'],
                    "in_group"=>$user['in_group'],
                    "group_id"=>$user['group_id'],
                    "user_type"=>$user['user_type']
                ));
            }
            
        }        
    }
    else{
        $requestedGroupId=$_GET['group_id'];
        foreach($userDetailsArray as $user){
            if($canGetNonAdminMembers || $user['user_type']=="ADMINISTRATOR" || $user['user_type']=="GROUP_ADMIN"){
                if($user['group_id']==$requestedGroupId){
                    array_push($responce["users"],array(
                        "id"=>$user['id'],
                        "name"=>$user['name'],
                        "cf_handle"=>$user['cf_handle'],
                        "rating"=>$user['rating'],
                        "in_group"=>$user['in_group'],
                        "group_id"=>$user['group_id'],
                        "user_type"=>$user['user_type']
                    ));
                }
            }
        } 
    }
    $responce["status"]="SUCCESS";
    $jsonresponce=json_encode($responce);
    echo $jsonresponce;
    mysqli_close($conn);
    exit;

?>