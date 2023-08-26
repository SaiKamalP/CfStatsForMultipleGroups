
let loginBtnStatus=1;
function onSubmitLogin(token) {
  if(loginBtnStatus==1){
    loginBtnStatus=0;
    document.getElementById("login-btn").innerHTML="WORKING";
    document.getElementById("login-form").submit();
  }
}

let signupBtnStatus=1;
async function onSubmitSignup(token) {
  if(signupBtnStatus==1){
    signupBtnStatus=0;
    document.querySelector('#signup-btn').innerHTML="WORKING";
    const clientHandle=document.getElementById('signup-cf_handle').value;
    const isAValidCodeforcesHandle=await fetch("https://codeforces.com/api/user.info?handles="+clientHandle);
    const result=await isAValidCodeforcesHandle.json();
    if(result.status=='OK'){
      document.getElementById("signup-form").submit();
    }
    else{
      alert("Can't find the codeforces handle.");
      document.querySelector('#signup-btn').innerHTML="JOIN";
      signupBtnStatus=1;
    }
  }
}

let half_slider_state=0;
const half_slider=document.querySelector('.half-slider');
const half_slider_btn=document.querySelector('.half-slider-btn');
half_slider_btn.addEventListener('click',function(){
    if(half_slider_state==0){
      half_slider.style.animation='slide-left 0.5s forwards';
      half_slider_state=1;
      half_slider_btn.innerHTML='GET IN?';
    }
    else{
      half_slider.style.animation='slide-right 0.5s forwards';
      half_slider_state=0;
      half_slider_btn.innerHTML='JOIN US';
      
    } 
});