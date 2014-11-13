<?php

error_reporting(E_ALL); // <D0><92><D1><8B><D0><B2><D0><BE><D0><B4> <D0><BE><D1><88><D0><B8><D0><B1><D0><BE><D0><BA>.

$to  = 'labvit@gmail.com';

$subject = 'Text';

$message = 'Next';

$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=windows-1251' . "\r\n";
$headers .= 'To: Vitaly <labvit@gmail.com>' . "\r\n";
$headers .= 'From: server <webmaster@poligon.info>' . "\r\n";

@macter.ru . "\r\n" .  'X-Mailer: PHP/' . phpversion();

if (@mail($to, $subject, $message, $headers)) echo 'сообщение отправлено';
else echo 'нет!!!';
?>
