<?php
error_reporting(E_ALL); // Вывод ошибок.

$to  = 'labvit@gmail.com';

$subject = 'Text';

$message = 'Next';

$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=windows-1251' . "\r\n";
$headers .= 'To: Vitaly <labvit@gmail.com>' . "\r\n";
$headers .= 'From: server <@poligon.info>' . "\r\n";

mail($to, $subject, $message, $headers);
?>
