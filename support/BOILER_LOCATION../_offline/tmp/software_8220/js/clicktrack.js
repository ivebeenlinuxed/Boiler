var xOffset,yOffset;
var tempX = 0;
var tempY = 0;
 
 
//detect browser
var IE = document.all?true:false
if (!IE) {
document.captureEvents(Event.MOUSEMOVE)
}
//find the position of the first item on screen and store offsets
//find the first item on screen (after body)
var firstElement=document.getElementsByTagName('body')[0].childNodes[1];
//find the offset coordinates
xOffset=findPosX(firstElement);
yOffset=findPosY(firstElement);
if (IE){ // In IE there's a default margin in the page body. If margin's not defined, use defaults
var marginLeftExplorer  = parseInt(document.getElementsByTagName('body')[0].style.marginLeft);
var marginTopExplorer   = parseInt(document.getElementsByTagName('body')[0].style.marginTop);
/*assume default 10px/15px margin in explorer*/
if (isNaN(marginLeftExplorer)) {marginLeftExplorer=10;}
if (isNaN(marginTopExplorer)) {marginTopExplorer=15;}
xOffset=xOffset+marginLeftExplorer;
yOffset=yOffset+marginTopExplorer;
}
/*attach a handler to the onmousedown event that calls a function to store the values*/
document.onmousedown = getMouseXY;
 
 
 
 
/*Functions*/
/*Find positions*/
function findPosX(obj){
var curleft = 0;
if (obj.offsetParent){
while (obj.offsetParent){
curleft += obj.offsetLeft
obj = obj.offsetParent;
}
}else if (obj.x){
curleft += obj.x;
}
return curleft;
}
 
 
function findPosY(obj){
var curtop = 0;
if (obj.offsetParent){
while (obj.offsetParent){
curtop += obj.offsetTop
obj = obj.offsetParent;
}
}else if (obj.y){
curtop += obj.y;
}
return curtop;
}
function getMouseXY(e) {
if (IE) {
tempX = event.clientX + document.body.scrollLeft
tempY = event.clientY + document.body.scrollTop
} else {
tempX = e.pageX
tempY = e.pageY
}
tempX-=xOffset;
tempY-=yOffset;
var url='http://yourwebsite.com/empty.php?x='+tempX+'&y='+tempY; /* Type your website URL here*/
 
 
ajad_send(url);
 
 
return true;
}
 
 
var ajad_ndx_script = 0;
 
 
function ajad_do (u) {
// Create new JS element
var js = document.createElement('SCRIPT');
js.type = 'text/javascript';
ajad_ndx_script++;
js.id = 'ajad-' + ajad_ndx_script;
js.src = u;
 
 
// Append JS element (therefore executing the 'AJAX' call)
document.body.appendChild(js);
 
 
return true;
}
 
 
function ajad_get (r) {
// Create URL
var u = r;
 
 
 
 
// Do AJAD
return ajad_do(u);
}
 
 
function ajad_send(url) {
// referrer
// r = window.location;
 
 
var r = url;
// send it
ajad_get(r);
 
 
// remove the last script node.
document.body.removeChild(document.getElementById('ajad-' + ajad_ndx_script));
ajad_ndx_script--;
}
