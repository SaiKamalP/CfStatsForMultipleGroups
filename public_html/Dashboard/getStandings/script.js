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
function getPdfStandings(contestId){
    clearPreviousPdf();
    fetch("../login/API/getUsers.php").then(responce=>responce.json()).then(data=>{
        if(data['status']=='FAILED'){
            return;
        }
        const users=data.users;
        let cfusersStringForCFAPI="";
        users.forEach(usr => {
            cfusersStringForCFAPI+=usr.cf_handle+';';
        });
        
        var cfAPICallString="https://codeforces.com/api/contest.standings?contestId="+contestId+"&showUnofficial=true&handles="+
                            cfusersStringForCFAPI;
        fetch(cfAPICallString).then(responce=>responce.json()).then(async data2=>{
            const cfStandings=data2.result;
    
            //contest data filling
            const first_page_outer=document.querySelector('.first-page-outer');
            first_page_outer.innerHTML=cfStandings.contest.name+"<br>" +getDateString(cfStandings.contest.startTimeSeconds)+"<br>"+getTimeString(cfStandings.contest.startTimeSeconds);
            document.querySelector('title').textContent=cfStandings.contest.name+" stangings report";
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
            console.log(cfStandings);
            //group counting( 0 initialized above)
            cfStandings.rows.forEach(row=>{
                if(row.party.participantType=="CONTESTANT" || row.party.participantType=="OUT_OF_COMPETITION" ){
                    if(participantToGroupMap.get(row.party.members[0].handle)!="none"){
                        groupParticipantsCount.set(participantToGroupMap.get(row.party.members[0].handle),groupParticipantsCount.get(participantToGroupMap.get(row.party.members[0].handle))+1);
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
                participantToRatingMap.set(person.handle,0);
                if(person.hasOwnProperty("rating")){
                    participantToRatingMap.set(person.handle,person.rating);
                }
            });
    
            const section_3_page=document.querySelector('.standings-template').cloneNode(true);
            section_3_page.className="A4-width dynamicElement";
            const section_3_container_outer=section_3_page.querySelector('.section-3-container-outer');
            const section_3_row_template=section_3_page.querySelector('.section-3-row-outer');
            const section_3_top_bar=section_3_page.querySelector('.section-3-top-bar');
            cfStandings.problems.forEach(problem=>{
                const aTagElement=document.createElement('p');
                aTagElement.className="section-3-top-bar-tag";
                aTagElement.textContent=problem.index;
                section_3_top_bar.appendChild(aTagElement);
            });
            let count=0;
            cfStandings.rows.forEach(row=>{
                if(row.party.participantType=="CONTESTANT" || row.party.participantType=="OUT_OF_COMPETITION"){
                    count++;
                    const section_3_row_clone=section_3_row_template.cloneNode(true);
                    section_3_row_clone.className="section-3-row-outer";
                    section_3_row_clone.querySelector('.section-3-row-b1').textContent=count+"("+row.rank+")";
                    if(participantToGroupMap.get(row.party.members[0].handle)!="none"){
                        section_3_row_clone.querySelector('.section-3-row-b2').textContent=participantToGroupMap.get(row.party.members[0].handle);
                    }
                    else{
                        section_3_row_clone.querySelector('.section-3-row-b2').textContent="";
                    }
                    section_3_row_clone.querySelector('.section-3-row-b3').textContent=row.party.members[0].handle;
                    section_3_row_clone.querySelector('.section-3-row-b3').style.color=getColor(participantToRatingMap.get(row.party.members[0].handle));
                    section_3_row_clone.querySelector('.section-3-row-b4').textContent=row.points;
    
                    row.problemResults.forEach(pData=>{
                        var pointsTag=document.createElement("p");
                        pointsTag.className="section-3-row-tag";
                        if(pData.points>0){
                            pointsTag.textContent="+"+pData.points;
                        }
                        else{
                            if(pData.rejectedAttemptCount>0){
                                pointsTag.textContent="-"+pData.rejectedAttemptCount;
                                pointsTag.style.color='red';
                            }
                            else{
                                pointsTag.textContent="0";
                                pointsTag.style.color='grey';
    
                            }
                        }
                        section_3_row_clone.appendChild(pointsTag);
                    });
    
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
                        const section_3_top_bar=section_3_page.querySelector('.section-3-top-bar');
                        cfStandings.problems.forEach(problem=>{
                            const aTagElement=document.createElement('p');
                            aTagElement.className="section-3-top-bar-tag";
                            aTagElement.textContent=problem.index;
                            section_3_top_bar.appendChild(aTagElement);
                        });
                        let count=0;
                        cfStandings.rows.forEach(row=>{
                            if((row.party.participantType=="CONTESTANT" || row.party.participantType=="OUT_OF_COMPETITION" )&& (a[0]==participantToGroupMap.get(row.party.members[0].handle))){
                                count++;
                                const section_3_row_clone=section_3_row_template.cloneNode(true);
                                section_3_row_clone.className="section-3-row-outer";
                                section_3_row_clone.querySelector('.section-3-row-b1').textContent=count+"("+row.rank+")";
                                section_3_row_clone.querySelector('.section-3-row-b2').textContent=participantToGroupMap.get(row.party.members[0].handle);
                                section_3_row_clone.querySelector('.section-3-row-b3').textContent=row.party.members[0].handle;
                                section_3_row_clone.querySelector('.section-3-row-b3').style.color=getColor(participantToRatingMap.get(row.party.members[0].handle));
                                section_3_row_clone.querySelector('.section-3-row-b4').textContent=row.points;
            
                                row.problemResults.forEach(pData=>{
                                    var pointsTag=document.createElement("p");
                                    pointsTag.className="section-3-row-tag";
                                    if(pData.points>0){
                                        pointsTag.textContent="+"+pData.points;
                                    }
                                    else{
                                        if(pData.rejectedAttemptCount>0){
                                            pointsTag.textContent="-"+pData.rejectedAttemptCount;
                                            pointsTag.style.color='red';
                                        }
                                        else{
                                            pointsTag.textContent="0";
                                            pointsTag.style.color='grey';
            
                                        }
                                    }
                                    section_3_row_clone.appendChild(pointsTag);
                                });
            
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

function temporarlyAskForContestId(){
    const contest_id=prompt("Enter the contest ID.");
    getPdfStandings(contest_id);

}

temporarlyAskForContestId();


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