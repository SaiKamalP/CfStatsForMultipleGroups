<?php
    require_once __DIR__."/dbAndOtherDetails.php";
    function getUsersArray(){
        global $host,$username,$password,$dbName;
        global $cfUsersTableName,$cfGroupsTableName;
        try{
            $conn=mysqli_connect($host,$username,$password,$dbName);
            $responce=["users"=>array()];
            $query="SELECT `id`, `name`, `cf_handle`, `in_group`, `rating`, `user_type` FROM `".$cfUsersTableName."`";
            $groupsQuery="SELECT `id`, `name` FROM `".$cfGroupsTableName."`";
            $result=mysqli_query($conn,$query);
            $groupsResult=mysqli_query($conn,$groupsQuery);
            $groupmap=["0"=>"none"];
            foreach($groupsResult as $groupRow){
                $groupmap[$groupRow['id']]=$groupRow['name'];
            }
            $responce=array();
            foreach($result as $row){
                $group="none";
                if(array_key_exists($row['in_group'],$groupmap)){
                    $group=$groupmap[$row['in_group']];  
                }
                array_push($responce,array(
                    "id"=>$row['id'],
                    "name"=>$row['name'],
                    "cf_handle"=>$row['cf_handle'],
                    "rating"=>$row['rating'],
                    "in_group"=>$group,
                    "group_id"=>$row['in_group'],
                    "user_type"=>$row['user_type']
                ));
            }
            mysqli_close($conn);
            return array(
                'status'=>'SUCCESS',
                'result'=>$responce
            );
        }
        catch(Exception $e){
            
            return array(
                'status' => 'FAILED',
            );
        }
    }
    

?>