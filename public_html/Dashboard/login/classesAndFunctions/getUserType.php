<?php
    require_once __DIR__."/dbAndOtherDetails.php";
    function getUserType($userHandle){
        global $host,$username,$password,$dbName;
        global $cfUsersTableName;
        try{
            $conn = mysqli_connect($host, $username, $password, $dbName);

            $query = "SELECT `user_type` FROM `" . $cfUsersTableName . "` WHERE `cf_handle`=?";
            $prepareStmt = mysqli_prepare($conn, $query);
            if (!$prepareStmt) {
                return array(
                    'status'=>'FAILED'
                );
            }

            $prepareStmt->bind_param("s", $userHandle);
            if (!$prepareStmt->execute()) {
                return array(
                    'status'=>'FAILED'
                );
            }

            $queryResult = $prepareStmt->get_result();
            if (!($queryResult->num_rows > 0)) {
                return array(
                    'status'=>'FAILED'
                );
            }

            $row = $queryResult->fetch_assoc();
            
            return array(
                'status'=>'SUCCESS',
                'result'=>$row['user_type']
            );
        }
        catch(Exception $e){
            return array(
                'status'=>'FAILED'
            );
        }
    }

?>