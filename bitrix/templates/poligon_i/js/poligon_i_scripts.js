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
	$("BUTTON#special_close").click(function() { 
	// Скрываем блок 
	$("section#special").slideUp(); 
	return false; // не производить переход по ссылке
}); 
}); // end of ready()
//<!-- Скрипт плавного открытия и закрытия блока контактов -->
$(document).ready(function() { 
	$("A#trigger_1").click(function() { 
	// Отображаем скрытый блок 
	$("section#contacts").slideDown();
	return false; // не производить переход по ссылке
}); 
}); // end of ready() 
$(document).ready(function() { 
	$("BUTTON#trigger_2").click(function() { 
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
	$('BUTTON#info_off').click(function(){
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
	  $('#citel_h2_2').animate({
		  opacity:"0",
      }, 500 );
	  $('#citel_h2_3').animate({
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
	  $('#citel_h2_2').animate({
		  opacity:"100",
      }, 3000 );
	  $('#citel_h2_3').animate({
		  opacity:"100",
      }, 3000 );
  $('#citel_hide_story_arrow').slideUp();
	  		return false;
});
});
//<!-- Скрипт появления и закрытия сертификатов BENEDICT -->
	$(document).ready(function() {
  $('A#start_benedict_sertificates').click(function(){
  $('#benedict_sertificates').slideDown();
  	  	return false;	
});
$('A#hide_benedict_sertificates').click(function(){
  $('#benedict_sertificates').slideUp();
	  	return false;
});
});
//<!-- Скрипт появления и закрытия текста BENEDICT -->
	$(document).ready(function() {
$('A#start_benedict_story').click(function(){
  $('#benedict_story').animate({
		  height:"180",
		  opacity:"100"
      }, 1000 );
  $('#benedict_logo').animate({
		  opacity:"0"
      }, 1000 );
  $('#benedict_h2').animate({
		  opacity:"0",
      }, 500 );
	    $('#benedict_h2_1').animate({
		  opacity:"0",
      }, 500 );
	  $('#benedict_h2_2').animate({
		  opacity:"0",
      }, 500 );
	  $('#benedict_h2_3').animate({
		  opacity:"0",
      }, 500 );
  $('#benedict_hide_story_arrow').slideDown();
	  		return false;
}); 
$('A#return_benedict_story').click(function(){
  $('#benedict_story').animate({
		  opacity:"0",
		  height:"0"
      }, 1000 );
  $('#benedict_logo').animate({
		  height:"80",
		  opacity:"100"
      }, 1000 );
  $('#benedict_h2').animate({
		  opacity:"100",
      }, 3000 );
	    $('#benedict_h2_1').animate({
		  opacity:"100",
      }, 3000 );
	  $('#benedict_h2_2').animate({
		  opacity:"100",
      }, 3000 );
	  $('#benedict_h2_3').animate({
		  opacity:"100",
      }, 3000 );
  $('#benedict_hide_story_arrow').slideUp();
	  		return false;
});
});
//<!-- Скрипт появления и закрытия сертификатов GRAESSLIN -->
	$(document).ready(function() {
  $('A#start_graesslin_sertificates').click(function(){
  $('#graesslin_sertificates').slideDown();
  	  	return false;	
});
$('A#hide_graesslin_sertificates').click(function(){
  $('#graesslin_sertificates').slideUp();
	  	return false;
});
});
//<!-- Скрипт появления и закрытия текста GRAESSLIN -->
	$(document).ready(function() {
$('A#start_graesslin_story').click(function(){
  $('#graesslin_story').slideDown();
  $('#graesslin_logo').animate({
		  opacity:"0"
      }, 50 );
  $('#graesslin_h2').animate({
		  opacity:"0",
      }, 50 );
	    $('#graesslin_h2_1').animate({
		  opacity:"0",
      }, 50 );
	  $('#graesslin_h2_2').animate({
		  opacity:"0",
      }, 50 );
	  $('#graesslin_h2_3').animate({
		  opacity:"0",
      }, 50 );
  $('#graesslin_hide_story_arrow').slideDown();
	  		return false;
}); 
$('A#return_graesslin_story').click(function(){
  $('#graesslin_story').slideUp();
  $('#graesslin_logo').animate({
		  height:"100",
		  opacity:"100"
      }, 50 );
  $('#graesslin_h2').animate({
		  opacity:"100",
      }, 50 );
	    $('#graesslin_h2_1').animate({
		  opacity:"100",
      }, 50 );
	  $('#graesslin_h2_2').animate({
		  opacity:"100",
      }, 50 );
	  $('#graesslin_h2_3').animate({
		  opacity:"100",
      }, 50 );
  $('#graesslin_hide_story_arrow').slideUp();
	  		return false;
});
});
//<!-- Скрипт появления и закрытия сертификатов SONDER -->
	$(document).ready(function() {
  $('A#start_sonder_sertificates').click(function(){
  $('#sonder_sertificates').slideDown();
  	  	return false;	
});
$('A#hide_sonder_sertificates').click(function(){
  $('#sonder_sertificates').slideUp();
	  	return false;
});
});
//<!-- Скрипт появления и закрытия текста SONDER -->
	$(document).ready(function() {
$('A#start_sonder_story').click(function(){
  $('#sonder_story').slideDown();
  $('#sonder_logo').animate({
		  height:"0",
		  opacity:"0"
      }, 50 );
  $('#sonder_h2').animate({
		  opacity:"0",
      }, 50 );
	    $('#sonder_h2_1').animate({
		  opacity:"0",
      }, 50 );
	  $('#sonder_h2_2').animate({
		  opacity:"0",
      }, 50 );
	  $('#sonder_h2_3').animate({
		  opacity:"0",
      }, 50 );
  $('#sonder_hide_story_arrow').slideDown();
  $('#sonder_story2').slideDown();
  $('#sonder_device_image').animate({
		  height:"0",
		  opacity:"0"
      }, 50 );
  $('#sonder_hide_story_arrow2').slideDown();
	  		return false;
}); 
$('A#return_sonder_story').click(function(){
  $('#sonder_story').slideUp();
  $('#sonder_logo').animate({
		  height:"190",
		  opacity:"100"
      }, 50 );
  $('#sonder_h2').animate({
		  opacity:"100",
      }, 50 );
	    $('#sonder_h2_1').animate({
		  opacity:"100",
      }, 50 );
	  $('#sonder_h2_2').animate({
		  opacity:"100",
      }, 50 );
	  $('#sonder_h2_3').animate({
		  opacity:"100",
      }, 50 );
  $('#sonder_hide_story_arrow').slideUp();
  $('#sonder_story2').slideUp();
  $('#sonder_device_image').animate({
		  height:"370",
		  opacity:"100"
      }, 50 );
  $('#sonder_hide_story_arrow2').slideUp();
	  		return false;
});
$('A#return_sonder_story2').click(function(){
  $('#sonder_story').slideUp();
  $('#sonder_logo').animate({
		  height:"190",
		  opacity:"100"
      }, 50 );
  $('#sonder_h2').animate({
		  opacity:"100",
      }, 50 );
	    $('#sonder_h2_1').animate({
		  opacity:"100",
      }, 50 );
	  $('#sonder_h2_2').animate({
		  opacity:"100",
      }, 50 );
	  $('#sonder_h2_3').animate({
		  opacity:"100",
      }, 50 );
  $('#sonder_hide_story_arrow').slideUp();
  $('#sonder_story2').slideUp();
  $('#sonder_device_image').animate({
		  height:"370",
		  opacity:"100"
      }, 50 );
  $('#sonder_hide_story_arrow2').slideUp();
	  		return false;
});
});
//<!-- Скрипт появления и закрытия сертификатов RELEQUICK -->
	$(document).ready(function() {
  $('A#start_relequick_sertificates').click(function(){
  $('#relequick_sertificates').slideDown();
  	  	return false;	
});
$('A#hide_relequick_sertificates').click(function(){
  $('#relequick_sertificates').slideUp();
	  	return false;
});
});
//<!-- Скрипт появления и закрытия текста RELEQUICK -->
	$(document).ready(function() {
$('A#start_relequick_story').click(function(){
  $('#relequick_story').slideDown();
  $('#relequick_logo').animate({
  		  height:"0",
		  opacity:"0"
      }, 50 );
  $('#relequick_h2').animate({
		  opacity:"0",
      }, 50 );
	    $('#relequick_h2_1').animate({
		  opacity:"0",
      }, 50 );
	  $('#relequick_h2_2').animate({
		  opacity:"0",
      }, 50 );
	  $('#relequick_h2_3').animate({
		  opacity:"0",
      }, 50 );
  $('#relequick_hide_story_arrow').slideDown();
	  		return false;
}); 
$('A#return_relequick_story').click(function(){
  $('#relequick_hide_story_arrow').slideUp();
  $('#relequick_story').slideUp();
  $('#relequick_logo').animate({
		  height:"100",
		  opacity:"100"
      }, 50 );
  $('#relequick_h2').animate({
		  opacity:"100",
      }, 50 );
	    $('#relequick_h2_1').animate({
		  opacity:"100",
      }, 50 );
	  $('#relequick_h2_2').animate({
		  opacity:"100",
      }, 50 );
	  $('#relequick_h2_3').animate({
		  opacity:"100",
      }, 50 );
	  		return false;
});
});
//<!-- Скрипт появления и закрытия сертификатов comat-releco -->
	$(document).ready(function() {
  $('A#start_comat-releco_sertificates').click(function(){
  $('#comat-releco_sertificates').slideDown();
  	  	return false;	
});
$('A#hide_comat-releco_sertificates').click(function(){
  $('#comat-releco_sertificates').slideUp();
	  	return false;
});
});
//<!-- Скрипт появления и закрытия текста comat-releco -->
	$(document).ready(function() {
$('A#start_comat-releco_story').click(function(){
  $('#comat-releco_story').slideDown();
  $('#comat-releco_logo').animate({
  		  height:"0",
		  opacity:"0"
      }, 50 );
  $('#comat-releco_h2').animate({
		  opacity:"0",
      }, 50 );
	    $('#comat-releco_h2_1').animate({
		  opacity:"0",
      }, 50 );
	  $('#comat-releco_h2_2').animate({
		  opacity:"0",
      }, 50 );
	  $('#comat-releco_h2_3').animate({
		  opacity:"0",
      }, 50 );
  $('#comat-releco_hide_story_arrow').slideDown();
	  		return false;
}); 
$('A#return_comat-releco_story').click(function(){
  $('#comat-releco_hide_story_arrow').slideUp();
  $('#comat-releco_story').slideUp();
  $('#comat-releco_logo').animate({
		  height:"100",
		  opacity:"100"
      }, 50 );
  $('#comat-releco_h2').animate({
		  opacity:"100",
      }, 50 );
	    $('#comat-releco_h2_1').animate({
		  opacity:"100",
      }, 50 );
	  $('#comat-releco_h2_2').animate({
		  opacity:"100",
      }, 50 );
	  $('#comat-releco_h2_3').animate({
		  opacity:"100",
      }, 50 );
	  		return false;
});
});
//<!-- Скрипт появления и закрытия сертификатов emko -->
	$(document).ready(function() {
  $('A#start_emko_sertificates').click(function(){
  $('#emko_sertificates').slideDown();
  	  	return false;	
});
$('A#hide_emko_sertificates').click(function(){
  $('#emko_sertificates').slideUp();
	  	return false;
});
});
//<!-- Скрипт появления и закрытия текста emko -->
	$(document).ready(function() {
$('A#start_emko_story').click(function(){
  $('#emko_story').slideDown();
  $('#emko_logo').animate({
  		  height:"0",
		  opacity:"0"
      }, 50 );
  $('#emko_h2').animate({
		  opacity:"0",
      }, 50 );
	    $('#emko_h2_1').animate({
		  opacity:"0",
      }, 50 );
	  $('#emko_h2_2').animate({
		  opacity:"0",
      }, 50 );
	  $('#emko_h2_3').animate({
		  opacity:"0",
      }, 50 );
  $('#emko_hide_story_arrow').slideDown();
	  		return false;
}); 
$('A#return_emko_story').click(function(){
  $('#emko_hide_story_arrow').slideUp();
  $('#emko_story').slideUp();
  $('#emko_logo').animate({
		  height:"100",
		  opacity:"100"
      }, 50 );
  $('#emko_h2').animate({
		  opacity:"100",
      }, 50 );
	    $('#emko_h2_1').animate({
		  opacity:"100",
      }, 50 );
	  $('#emko_h2_2').animate({
		  opacity:"100",
      }, 50 );
	  $('#emko_h2_3').animate({
		  opacity:"100",
      }, 50 );
	  		return false;
});
});
//<!-- Скрипт появления и закрытия сертификатов cbi -->
	$(document).ready(function() {
  $('A#start_cbi_sertificates').click(function(){
  $('#cbi_sertificates').slideDown();
  	  	return false;	
});
$('A#hide_cbi_sertificates').click(function(){
  $('#cbi_sertificates').slideUp();
	  	return false;
});
});
//<!-- Скрипт появления и закрытия текста cbi -->
	$(document).ready(function() {
$('A#start_cbi_story').click(function(){
  $('#cbi_story').slideDown();
  $('#cbi_logo').animate({
		  height:"0",
		  opacity:"0"
      }, 50 );
  $('#cbi_h2').animate({
		  opacity:"0",
      }, 50 );
	    $('#cbi_h2_1').animate({
		  opacity:"0",
      }, 50 );
	  $('#cbi_h2_2').animate({
		  opacity:"0",
      }, 50 );
	  $('#cbi_h2_3').animate({
		  opacity:"0",
      }, 50 );
  $('#cbi_hide_story_arrow').slideDown();
  $('#cbi_story2').slideDown();
  $('#cbi_device_image').animate({
		  height:"0",
		  opacity:"0"
      }, 50 );
  $('#cbi_hide_story_arrow2').slideDown();
	  		return false;
}); 
$('A#return_cbi_story').click(function(){
  $('#cbi_story').slideUp();
  $('#cbi_logo').animate({
		  height:"110",
		  opacity:"100"
      }, 50 );
  $('#cbi_h2').animate({
		  opacity:"100",
      }, 50 );
	    $('#cbi_h2_1').animate({
		  opacity:"100",
      }, 50 );
	  $('#cbi_h2_2').animate({
		  opacity:"100",
      }, 50 );
	  $('#cbi_h2_3').animate({
		  opacity:"100",
      }, 50 );
  $('#cbi_hide_story_arrow').slideUp();
  $('#cbi_story2').slideUp();
  $('#cbi_device_image').animate({
		  height:"370",
		  opacity:"100"
      }, 50 );
  $('#cbi_hide_story_arrow2').slideUp();
	  		return false;
});
$('A#return_cbi_story2').click(function(){
  $('#cbi_story').slideUp();
  $('#cbi_logo').animate({
		  height:"110",
		  opacity:"100"
      }, 50 );
  $('#cbi_h2').animate({
		  opacity:"100",
      }, 50 );
	    $('#cbi_h2_1').animate({
		  opacity:"100",
      }, 50 );
	  $('#cbi_h2_2').animate({
		  opacity:"100",
      }, 50 );
	  $('#cbi_h2_3').animate({
		  opacity:"100",
      }, 50 );
  $('#cbi_hide_story_arrow').slideUp();
  $('#cbi_story2').slideUp();
  $('#cbi_device_image').animate({
		  height:"370",
		  opacity:"100"
      }, 50 );
  $('#cbi_hide_story_arrow2').slideUp();
	  		return false;
});
});
//<!-- Скрипт появления и закрытия текста huber_suhner -->
	$(document).ready(function() {
$('A#start_huber_suhner_story').click(function(){
  $('#huber_suhner_story').slideDown();
  $('#huber_suhner_logo').animate({
		  height:"0",
		  opacity:"0"
      }, 50 );
  $('#huber_suhner_h2').animate({
		  opacity:"0",
      }, 50 );
	    $('#huber_suhner_h2_1').animate({
		  opacity:"0",
      }, 50 );
	  $('#huber_suhner_h2_2').animate({
		  opacity:"0",
      }, 50 );
	  $('#huber_suhner_h2_3').animate({
		  opacity:"0",
      }, 50 );
  $('#huber_suhner_hide_story_arrow').slideDown();
  $('#huber_suhner_hide_story_arrow2').slideDown();
  $('#huber_suhner_story2').slideDown();
  $('#huber_suhner_story3').slideDown();
  $('#huber_suhner_device_image').animate({
		  height:"0",
		  opacity:"0"
      }, 50 );
	  		return false;
}); 
$('A#return_huber_suhner_story').click(function(){
  $('#huber_suhner_story').slideUp();
  $('#huber_suhner_logo').animate({
		  height:"60",
		  opacity:"100"
      }, 50 );
  $('#huber_suhner_h2').animate({
		  opacity:"100",
      }, 50 );
	    $('#huber_suhner_h2_1').animate({
		  opacity:"100",
      }, 50 );
	  $('#huber_suhner_h2_2').animate({
		  opacity:"100",
      }, 50 );
	  $('#huber_suhner_h2_3').animate({
		  opacity:"100",
      }, 50 );
	  $('#huber_suhner_links_list').animate({
		  opacity:"100",
      }, 50 );
  $('#huber_suhner_hide_story_arrow').slideUp();
  $('#huber_suhner_hide_story_arrow2').slideUp();
  $('#huber_suhner_story2').slideUp();
  $('#huber_suhner_story3').slideUp();
  $('#huber_suhner_device_image').animate({
		  height:"270",
		  opacity:"100"
      }, 50 );
	  		return false;
});
$('A#return_huber_suhner_story2').click(function(){
  $('#huber_suhner_story').slideUp();
  $('#huber_suhner_logo').animate({
		  height:"60",
		  opacity:"100"
      }, 50 );
  $('#huber_suhner_h2').animate({
		  opacity:"100",
      }, 50 );
	    $('#huber_suhner_h2_1').animate({
		  opacity:"100",
      }, 50 );
	  $('#huber_suhner_h2_2').animate({
		  opacity:"100",
      }, 50 );
	  $('#huber_suhner_h2_3').animate({
		  opacity:"100",
      }, 50 );
	  $('#huber_suhner_links_list').animate({
		  opacity:"100",
      }, 50 );
  $('#huber_suhner_hide_story_arrow').slideUp();
  $('#huber_suhner_hide_story_arrow2').slideUp();
  $('#huber_suhner_story2').slideUp();
  $('#huber_suhner_story3').slideUp();
  $('#huber_suhner_device_image').animate({
		  height:"270",
		  opacity:"100"
      }, 50 );
	  		return false;
});
});
}