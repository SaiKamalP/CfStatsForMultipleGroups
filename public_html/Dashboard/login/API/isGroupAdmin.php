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
    if ($jwtResult==null) { returnFailedStatus(1);}

    if(!isset($_GET['group_id']) || empty($_GET['group_id'])){returnFailedStatus(2);}
    
    $isHeAdminOfGroupFetch=isGroupAdmin($jwtResult['payload']['sub'],$_GET['group_id']);
    if($isHeAdminOfGroupFetch['status']=='FAILED'){
        returnFailedStatus();
    }
    $isHeAdminOfGroup=$isHeAdminOfGroupFetch['result'];

    $responce = array(
        'status' => 'SUCCESS',
        'isGroupAdmin'=>$isHeAdminOfGroup
    );
    echo json_encode($responce);
    exit;



?>