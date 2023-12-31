function setAllA4Width(){
    var width=document.querySelector('body').clientWidth;
    var element=document.querySelectorAll('.A4-page');
    for(var i=0;i<element.length;i++){
        element[i].style.width=width+'px';
    }
}
function clearPreviousPdf(){
    document.querySelectorAll('.dynamicElement').forEach(element=>{
        element.remove();
    });
}
function getPdfRatingChanges(contestId){
    clearPreviousPdf();
    fetch("../login/API/getUsers.php").then(responce=>responce.json()).then(data=>{
        if(data['status']=='FAILED'){
            return;
        }
        let users=[];
        data.users.forEach(usr=>{
            if(selectedEveryone || (usr.group_id>0 && selectedAllGroups) || (selectedGroupIds.indexOf(parseInt(usr.group_id,10))!=-1)){
                users.push(usr);
            }
        });
        let cfusersStringForCFAPI="";
        users.forEach(usr => {
            cfusersStringForCFAPI+=usr.cf_handle+';';
        });
        if(cfusersStringForCFAPI==""){
            return;
        }
        
        var cfAPICallString="https://codeforces.com/api/contest.ratingChanges?contestId="+contestId;
        fetch(cfAPICallString).then(responce=>responce.json()).then(async data2=>{
            const cfRatingChanges=data2.result;
            //contest data filling
            cfRatingChanges.forEach(row=>{
                row.handle=row.handle.toLowerCase();
            });
            document.querySelector('.first-page-contest-name-outer').innerHTML=cfRatingChanges[0].contestName;
            document.querySelector('.first-page-contest-date-outer').innerHTML=selectedContestDate;//getDateString(cfRatingChanges.contest.startTimeSeconds);
            document.querySelector('title').textContent=cfRatingChanges[0].contestName+" Rating Changes report";
            let groupParticipantsCount=new Map();
            let participantToGroupMap=new Map();
            users.forEach(usr=>{
                participantToGroupMap.set(usr.cf_handle,usr.in_group);
                if(!(usr.in_group=="none")){
                    if(!groupParticipantsCount.has(usr.in_group)){
                        groupParticipantsCount.set(usr.in_group,0);
                    }
                }
            });
            let requiredhandlesList=[];
            users.forEach(usr=>{
                requiredhandlesList.push(usr.cf_handle);
            });
            // console.log(cfRatingChanges);
            //group counting( 0 initialized above)
            cfRatingChanges.forEach(row=>{
                if(requiredhandlesList.indexOf(row.handle)!=-1){
                    if(participantToGroupMap.get(row.handle)!="none"){
                        groupParticipantsCount.set(participantToGroupMap.get(row.handle),groupParticipantsCount.get(participantToGroupMap.get(row.handle))+1);
                    }
                }
            });
            const groupParticipantsCountArray=Array.from(groupParticipantsCount);
            groupParticipantsCountArray.sort((a,b)=>b[1]-a[1]);
            const section_2_container_outer=document.querySelector('.section-2-continer-outer');
            const section_2_row_template=document.querySelector('.section-2-row-outer');
            groupParticipantsCountArray.forEach((a,i)=>{
                const section_2_row_clone=section_2_row_template.cloneNode(true);
                section_2_row_clone.className="section-2-row-outer dynamicElement";
                section_2_row_clone.querySelector('.section-2-row-b1').textContent=i+1;
                section_2_row_clone.querySelector('.section-2-row-b2').textContent=a[0];
                section_2_row_clone.querySelector('.section-2-row-b3').textContent=a[1];
                section_2_container_outer.appendChild(section_2_row_clone);
            });
    
    
            //standings
    
            const contestantsRatingsCall=await fetch("https://codeforces.com/api/user.info?handles="+cfusersStringForCFAPI);
            const contestantsRatingsresult=(await contestantsRatingsCall.json());
            const participantToRatingMap=new Map();
            contestantsRatingsresult.result.forEach(person=>{
                person.handle=person.handle.toLowerCase();
                participantToRatingMap.set(person.handle,0);
                if(person.hasOwnProperty("rating")){
                    participantToRatingMap.set(person.handle,person.rating);
                }
            });
    
            const section_3_page=document.querySelector('.standings-template').cloneNode(true);
            section_3_page.className="A4-width dynamicElement";
            const section_3_container_outer=section_3_page.querySelector('.section-3-container-outer');
            const section_3_row_template=section_3_page.querySelector('.section-3-row-outer');
            let count=0;
            cfRatingChanges.forEach(row=>{
                if(requiredhandlesList.indexOf(row.handle)!=-1){
                    count++;
                    const section_3_row_clone=section_3_row_template.cloneNode(true);
                    section_3_row_clone.className="section-3-row-outer";
                    section_3_row_clone.querySelector('.section-3-row-b1').textContent=count+"("+row.rank+")";
                    if(participantToGroupMap.get(row.handle)!="none"){
                        section_3_row_clone.querySelector('.section-3-row-b2').textContent=participantToGroupMap.get(row.handle);
                    }
                    else{
                        section_3_row_clone.querySelector('.section-3-row-b2').textContent="";
                    }
                    section_3_row_clone.querySelector('.section-3-row-b3').textContent=row.handle;
                    section_3_row_clone.querySelector('.section-3-row-b3').style.color=getColor(participantToRatingMap.get(row.handle));
                    section_3_row_clone.querySelector('.section-3-row-b4').textContent='+'+(row.newRating-row.oldRating);
                    if(row.newRating-row.oldRating<0){
                        section_3_row_clone.querySelector('.section-3-row-b4').textContent=(row.newRating-row.oldRating);
                        section_3_row_clone.querySelector('.section-3-row-b4').style.color="red";
                    }
                    section_3_row_clone.querySelector('.section-3-row-b5').textContent=row.oldRating;
                    section_3_row_clone.querySelector('.section-3-row-b5').style.color=getColor(row.oldRating);
                    section_3_row_clone.querySelector('.section-3-row-b7').textContent=row.newRating;
                    section_3_row_clone.querySelector('.section-3-row-b7').style.color=getColor(row.newRating);
                    if(getColor(row.oldRating)!=getColor(row.newRating)){
                        section_3_row_clone.querySelector('.section-3-row-b8').textContent="Became "+getLevel(row.newRating);
                        section_3_row_clone.querySelector('.section-3-row-b8').style.color=getColor(row.newRating);
                    }
                    section_3_container_outer.appendChild(section_3_row_clone);
            }
            });
            document.querySelector('.A4-pages').appendChild(section_3_page);
            const pagebreak=document.createElement("div");
            pagebreak.className="non-print display-gap dynamicElement";
            document.querySelector('.A4-pages').appendChild(pagebreak);
        
            if(groupParticipantsCount.size>1){
                groupParticipantsCountArray.forEach(a=>{
                    //if people participated in the group is greater than 0 only then we will we creating a new page for them.
                    if(a[1]>0){
                        const section_3_page=document.querySelector('.standings-template').cloneNode(true);
                        section_3_page.className="A4-width  dynamicElement";
                        section_3_page.querySelector('.section-3-heading-outer').querySelector('p').textContent=a[0];
                        const section_3_container_outer=section_3_page.querySelector('.section-3-container-outer');
                        const section_3_row_template=section_3_page.querySelector('.section-3-row-outer');
                        let count=0;
                        cfRatingChanges.forEach(row=>{
                            if((requiredhandlesList.indexOf(row.handle)!=-1)&& (a[0]==participantToGroupMap.get(row.handle))){
                                count++;
                                const section_3_row_clone=section_3_row_template.cloneNode(true);
                                section_3_row_clone.className="section-3-row-outer";
                                section_3_row_clone.querySelector('.section-3-row-b1').textContent=count+"("+row.rank+")";
                                if(participantToGroupMap.get(row.handle)!="none"){
                                    section_3_row_clone.querySelector('.section-3-row-b2').textContent=participantToGroupMap.get(row.handle);
                                }
                                else{
                                    section_3_row_clone.querySelector('.section-3-row-b2').textContent="";
                                }
                                section_3_row_clone.querySelector('.section-3-row-b3').textContent=row.handle;
                                section_3_row_clone.querySelector('.section-3-row-b3').style.color=getColor(participantToRatingMap.get(row.handle));
                                section_3_row_clone.querySelector('.section-3-row-b4').textContent='+'+(row.newRating-row.oldRating);
                                if(row.newRating-row.oldRating<0){
                                    section_3_row_clone.querySelector('.section-3-row-b4').textContent=(row.newRating-row.oldRating);
                                    section_3_row_clone.querySelector('.section-3-row-b4').style.color="red";
                                }
                                section_3_row_clone.querySelector('.section-3-row-b5').textContent=row.oldRating;
                                section_3_row_clone.querySelector('.section-3-row-b5').style.color=getColor(row.oldRating);
                                section_3_row_clone.querySelector('.section-3-row-b7').textContent=row.newRating;
                                section_3_row_clone.querySelector('.section-3-row-b7').style.color=getColor(row.newRating);
                                if(getColor(row.oldRating)!=getColor(row.newRating)){
                                    section_3_row_clone.querySelector('.section-3-row-b8').textContent="Became "+getLevel(row.newRating);
                                    section_3_row_clone.querySelector('.section-3-row-b8').style.color=getColor(row.newRating);
                                }
                                
            
                                section_3_container_outer.appendChild(section_3_row_clone);
                        }
                        });
                        document.querySelector('.A4-pages').appendChild(section_3_page);
                        const pagebreak=document.createElement("div");
                        pagebreak.className="non-print display-gap dynamicElement";
                        document.querySelector('.A4-pages').appendChild(pagebreak);
                    
                
                    }
                    
                });
            }
           
            setTimeout(function(){
                window.print();
            },3000);

        });
    });
    
}
// function temporarlyAskForContestId(){
//     const contest_id=prompt("Enter the contest ID.");
//     getPdfRatingChanges(contest_id);

