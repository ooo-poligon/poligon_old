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
	$('#tele_logo').slideUp(50);
	$('#tele_logo_pad').slideUp(50);
	$('#tele_h2').slideUp(50);
	$('#tele_h2_1').slideUp(50);
	$('#tele_h2_2').slideUp(50);
	$('#tele_h2_3').slideUp(50);
	$('#tele_story').slideDown(1000);
	$('#tele_hide_story_arrow').slideDown(1000);
	return false;
}); 
$('A#return_tele_story').click(function(){
	$('#tele_story').slideUp(1000);
	$('#tele_hide_story_arrow').slideUp(1000);
	$('#tele_logo').delay(1000).slideDown(1000);
	$('#tele_logo_pad').delay(1000).slideDown(1000);
	$('#tele_h2').delay(1000).slideDown(1000);
	$('#tele_h2_1').delay(1000).slideDown(1000);
	$('#tele_h2_2').delay(1000).slideDown(1000);
	$('#tele_h2_3').delay(1000).slideDown(1000);
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
	$('#citel_logo').slideUp(50);
	$('#citel_logo_pad').slideUp(50);
	$('#citel_h2').slideUp(50);
	$('#citel_h2_1').slideUp(50);
	$('#citel_h2_2').slideUp(50);
	$('#citel_h2_3').slideUp(50);
	$('#citel_story').slideDown(1000);
	$('#citel_hide_story_arrow').slideDown(1000);
	return false;
}); 
$('A#return_citel_story').click(function(){
	$('#citel_story').slideUp(1000);
	$('#citel_hide_story_arrow').slideUp(1000);
	$('#citel_logo').delay(1000).slideDown(1000);
	$('#citel_logo_pad').delay(1000).slideDown(1000);
	$('#citel_h2').delay(1000).slideDown(1000);
	$('#citel_h2_1').delay(1000).slideDown(1000);
	$('#citel_h2_2').delay(1000).slideDown(1000);
	$('#citel_h2_3').delay(1000).slideDown(1000);
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
	$('#benedict_logo').slideUp(50);
	$('#benedict_logo_pad').slideUp(50);
	$('#benedict_h2').slideUp(50);
	$('#benedict_h2_1').slideUp(50);
	$('#benedict_h2_2').slideUp(50);
	$('#benedict_h2_3').slideUp(50);
	$('#benedict_story').slideDown(1000);
	$('#benedict_hide_story_arrow').slideDown(1000);
	return false;
}); 
$('A#return_benedict_story').click(function(){
	$('#benedict_story').slideUp(1000);
	$('#benedict_hide_story_arrow').slideUp(1000);
	$('#benedict_logo').delay(1000).slideDown(1000);
	$('#benedict_logo_pad').delay(1000).slideDown(1000);
	$('#benedict_h2').delay(1000).slideDown(1000);
	$('#benedict_h2_1').delay(1000).slideDown(1000);
	$('#benedict_h2_2').delay(1000).slideDown(1000);
	$('#benedict_h2_3').delay(1000).slideDown(1000);
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
	$('#graesslin_logo').slideUp(50);
	$('#graesslin_logo_pad').slideUp(50);
	$('#graesslin_h2').slideUp(50);
	$('#graesslin_h2_1').slideUp(50);
	$('#graesslin_h2_2').slideUp(50);
	$('#graesslin_h2_3').slideUp(50);
	$('#graesslin_story').slideDown(1000);
	$('#graesslin_hide_story_arrow').slideDown(1000);
	return false;
}); 
$('A#return_graesslin_story').click(function(){
	$('#graesslin_story').slideUp(1000);
	$('#graesslin_hide_story_arrow').slideUp(1000);
	$('#graesslin_logo').delay(1000).slideDown(1000);
	$('#graesslin_logo_pad').delay(1000).slideDown(1000);
	$('#graesslin_h2').delay(1000).slideDown(1000);
	$('#graesslin_h2_1').delay(1000).slideDown(1000);
	$('#graesslin_h2_2').delay(1000).slideDown(1000);
	$('#graesslin_h2_3').delay(1000).slideDown(1000);
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
	$('#sonder_logo').slideUp(50);
	$('#sonder_logo_pad').slideUp(50);
	$('#sonder_device_image').slideUp(50);
	$('#sonder_h2').slideUp(50);
	$('#sonder_h2_1').slideUp(50);
	$('#sonder_h2_2').slideUp(50);
	$('#sonder_h2_3').slideUp(50);
	$('#sonder_story').slideDown(1000);
	$('#sonder_hide_story_arrow').slideDown(1000);
	$('#sonder_story2').slideDown(1000);
	$('#sonder_hide_story_arrow2').slideDown(1000);
	return false;
}); 
$('A#return_sonder_story').click(function(){
	$('#sonder_story').slideUp(1000);
	$('#sonder_hide_story_arrow').slideUp(1000);
	$('#sonder_story2').slideUp(1000);
	$('#sonder_hide_story_arrow2').slideUp(1000);
	$('#sonder_logo').delay(1000).slideDown(1000);
	$('#sonder_logo_pad').delay(1000).slideDown(1000);
	$('#sonder_device_image').delay(1000).slideDown(1000);
	$('#sonder_h2').delay(1000).slideDown(1000);
	$('#sonder_h2_1').delay(1000).slideDown(1000);
	$('#sonder_h2_2').delay(1000).slideDown(1000);
	$('#sonder_h2_3').delay(1000).slideDown(1000);
	return false;
});
$('A#return_sonder_story2').click(function(){
	$('#sonder_story').slideUp(1000);
	$('#sonder_hide_story_arrow').slideUp(1000);
	$('#sonder_story2').slideUp(1000);
	$('#sonder_hide_story_arrow2').slideUp(1000);
	$('#sonder_logo').delay(1000).slideDown(1000);
	$('#sonder_logo_pad').delay(1000).slideDown(1000);
	$('#sonder_device_image').delay(1000).slideDown(1000);
	$('#sonder_h2').delay(1000).slideDown(1000);
	$('#sonder_h2_1').delay(1000).slideDown(1000);
	$('#sonder_h2_2').delay(1000).slideDown(1000);
	$('#sonder_h2_3').delay(1000).slideDown(1000);
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
	$('#relequick_logo').slideUp(50);
	$('#relequick_logo_pad').slideUp(50);
	$('#relequick_h2').slideUp(50);
	$('#relequick_h2_1').slideUp(50);
	$('#relequick_h2_2').slideUp(50);
	$('#relequick_h2_3').slideUp(50);
	$('#relequick_story').slideDown(1000);
	$('#relequick_hide_story_arrow').slideDown(1000);
	return false;
}); 
$('A#return_relequick_story').click(function(){
	$('#relequick_story').slideUp(1000);
	$('#relequick_hide_story_arrow').slideUp(1000);
	$('#relequick_logo').delay(1000).slideDown(1000);
	$('#relequick_logo_pad').delay(1000).slideDown(1000);
	$('#relequick_h2').delay(1000).slideDown(1000);
	$('#relequick_h2_1').delay(1000).slideDown(1000);
	$('#relequick_h2_2').delay(1000).slideDown(1000);
	$('#relequick_h2_3').delay(1000).slideDown(1000);
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
	$('#comat-releco_logo').slideUp(50);
	$('#comat-releco_logo_pad').slideUp(50);
	$('#comat-releco_h2').slideUp(50);
	$('#comat-releco_h2_1').slideUp(50);
	$('#comat-releco_h2_2').slideUp(50);
	$('#comat-releco_h2_3').slideUp(50);
	$('#comat-releco_story').slideDown(1000);
	$('#comat-releco_hide_story_arrow').slideDown(1000);
	return false;
}); 
$('A#return_comat-releco_story').click(function(){
	$('#comat-releco_story').slideUp(1000);
	$('#comat-releco_hide_story_arrow').slideUp(1000);
	$('#comat-releco_logo').delay(1000).slideDown(1000);
	$('#comat-releco_logo_pad').delay(1000).slideDown(1000);
	$('#comat-releco_h2').delay(1000).slideDown(1000);
	$('#comat-releco_h2_1').delay(1000).slideDown(1000);
	$('#comat-releco_h2_2').delay(1000).slideDown(1000);
	$('#comat-releco_h2_3').delay(1000).slideDown(1000);
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
	$('#emko_logo').slideUp(50);
	$('#emko_logo_pad').slideUp(50);
	$('#emko_h2').slideUp(50);
	$('#emko_h2_1').slideUp(50);
	$('#emko_h2_2').slideUp(50);
	$('#emko_h2_3').slideUp(50);
	$('#emko_story').slideDown(1000);
	$('#emko_hide_story_arrow').slideDown(1000);
	return false;
}); 
$('A#return_emko_story').click(function(){
	$('#emko_story').slideUp(1000);
	$('#emko_hide_story_arrow').slideUp(1000);
	$('#emko_logo').delay(1000).slideDown(1000);
	$('#emko_logo_pad').delay(1000).slideDown(1000);
	$('#emko_h2').delay(1000).slideDown(1000);
	$('#emko_h2_1').delay(1000).slideDown(1000);
	$('#emko_h2_2').delay(1000).slideDown(1000);
	$('#emko_h2_3').delay(1000).slideDown(1000);
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
	$('#cbi_logo').slideUp(50);
	$('#cbi_logo_pad').slideUp(50);
	$('#cbi_device_image').slideUp(50);
	$('#cbi_h2').slideUp(50);
	$('#cbi_h2_1').slideUp(50);
	$('#cbi_h2_2').slideUp(50);
	$('#cbi_h2_3').slideUp(50);
	$('#cbi_story').slideDown(1000);
	$('#cbi_hide_story_arrow').slideDown(1000);
	$('#cbi_story2').slideDown(1000);
	$('#cbi_hide_story_arrow2').slideDown(1000);
	return false;
}); 
$('A#return_cbi_story').click(function(){
	$('#cbi_story').slideUp(1000);
	$('#cbi_hide_story_arrow').slideUp(1000);
	$('#cbi_story2').slideUp(1000);
	$('#cbi_hide_story_arrow2').slideUp(1000);
	$('#cbi_logo').delay(1000).slideDown(1000);
	$('#cbi_logo_pad').delay(1000).slideDown(1000);
	$('#cbi_device_image').delay(1000).slideDown(1000);
	$('#cbi_h2').delay(1000).slideDown(1000);
	$('#cbi_h2_1').delay(1000).slideDown(1000);
	$('#cbi_h2_2').delay(1000).slideDown(1000);
	$('#cbi_h2_3').delay(1000).slideDown(1000);
	return false;
});
$('A#return_cbi_story2').click(function(){
	$('#cbi_story').slideUp(1000);
	$('#cbi_hide_story_arrow').slideUp(1000);
	$('#cbi_story2').slideUp(1000);
	$('#cbi_hide_story_arrow2').slideUp(1000);
	$('#cbi_logo').delay(1000).slideDown(1000);
	$('#cbi_logo_pad').delay(1000).slideDown(1000);
	$('#cbi_device_image').delay(1000).slideDown(1000);
	$('#cbi_h2').delay(1000).slideDown(1000);
	$('#cbi_h2_1').delay(1000).slideDown(1000);
	$('#cbi_h2_2').delay(1000).slideDown(1000);
	$('#cbi_h2_3').delay(1000).slideDown(1000);
	return false;
});
});
//<!-- Скрипт появления и закрытия текста huber_suhner -->
	$(document).ready(function() {
$('A#start_huber_suhner_story').click(function(){
	$('#huber_suhner_logo').slideUp(50);
	$('#huber_suhner_logo_pad').slideUp(50);
	$('#huber_suhner_device_image').slideUp(50);
	$('#huber_suhner_h2').slideUp(50);
	$('#huber_suhner_h2_1').slideUp(50);
	$('#huber_suhner_h2_2').slideUp(50);
	$('#huber_suhner_h2_3').slideUp(50);
	$('#huber_suhner_story').slideDown(1000);
	$('#huber_suhner_hide_story_arrow').slideDown(1000);
	$('#huber_suhner_story3').slideDown(1000);
	$('#huber_suhner_hide_story_arrow2').slideDown(1000);
	return false;
}); 
$('A#return_huber_suhner_story').click(function(){
	$('#huber_suhner_story').slideUp(1000);
	$('#huber_suhner_hide_story_arrow').slideUp(1000);
	$('#huber_suhner_story3').slideUp(1000);
	$('#huber_suhner_hide_story_arrow2').slideUp(1000);
	$('#huber_suhner_logo').delay(1000).slideDown(1000);
	$('#huber_suhner_logo_pad').delay(1000).slideDown(1000);
	$('#huber_suhner_device_image').delay(1000).slideDown(1000);
	$('#huber_suhner_h2').delay(1000).slideDown(1000);
	$('#huber_suhner_h2_1').delay(1000).slideDown(1000);
	$('#huber_suhner_h2_2').delay(1000).slideDown(1000);
	$('#huber_suhner_h2_3').delay(1000).slideDown(1000);
	return false;
});
$('A#return_huber_suhner_story2').click(function(){
	$('#huber_suhner_story').slideUp(1000);
	$('#huber_suhner_hide_story_arrow').slideUp(1000);
	$('#huber_suhner_story3').slideUp(1000);
	$('#huber_suhner_hide_story_arrow2').slideUp(1000);
	$('#huber_suhner_logo').delay(1000).slideDown(1000);
	$('#huber_suhner_logo_pad').delay(1000).slideDown(1000);
	$('#huber_suhner_device_image').delay(1000).slideDown(1000);
	$('#huber_suhner_h2').delay(1000).slideDown(1000);
	$('#huber_suhner_h2_1').delay(1000).slideDown(1000);
	$('#huber_suhner_h2_2').delay(1000).slideDown(1000);
	$('#huber_suhner_h2_3').delay(1000).slideDown(1000);
	return false;
});
});
}