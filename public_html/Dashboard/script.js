
let isAdministrator=false;
async function fetchAndSetUserType(){
    const getUserTypeFetch=await fetch('login/API/getUserType.php');
    const getUserTypeJson=await getUserTypeFetch.json();
    if(getUserTypeJson['status']=='SUCCESS'){
        if(getUserTypeJson['user_type']=='ADMINISTRATOR'){
            isAdministrator=true;
        }
    }
    else{
        window.location.href='login/';
    }
}
fetchAndSetUserType();

document.querySelector('.add-group-btn').addEventListener('click',function(){
    if(isAdministrator){
        document.querySelector('.add-group-window-outer').style.display="block";
        
    }
    else{
        alert('You must be an administrator to perform this action.');
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
        group_tile_clone.addEventListener('click',function(){
            window.location.href="group?g="+groupId;
        });
        group_tiles_outer.appendChild(group_tile_clone);
    });
 }
