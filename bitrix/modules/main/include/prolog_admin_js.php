<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!$USER->IsAuthorized())
{
	echo "<script>window.location = window.location.href;</script>";
	die();
}
?>