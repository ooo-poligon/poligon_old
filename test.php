<?php
$to      = 'labvit@gmail.com';
$subject = 'the subject';
$message = 'hello';
$headers = 'From: webmaster@poligon.info' . "\r\n" .
    'Reply-To: web:master@poligon.info' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);
?>
