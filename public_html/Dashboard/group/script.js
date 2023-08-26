
const group_id=new URLSearchParams(window.location.search).get('g');

let isGroupAdmin=false;
let isAdministrator=false;
async function fetchAndSetUserType(){
    const getUserTypeFetch=await fetch('../login/API/getUserType.php');
    const getUserTypeJson=await getUserTypeFetch.json();
    if(getUserTypeJson['status']=='SUCCESS'){
        if(getUserTypeJson['user_type']=='ADMINISTRATOR'){
            isAdministrator=true;
            isGroupAdmin=true;
        }
        else{
            const getIsUserAdminJson=await (await fetch("../login/API/isGroupAdmin.php?group_id="+group_id)).json();
            if(getIsUserAdminJson['status']=='SUCCESS'){
                if(getIsUserAdminJson['isGroupAdmin']==true){
                    isGroupAdmin=true;
                }
            }
            else{
                window.location.href='login/';
            }
        }
    }
    else{
        window.location.href='login/';
    }
}
fetchAndSetUserType();


document.querySelector('.add-member-btn').addEventListener('click',function(){
    if(isGroupAdmin){
        document.querySelector('.add-members-window-outer').style.display='block';
    }
    else{
        alert("ONLY GROUP ADMINS/ADMINISTRATORS CAN PERFORM THIS OPERATION.");
    }
});
document.querySelector('.add-members-window-outer').addEventListener('click',function(){

    document.querySelector('.add-members-window-outer').style.display='none';
});
document.querySelector('.add-members-window').addEventListener('click',function(event){
    event.stopPropagation();
});

function onSubmitAddMembers(token){
    document.getElementById('add-members-form-group-id').value=group_id;
    document.getElementById('add-members-form').submit();
}



async function loadData(){
    
    const groupUsersfetch=await (await fetch("../login/API/getUsers.php?group_id="+group_id)).json();
    if(groupUsersfetch['status']=='SUCCESS'){
        loadMembersFormUsers(groupUsersfetch['users']);
    }
    else{
        alert("Something went wrong");
        return;
    }

}
loadData();
function loadMembersFormUsers(users){
    const members_container=document.querySelector('.members-container');
    const members_row_template=document.querySelector('.member-row-outer');
    let sn=1;
    users.forEach(user => {
        const members_row_clone=members_row_template.cloneNode(true);
        members_row_clone.className="member-row-outer";
        members_row_clone.querySelector(".member-row-sn").innerHTML=sn;
        members_row_clone.querySelector(".member-row-handle").innerHTML=user['cf_handle'];
        members_row_clone.querySelector(".member-row-rating-name").innerHTML=getLevel(user['rating']);
        members_row_clone.querySelector(".member-row-rating").innerHTML=user['rating'];
        if(user['user_type']!="NORMAL"){
            members_row_clone.querySelector(".member-row-member-type").innerHTML=user['user_type'];
        }
        else{
            members_row_clone.querySelector(".member-row-member-type").innerHTML="";

        }
        const userIdFinal=user['id'];
        const userHandelFinal=user['cf_handle'];
        members_row_clone.querySelector(".member-row-options-btn").addEventListener('click',function(){
            showOptionsForUser(userIdFinal,userHandelFinal);
        });
        sn++;
        members_container.appendChild(members_row_clone);
    });
}



function getLevel(x){
    if(x<1200){
        return "Newbie";
    }
    if(x<1400){
        return 'Pupil';
    }
    if(x<1600){
        return 'Specialist';
    }
    if(x<1900){
        return 'Expert';

    }
    if(x<2100){
        return 'Candidate Master';

    }
    if(x<2300){
        return 'Master';

    }
    if(x<2400){
        return 'International Master';

    }
    if(x<2600){
        return 'Grandmaster';

    }
    if(x<3000){
        return 'International Grandmaster';

    }
    return 'Legendary Grandmaster';
}

let selectedUserId,selectedUserHandle;
function showOptionsForUser(user_id,userHandle){
    selectedUserHandle=userHandle;
    selectedUserId=user_id;
    document.querySelector('.user-options-window-outer').style.display='block';
    
}

function promoteToGroupAdmin(token){
    document.getElementById('user-option-promote-to-group-admin-group-id').value=group_id;
    document.getElementById('user-option-promote-to-group-admin-selected-handle').value=selectedUserHandle;
    
    document.getElementById('user-option-promote-to-group-admin').submit();
}
function demoteFromGroupAdmin(token){
    document.getElementById('user-option-demote-from-group-admin-group-id').value=group_id;
    document.getElementById('user-option-demote-from-group-admin-selected-handle').value=selectedUserHandle;
    
    document.getElementById('user-option-demote-from-group-admin').submit();
 
}
function removeFromGroup(token){
    document.getElementById('user-option-remove-from-group-group-id').value=group_id;
    document.getElementById('user-option-remove-from-group-selected-handle').value=selectedUserHandle;
    
    document.getElementById('user-option-remove-from-group').submit();

}
function promoteToAdministrator(token){
    document.getElementById('user-option-promote-to-administrator-group-id').value=group_id;
    document.getElementById('user-option-promote-to-administrator-selected-handle').value=selectedUserHandle;
    
    document.getElementById('user-option-promote-to-administrator').submit();

}
function demoteFromAdministrator(token){
    document.getElementById('user-option-demote-from-administrator-group-id').value=group_id;
    document.getElementById('user-option-demote-from-administrator-selected-handle').value=selectedUserHandle;
    
    document.getElementById('user-option-demote-from-administrator').submit();

}

document.querySelector('.user-options-window-outer').addEventListener('click',function(){

    document.querySelector('.user-options-window-outer').style.display='none';
});
document.querySelector('.user-options-window').addEventListener('click',function(event){
    event.stopPropagation();
});
