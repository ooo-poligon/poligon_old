$flag = 0;
function resize(){

	if ($flag ==0)	{
    intHeight = document.documentElement.clientHeight;
	mainHeight = document.getElementById('main_div').offsetHeight;
	footerHeight = document.getElementById('footer').offsetHeight;
	sumHeight = mainHeight + footerHeight;
	newHeight = intHeight - mainHeight;
	$flag = 1;
	};
	if (sumHeight<intHeight)	{
  document.getElementById('footer').style.height = newHeight-2 + "px";
  }
  else	{
  document.getElementById('footer').style.height = footerHeight + "px";
  };
}
window.onresize=resize;
window.onload=resize;