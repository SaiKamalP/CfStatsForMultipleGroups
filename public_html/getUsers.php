<?php
    header('Content-Type: application/json');

    $servername = "localhost";
    $username = "username";
    $password = "password";
    $dbName = "DBName";
    $cfUsersTableName="cfUsersTableName";
    try{
    $conn=mysqli_connect($servername,$username,$password,$dbName);
    }
    catch(Exception $e){
        echo $e->getMessage();
        die ("\nconenction-failed");
    }
    $responce=["users"=>array()];
    $query="SELECT `id`, `name`, `cf_handle`, `in_group` FROM `"+$cfUsersTableName+"`";
    $groupsQuery="SELECT `id`, `name` FROM `cfGroups`";
    $result=mysqli_query($conn,$query);
    $groupsResult=mysqli_query($conn,$groupsQuery);
    $groupmap=["0"=>"none"];
    foreach($groupsResult as $groupRow){
        $groupmap[$groupRow['id']]=$groupRow['name'];
    }
    foreach($result as $row){
        $group="none";
        if(array_key_exists($row['in_group'],$groupmap)){
          $group=$groupmap[$row['in_group']];  
        }
        array_push($responce["users"],[
            "id"=>$row['id'],
            "name"=>$row['name'],
            "cf_handle"=>$row['cf_handle'],
            "in_group"=>$group
        ]);
     }
    $jsonresponce=json_encode($responce);
    echo $jsonresponce;
    mysqli_close($conn);

?>