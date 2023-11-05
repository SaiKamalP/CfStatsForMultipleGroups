function setRecaptchKey(){
    fetch("login/API/getRecaptchaPublicKey.php").then(responce=>responce.json()).then(data=>{
      if(data.status=="SUCCESS"){
        document.getElementById('signup-btn').setAttribute('data-sitekey',data.result);
        document.getElementById('rename-group-form-submit-btn').setAttribute('data-sitekey',data.result);
        document.getElementById('remove-group-form-submit-btn').setAttribute('data-sitekey',data.result);
        const recaptchScriptElement=document.createElement("script");
        recaptchScriptElement.setAttribute("src","https://www.google.com/recaptcha/api.js");
        document.head.appendChild(recaptchScriptElement);
      }
      else{
        alert("can't process requests at the moment.");
      }
    });
  }
  setRecaptchKey();


let isAdministrator=false;
let isAdminOfSomeGroup=false;
async function fetchAndSetUserType(){
    const getUserTypeFetch=await fetch('login/API/getUserType.php');
    const getUserTypeJson=await getUserTypeFetch.json();
    if(getUserTypeJson['status']=='SUCCESS'){
        if(getUserTypeJson['user_type']=='ADMINISTRATOR'){
            isAdministrator=true;
            isAdminOfSomeGroup=true;
        }
        else if(getUserTypeJson['user_type']=='GROUP_ADMIN'){
            isAdminOfSomeGroup=true;
        }
    }
    else{
        window.location.href='login/';
    }
}
fetchAndSetUserType();

function showMessage(){
    const messageId=new URLSearchParams(window.location.search).get('m');
  
    switch(messageId){
      case '0':
        alert("Something went wrong. Please try again later");
        break;
      default:
        break;
    }
}
showMessage();

document.querySelector('.add-group-btn').addEventListener('click',function(){
    if(isAdministrator){
        document.querySelector('.add-group-window-outer').style.display="block";
        
    }
    else{
        alert('You must be an administrator to perform this action.');
    }
});

document.querySelector('.get-standings-btn').addEventListener('click',function(){
    if(isAdminOfSomeGroup){
        window.location.href="getStandings";
    }
    else{
        alert('You must be an administrator or a group admin to perform this action.');
    }
});

document.querySelector('.get-rating-changes-btn').addEventListener('click',function(){
    if(isAdminOfSomeGroup){
        window.location.href="getRatingChanges";
    }
    else{
        alert('You must be an administrator or a group admin to perform this action.');
    }
});

document.querySelector('.add-group-window-outer').addEventListener('click',function(){
    document.querySelector('.add-group-window-outer').style.display='none';
});
document.querySelector('.add-group-window').addEventListener('click',function(event){
    event.stopPropagation();
});

function onSubmitAddGroup(token) {
    document.getElementById("add-group-form").submit();
 }


 //loading groups details

 async function loadGroupTiles(){
    var groupsFetch=await (await fetch("login/API/getGroups.php")).json();
    if(groupsFetch['status']=='SUCCESS'){
        loadGroupTilesFromGroups(groupsFetch['groups']);
    }
    else{
        alert('Something went wrong in getting the groups');
    }
 }
 loadGroupTiles();
 function loadGroupTilesFromGroups(groups){
    const group_tiles_outer=document.querySelector('.group-tiles-outer');
    const group_tile_template=document.querySelector('.group-tile');
    groups.forEach(group => {
        const group_tile_clone=group_tile_template.cloneNode(true);
        group_tile_clone.className="group-tile";
        group_tile_clone.querySelector('.group-title').innerHTML=group['name'];
        group_tile_clone.querySelector('.group-description').innerHTML=group['description'];
        const groupId=group['id'];
        group_tile_clone.querySelector('.group-tile-options-btn-outer').addEventListener('click',function(event){
            event.stopPropagation();
            showGroupOptions(groupId);
        });
        group_tile_clone.addEventListener('click',function(){
            window.location.href="group?g="+groupId;
        });
        group_tiles_outer.appendChild(group_tile_clone);
    });
 }

let selectedGroupForOptions;
function showGroupOptions(group_id){
    if(isAdministrator){
        selectedGroupForOptions=group_id;
        document.querySelector('.group-options-window-outer').style.display="block";
    }
}
document.querySelector('.group-options-window-outer').addEventListener('click',function(){
    document.querySelector('.group-options-window-outer').style.display="none";
});
document.querySelector('.group-options-window').addEventListener('click',function(event){
    event.stopPropagation();
});
function onSubmitRemoveGroup(token){
    document.getElementById("remove-group-from-group-id").value=selectedGroupForOptions;
    document.getElementById("remove-group-form").submit();
}

function onSubmitRenameGroup(token){
    document.getElementById("rename-group-from-group-id").value=selectedGroupForOptions;
    document.getElementById("rename-group-form").submit();
}
