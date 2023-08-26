<?php

    require_once __DIR__."/../classesAndFunctions/jwsTokenVerification.php";
    require_once __DIR__."/../classesAndFunctions/groups.php";
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

    $groupsArrayFetch = getGroupBasicDeatilsArray();
    if($groupsArrayFetch['status']=='FAILED'){
        returnFailedStatus();
    }
    $groupsArray=$groupsArrayFetch['result'];
    $responce=array(
        'status'=>'SUCCESS',
        'groups'=>$groupsArray
    );
    echo json_encode($responce);
    exit;
    
?>