// }

// temporarlyAskForContestId();


function getColor(x){
    if(x<1200){
        return 'rgb(128,128,128)';
    }
    if(x<1400){
        return 'rgb(0,128,0)';
    }
    if(x<1600){
        return 'rgb(3,168,158)';
    }
    if(x<1900){
        return 'rgb(0,0,255)';

    }
    if(x<2100){
        return 'rgb(170,0,170)';

    }
    if(x<2300){
        return 'rgb(254,152,12)';

    }
    if(x<2400){
        return 'rgb(255,141,36)';

    }
    if(x<2600){
        return 'rgba(255,0,0,255)';

    }
    return 'rgb(239,35,36)';
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

function getTimeString(time){
    const date = new Date(time*1000);
    const formattedTime = date.toLocaleTimeString('en-US', { hour: 'numeric', minute: 'numeric' });

    return formattedTime;
}

function getDateString(time){
    const date = new Date(time*1000);   
    const day = date.getUTCDate().toString().padStart(2, '0'); // Get the day and pad with leading zeros if necessary
    const month = (date.getUTCMonth() + 1).toString().padStart(2, '0'); // Get the month (zero-based) and add 1, then pad with leading zeros if necessary
    const year = date.getUTCFullYear();

    const formattedDate = `${day}/${month}/${year}`;
    return formattedDate;
}

let selctedContestId;
let selectedContestDate;
const NUMBER_OF_CONTESTS_TO_DISPLY=5;
function displayContests(){
    fetch(" https://codeforces.com/api/contest.list").then(responce=>responce.json()).then(data=>{
        if(data.status="OK"){
            let contestsList=data.result;
            let count=0;
            let i1=0;
            const contestListContainer=document.querySelector(".contest-list-container");
            const contestListElementTemplate=document.querySelector("#contest-list-element-template");
            while(count<NUMBER_OF_CONTESTS_TO_DISPLY){
                if(contestsList[i1].phase!="BEFORE"){
                    const newContestElement=contestListElementTemplate.cloneNode(true);
                    newContestElement.className="contest-display-outer";
                    newContestElement.querySelector(".contest-display-name").innerHTML=contestsList[i1].name;
                    newContestElement.querySelector(".contest-display-date-time").innerHTML=getDateString(contestsList[i1].startTimeSeconds)+" "+getTimeString(contestsList[i1].startTimeSeconds);
                    newContestElement.querySelector(".contest-display-phase").innerHTML=contestsList[i1].phase;
                    const contestId=contestsList[i1].id;
                    const contestDate=getDateString(contestsList[i1].startTimeSeconds);
                    newContestElement.querySelector(".contest-display-action-btn").addEventListener('click',function(){
                        newContestElement.querySelector(".contest-display-action-btn").innerHTML="PROCESSING";
                        selctedContestId=contestId;
                        selectedContestDate=contestDate;
                        displayGroupSelector();
                        newContestElement.querySelector(".contest-display-action-btn").innerHTML="Get Rating Changes";
                    });
                    contestListContainer.appendChild(newContestElement);
                    count++;
                }
                i1++;
            }
            
        }
    });
}
displayContests();
function removeDynamicGroups(){
    let groupsDisplayes=document.querySelectorAll(".dynamic-group-element");
    groupsDisplayes.forEach(element=>{
        element.remove();
    });
}
let selectedGroupIds=[];
let selectedAllGroups=false;
let selectedEveryone=false;
function displayGroupSelector(){
    selectedGroupIds=[];
    selectedAllGroups=false;
    selectedEveryone=false;
    document.querySelector(".group-selector-frame").style.display="block";
    removeDynamicGroups();
    fetch("../login/API/getGroups.php").then(responce=>responce.json()).then(data=>{
        if(data.status=="SUCCESS"){
            const groupsContainer=document.querySelector(".group-selector-section-2-outer");
            const groupElementTemplate=document.querySelector("#group-selector-group-element-template");
            data.groups.forEach(group=>{
                const newGroupElement=groupElementTemplate.cloneNode(true);
                newGroupElement.className="group-selector-group-element dynamic-group-element";
                newGroupElement.innerHTML=group.name;
                const groupId=group.id;
                newGroupElement.addEventListener("click",function(){
                    if(selectedGroupIds.indexOf(groupId)!=-1){
                        newGroupElement.style.background="rgba(0, 0, 0, 0.468)";
                        delete selectedGroupIds[selectedGroupIds.indexOf(groupId)];
                    }
                    else{
                        console.log()
                        selectedGroupIds.push(groupId);
                        newGroupElement.style.background="rgb(150,100,100)";

                    }
                });
                groupsContainer.appendChild(newGroupElement);
            });
        }
        else{
            alert("something went wrong");
        }
        

    });
}
document.querySelector(".group-selector-frame").addEventListener('click',function(){
    document.querySelector(".group-selector-frame").style.display="none";
});
document.querySelector(".group-selector-window").addEventListener('click',function(event){
    event.stopPropagation();
});

document.querySelector("#group-selector-btn-everyone").addEventListener("click",function(){
    selectedEveryone=true;
    document.querySelector(".group-selector-frame").style.display="none";
    getPdfRatingChanges(selctedContestId);
});
document.querySelector("#group-selector-btn-all-groups").addEventListener("click",function(){
    selectedAllGroups=true;
    document.querySelector(".group-selector-frame").style.display="none";
    getPdfRatingChanges(selctedContestId);
});
document.querySelector("#group-selector-get-standings-btn").addEventListener("click",function(){
    document.querySelector(".group-selector-frame").style.display="none";
    getPdfRatingChanges(selctedContestId);
});

function getTimeString(time){
    const date = new Date(time*1000);
    const formattedTime = date.toLocaleTimeString('en-US', { hour: 'numeric', minute: 'numeric' });

    return formattedTime;
}

function getDateString(time){
    const date = new Date(time*1000);   
    const day = date.getUTCDate().toString().padStart(2, '0'); // Get the day and pad with leading zeros if necessary
    const month = (date.getUTCMonth() + 1).toString().padStart(2, '0'); // Get the month (zero-based) and add 1, then pad with leading zeros if necessary
    const year = date.getUTCFullYear();

    const formattedDate = `${day}/${month}/${year}`;
    return formattedDate;
}