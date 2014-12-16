window.onload=function(){
//<!-- Скрипт плавного перемещения к якорю -->
$(function($) {$.localScroll({duration: 1000, hash: false }); });
//<!-- Скрипт плавного открытия и закрытия блока спецпредложений -->
$(document).ready(function() { 
	$("A#special_open").click(function() { 
	// Отображаем скрытый блок 
	$("section#special").slideDown();
	return false; // не производить переход по ссылке
}); 
}); // end of ready() 
$(document).ready(function() { 
	$("AREA#special_close").click(function() { 
	// Скрываем блок 
	$("section#special").slideUp(); 
	return false; // не производить переход по ссылке
}); 
}); // end of ready()
//<!-- Скрипт плавного открытия и закрытия блока новостей -->
$(document).ready(function() { 
	$("A#trigger_1").click(function() { 
	// Отображаем скрытый блок 
	$("section#contacts").slideDown();
	return false; // не производить переход по ссылке
}); 
}); // end of ready() 
$(document).ready(function() { 
	$("AREA#trigger_2").click(function() { 
	// Скрываем блок 
	$("section#contacts").slideUp(); 
	return false; // не производить переход по ссылке
}); 
}); // end of ready()
//<!-- Скрипт появления и закрытия инфо с флагом -->
	$(document).ready(function() {
	$('A#info_on').click(function(){
	$('#company_info').slideDown();
  	  	return false;	
});
	$('A#info_off').click(function(){
	$('#company_info').slideUp();
	  	return false;
});
});
//<!-- Скрипт появления и закрытия сертификатов TELE -->
	$(document).ready(function() {
  $('A#start_tele_sertificates').click(function(){
  $('#tele_sertificates').slideDown();
  	  	return false;	
});
$('A#hide_tele_sertificates').click(function(){
  $('#tele_sertificates').slideUp();
	  	return false;
});
});
//<!-- Скрипт появления и закрытия текста TELE -->
	$(document).ready(function() {
$('A#start_tele_story').click(function(){
  $('#tele_story').animate({
		  height:"330",
		  opacity:"100"
      }, 1000 );
  $('#tele_logo').animate({
		  height:"0",
		  opacity:"0"
      }, 1000 );
  $('#tele_h2').animate({
		  opacity:"0"
      }, 500 );
	    $('#tele_h2_1').animate({
		  opacity:"0"
      }, 500 );
	  $('#tele_h2_2').animate({
		  opacity:"0"
      }, 500 );
	  $('#tele_h2_3').animate({
		  opacity:"0"
      }, 500 );
  $('#tele_hide_story_arrow').slideDown();
	  		return false;
}); 
$('A#return_tele_story').click(function(){
  $('#tele_story').animate({
		  opacity:"0",
		  height:"0"
      }, 1000 );
  $('#tele_logo').animate({
		  height:"100",
		  opacity:"100"
      }, 1000 );
	    $('#tele_h2').animate({
		  opacity:"100"
      }, 500 );
	    $('#tele_h2_1').animate({
		  opacity:"100"
      }, 500 );
	  $('#tele_h2_2').animate({
		  opacity:"100"
      }, 500 );
	  $('#tele_h2_3').animate({
		  opacity:"100"
      }, 500 );
  $('#tele_hide_story_arrow').slideUp();
	  		return false;
});
});
//<!-- Скрипт появления и закрытия сертификатов CITEL -->
	$(document).ready(function() {
  $('A#start_citel_sertificates').click(function(){
  $('#citel_sertificates').slideDown();
  	  	return false;	
});
$('A#hide_citel_sertificates').click(function(){
  $('#citel_sertificates').slideUp();
	  	return false;
});
});
//<!-- Скрипт появления и закрытия текста CITEL -->
	$(document).ready(function() {
$('A#start_citel_story').click(function(){
  $('#citel_story').animate({
		  height:"330",
		  opacity:"100"
      }, 1000 );
  $('#citel_logo').animate({
		  height:"0",
		  opacity:"0"
      }, 1000 );
  $('#citel_h2').animate({
		  opacity:"0",
      }, 500 );
	    $('#citel_h2_1').animate({
		  opacity:"0",
      }, 500 );
  $('#citel_hide_story_arrow').slideDown();
	  		return false;
}); 
$('A#return_citel_story').click(function(){
  $('#citel_story').animate({
		  opacity:"0",
		  height:"0"
      }, 1000 );
  $('#citel_logo').animate({
		  height:"200",
		  opacity:"100"
      }, 1000 );
  $('#citel_h2').animate({
		  opacity:"100",
      }, 3000 );
	    $('#citel_h2_1').animate({
		  opacity:"100",
      }, 3000 );
  $('#citel_hide_story_arrow').slideUp();
	  		return false;
});
});
}