<?php
    require_once __DIR__."/dbAndOtherDetails.php";
    require_once __DIR__."/utilities.php";
    require_once __DIR__."/getUserType.php";
    function addGroup($groupName){
        if(!isAValidNameWithSpace($groupName)){
            return array(
                'status' => 'FAILED'
            );
        }
        global $host,$username,$password,$dbName;
        global $cfGroupsTableName;
        try{
            $conn = mysqli_connect($host, $username, $password, $dbName);

            $query = "INSERT INTO `".$cfGroupsTableName."`(`id`, `name`, `adminList`, `description`) VALUES (NULL, ?,'','')";
            
            $prepareStmt = mysqli_prepare($conn, $query);
            if (!$prepareStmt) {
                return array(
                    'status' => 'FAILED'
                );
            }

            $prepareStmt->bind_param("s", $groupName);
            if (!$prepareStmt->execute()) {
                return array(
                    'status' => 'FAILED'
                );
            }
            return array(
                'status' => 'SUCCESS'
            );
        }
        catch(Exception $e){
            return array(
                'status' => 'FAILED'
            );
        }
        return array(
            'status' => 'FAILED'
        );
    }

    /**
     * gets the groups id,name and discription
     */
    function getGroupBasicDeatilsArray(){
        global $host,$username,$password,$dbName;
        global $cfGroupsTableName;
        try{
            $conn = mysqli_connect($host, $username, $password, $dbName);

            $query = "SELECT * FROM `".$cfGroupsTableName."`";
            $prepareStmt = mysqli_prepare($conn, $query);
            if (!$prepareStmt) {
                return array(
                    'status' => 'FAILED'
                );
            }
            if (!$prepareStmt->execute()) {
                return array(
                    'status' => 'FAILED'
                );
            }
            $result=$prepareStmt->get_result();
            $responce=array();
            foreach($result as $row){
                array_push($responce,array(
                    'id'=>$row['id'],
                    'name'=>$row['name'],
                    'description'=>$row['description']
                ));
            }
            return array(
                'status' => 'SUCCESS',
                'result' => $responce
            );
        }
        catch(Exception $e){
            return array(
                'status' => 'FAILED'
            );
        }
        return array(
            'status' => 'FAILED'
        );
    }

    function isGroupAdmin($userHandle,$group_id){
        global $host,$username,$password,$dbName;
        global $cfGroupsTableName;
        try{
            $conn = mysqli_connect($host, $username, $password, $dbName);

            $query = "SELECT `adminList` FROM `".$cfGroupsTableName."` WHERE `id`=?";
            $prepareStmt = mysqli_prepare($conn, $query);
            if (!$prepareStmt) {
                return array(
                    'status' => 'FAILED'
                );
            }
            $prepareStmt->bind_param("i",$group_id);
            if (!$prepareStmt->execute()) {
                return array(
                    'status' => 'FAILED'
                );
            }
            $result=$prepareStmt->get_result();
            if($result->num_rows>0){
                $userIdFetch=getUserIdFromUserName($userHandle);
                if($userIdFetch['status']=='FAILED'){
                    return array(
                        'status' => 'FAILED'
                    );
                }
                $userId=$userIdFetch['result'];
                if($userId==null || empty($userId)){
                    return array(
                        'status' => 'FAILED'
                    );
                }
                $row=$result->fetch_assoc();
                $adminString=$row['adminList'];
                $adminArray=json_decode($adminString,true);
                if($adminArray==null){
                    $adminArray=array();
                }

                $isAdmin=false;
                foreach($adminArray as $adminId){
                    if($adminId==$userId){
                        $isAdmin=true;
                    }
                }
                return array(
                    'status' => 'SUCCESS',
                    'result' => $isAdmin
                );
            }
            else{
                return array(
                    'status' => 'FAILED'
                );
            }
            
        }
        catch(Exception $e){
            return array(
                'status' => 'FAILED'
            );
        }
        return array(
            'status' => 'FAILED'
        );
    }

    /** 
     * need to change the function location later
     */
    function getUserIdFromUserName($userHandle){
        global $host,$username,$password,$dbName;
        global $cfUsersTableName;
        try{
            $conn = mysqli_connect($host, $username, $password, $dbName);

            $query = "SELECT `id` FROM `" . $cfUsersTableName . "` WHERE `cf_handle`=?";
            $prepareStmt = mysqli_prepare($conn, $query);
            if (!$prepareStmt) {
                return array(
                    'status' => 'FAILED'
                );
            }

            $prepareStmt->bind_param("s", $userHandle);
            if (!$prepareStmt->execute()) {
                return array(
                    'status' => 'FAILED'
                );
            }

            $queryResult = $prepareStmt->get_result();
            if (!($queryResult->num_rows > 0)) {
                return array(
                    'status' => 'FAILED'
                );
            }

            $row = $queryResult->fetch_assoc();
            return array(
                'status' => 'SUCCESS',
                'result' => $row['id']
            );
        }
        catch(Exception $e){
            return array(
                'status' => 'FAILED'
            );
        }
    }

    function addUserToGroup($userHandle,$group_id){
        global $host,$username,$password,$dbName;
        global $cfUsersTableName;
        try{
            $conn = mysqli_connect($host, $username, $password, $dbName);

            $query = "SELECT `in_group` FROM `" . $cfUsersTableName . "` WHERE `cf_handle`=?";
            $prepareStmt = mysqli_prepare($conn, $query);
            if (!$prepareStmt) {
                return array(
                    'status' => 'FAILED'
                );
            }

            $prepareStmt->bind_param("s", $userHandle);
            if (!$prepareStmt->execute()) {
                return array(
                    'status' => 'FAILED'
                );
            }

            $queryResult = $prepareStmt->get_result();
            if (!($queryResult->num_rows > 0)) {
                return array(
                    'status' => 'SUCCESS',
                    'result' => false
                );
            }

            $row = $queryResult->fetch_assoc();
            if($row['in_group']!='0'){
                return array(
                    'status' => 'SUCCESS',
                    'result' => false
                );
            }

            $query = "UPDATE `".$cfUsersTableName."` SET `in_group`=? WHERE `cf_handle`=?";
            $prepareStmt = mysqli_prepare($conn, $query);
            if (!$prepareStmt) {
                return array(
                    'status' => 'FAILED'
                );
            }
            $prepareStmt->bind_param("is", $group_id,$userHandle);
            if (!$prepareStmt->execute()) {
                return array(
                    'status' => 'FAILED'
                );
            }
            return array(
                'status' => 'SUCCESS',
                'result' => true
            );

        }
        catch(Exception $e){
            return array(
                'status' => 'FAILED'
            );
        }
    }

    function removeUserFromGroup($userHandle,$group_id){
        $demoteFromGroupAdminFetch =demoteFromGroupAdmin($userHandle,$group_id);
        if($demoteFromGroupAdminFetch['status']=='FAILED'){
            return array(
                'status' => 'FAILED'
            );
        }
        if($demoteFromGroupAdminFetch['result']==false){
            return array(
                'status' => 'FAILED'
            );
        }
        global $host,$username,$password,$dbName;
        global $cfUsersTableName;
        try{
            $conn = mysqli_connect($host, $username, $password, $dbName);

            $query = "SELECT `in_group` FROM `" . $cfUsersTableName . "` WHERE `cf_handle`=?";
            $prepareStmt = mysqli_prepare($conn, $query);
            if (!$prepareStmt) {
                return array(
                    'status' => 'FAILED'
                );
            }

            $prepareStmt->bind_param("s", $userHandle);
            if (!$prepareStmt->execute()) {
                return array(
                    'status' => 'FAILED'
                );
            }

            $queryResult = $prepareStmt->get_result();
            if (!($queryResult->num_rows > 0)) {
                return array(
                    'status' => 'FAILED'
                );
            }

            $row = $queryResult->fetch_assoc();
            if($row['in_group']!=$group_id){
                return array(
                    'status' => 'SUCCESS',
                    'result' => false
                );
            }

            $query = "UPDATE `".$cfUsersTableName."` SET `in_group`=0 WHERE `cf_handle`=?";
            $prepareStmt = mysqli_prepare($conn, $query);
            if (!$prepareStmt) {
                return array(
                    'status' => 'FAILED'
                );
            }
            $prepareStmt->bind_param("s",$userHandle);
            if (!$prepareStmt->execute()) {
                return array(
                    'status' => 'FAILED'
                );
            }
            return array(
                'status' => 'SUCCESS',
                'result' => true
            );

        }
        catch(Exception $e){
            return array(
                'status' => 'FAILED'
            );
        }
    }

    function promoteToGroupAdmin($userHandle,$group_id){
        $userTypeFetch=getUserType($userHandle);
        if($userTypeFetch['status']=='FAILED'){
            return array(
                'status' => 'FAILED'
            );
        }
        $userType=$userTypeFetch['result'];
        if($userType=="ADMINISTRATOR"){
            return array(
                'status' => 'SUCCESS',
                'result' => true
            );
        }
        $isHeAlreadAdminOfGroupFetch=isGroupAdmin($userHandle,$group_id);
        if($isHeAlreadAdminOfGroupFetch['status']=='FAILED'){
            return array(
                'status' => 'FAILED'
            );
        }
        $isHeAlreadAdminOfGroup=$isHeAlreadAdminOfGroupFetch['result'];
        if($isHeAlreadAdminOfGroup==true){
            return array(
                'status' => 'SUCCESS',
                'result' => true
            );
        }
        
        global $host,$username,$password,$dbName;
        global $cfGroupsTableName,$cfUsersTableName;
        try{
            $conn = mysqli_connect($host, $username, $password, $dbName);

            $query = "SELECT `adminList` FROM `".$cfGroupsTableName."` WHERE `id`=?";
            $prepareStmt = mysqli_prepare($conn, $query);
            if (!$prepareStmt) {
                return array(
                    'status' => 'FAILED'
                );
            }
            $prepareStmt->bind_param("i",$group_id);
            if (!$prepareStmt->execute()) {
                return array(
                    'status' => 'FAILED'
                );
            }
            $result=$prepareStmt->get_result();
            if($result->num_rows>0){
                $userIdFetch=getUserIdFromUserName($userHandle);
                if($userIdFetch['status']=='FAILED'){
                    return array(
                        'status' => 'FAILED'
                    );
                }
                $userId=$userIdFetch['result'];
                if($userId==null || empty($userId)){
                    return array(
                        'status' => 'FAILED'
                    );
                }
                $row=$result->fetch_assoc();
                $adminString=$row['adminList'];

                $adminArray=json_decode($adminString,true);
                if($adminArray==null){
                    $adminArray=array();
                }
                array_push($adminArray,$userId);
                $adminString=json_encode($adminArray);
                $query="UPDATE `".$cfGroupsTableName."` SET `adminList`=? WHERE `id`=?";
                $prepareStmt=mysqli_prepare($conn,$query);
                if (!$prepareStmt) {
                    return array(
                        'status' => 'FAILED'
                    );
                }
                $prepareStmt->bind_param("si",$adminString,$group_id);
                if (!$prepareStmt->execute()) {
                    return array(
                        'status' => 'FAILED'
                    );
                }

                $query="UPDATE `".$cfUsersTableName."` SET `user_type`='GROUP_ADMIN' WHERE `id`=?";
                $prepareStmt=mysqli_prepare($conn,$query);
                if (!$prepareStmt) {
                    return array(
                        'status' => 'FAILED'
                    );
                }
                $prepareStmt->bind_param("i",$userId);
                if (!$prepareStmt->execute()) {
                    return array(
                        'status' => 'FAILED'
                    );
                }
                return array(
                    'status' => 'SUCCESS',
                    'result' =>true
                );
                
            }
            else{
                return array(
                    'status' => 'FAILED'
                );
            }
            
        }
        catch(Exception $e){
            return array(
                'status' => 'FAILED'
            );
        }
        return array(
            'status' => 'FAILED'
        );
        
    }

    function demoteFromGroupAdmin($userHandle,$group_id){
        $userTypeFetch=getUserType($userHandle);
        if($userTypeFetch['status']=='FAILED'){
            return array(
                'status' => 'FAILED'
            );
        }
        $userType=$userTypeFetch['result'];
        if($userType=="ADMINISTRATOR"){
            return array(
                'status' => 'SUCCESS',
                'result' => true
            );
        }
        $isHeAdminOfGroupFetch=isGroupAdmin($userHandle,$group_id);
        if($isHeAdminOfGroupFetch['status']=='FAILED'){
            return array(
                'status' => 'FAILED'
            );
        }
        $isHeAdminOfGroup=$isHeAdminOfGroupFetch['result'];
        if($isHeAdminOfGroup==true){
            global $host,$username,$password,$dbName;
            global $cfGroupsTableName,$cfUsersTableName;
            try{
                $conn = mysqli_connect($host, $username, $password, $dbName);

                $query = "SELECT `adminList` FROM `".$cfGroupsTableName."` WHERE `id`=?";
                $prepareStmt = mysqli_prepare($conn, $query);
                if (!$prepareStmt) {
                    return array(
                        'status' => 'FAILED'
                    );
                }
                $prepareStmt->bind_param("i",$group_id);
                if (!$prepareStmt->execute()) {
                    return array(
                        'status' => 'FAILED'
                    );
                }
                $result=$prepareStmt->get_result();
                if($result->num_rows>0){
                    $userIdFetch=getUserIdFromUserName($userHandle);
                    if($userIdFetch['status']=='FAILED'){
                        return array(
                            'status' => 'FAILED'
                        );
                    }
                    $userId=$userIdFetch['result'];
                    if($userId==null || empty($userId)){
                        return array(
                            'status' => 'FAILED'
                        );
                    }
                    $row=$result->fetch_assoc();
                    $adminString=$row['adminList'];

                    $adminArray=json_decode($adminString,true);
                    if($adminArray==null){
                        $adminArray=array();
                    }
                    $newAdminArray=array();
                    foreach($adminArray as $adminId){
                        if($adminId!=$userId){
                            array_push($newAdminArray,$adminId);
                        }
                    }
                    $adminString=json_encode($newAdminArray);
                    $query="UPDATE `".$cfGroupsTableName."` SET `adminList`=? WHERE `id`=?";
                    $prepareStmt=mysqli_prepare($conn,$query);
                    if (!$prepareStmt) {
                        return array(
                            'status' => 'FAILED'
                        );
                    }
                    $prepareStmt->bind_param("si",$adminString,$group_id);
                    if (!$prepareStmt->execute()) {
                        return array(
                            'status' => 'FAILED'
                        );
                    }

                    $query="UPDATE `".$cfUsersTableName."` SET `user_type`='NORMAL' WHERE `id`=?";
                    $prepareStmt=mysqli_prepare($conn,$query);
                    if (!$prepareStmt) {
                        return array(
                            'status' => 'FAILED'
                        );
                    }
                    $prepareStmt->bind_param("i",$userId);
                    if (!$prepareStmt->execute()) {
                        return array(
                            'status' => 'FAILED'
                        );
                    }
                    return array(
                        'status' => 'SUCCESS',
                        'result' =>true
                    );
                    
                }
                else{
                    return array(
                        'status' => 'FAILED'
                    );
                }
                
            }
            catch(Exception $e){
                return array(
                    'status' => 'FAILED'
                );
            }
            return array(
                'status' => 'FAILED'
            );
        }
        else{
            return array(
                'status' => 'SUCCESS',
                'result' => true
            );
        }
        
        
    }
    function promoteToAdministrator($userHandle,$group_id){
        $demoteFromGroupAdminFetch =demoteFromGroupAdmin($userHandle,$group_id);
        if($demoteFromGroupAdminFetch['status']=='FAILED'){
            return array(
                'status' => 'FAILED'
            );
        }
        if($demoteFromGroupAdminFetch['result']==false){
            return array(
                'status' => 'FAILED'
            );
        }
        global $host,$username,$password,$dbName;
        global $cfUsersTableName;
        $conn = mysqli_connect($host, $username, $password, $dbName);

        $query = "UPDATE `".$cfUsersTableName."` SET `user_type`='ADMINISTRATOR' WHERE `cf_handle`=?";
        $prepareStmt = mysqli_prepare($conn, $query);
        if (!$prepareStmt) {
            return array(
                'status' => 'FAILED'
            );
        }

        $prepareStmt->bind_param("s", $userHandle);
        if (!$prepareStmt->execute()) {
            return array(
                'status' => 'FAILED'
            );
        }
        return array(
            'status' => 'SUCCESS',
            'result' => true
        );
    }

    function demoteFromAdministrator($userHandle){
        global $host,$username,$password,$dbName;
        global $cfUsersTableName;
        $conn = mysqli_connect($host, $username, $password, $dbName);

        $query = "UPDATE `".$cfUsersTableName."` SET `user_type`='NORMAL' WHERE `cf_handle`=?";
        $prepareStmt = mysqli_prepare($conn, $query);
        if (!$prepareStmt) {
            return array(
                'status' => 'FAILED'
            );
        }

        $prepareStmt->bind_param("s", $userHandle);
        if (!$prepareStmt->execute()) {
            return array(
                'status' => 'FAILED'
            );
        }
        return array(
            'status' => 'SUCCESS',
            'result' => true
        );
    }


?>