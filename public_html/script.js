function setAllA4Width(){
    var width=document.querySelector('body').clientWidth;
    var element=document.querySelectorAll('.A4-page');
    for(var i=0;i<element.length;i++){
        element[i].style.width=width+'px';
    }
}