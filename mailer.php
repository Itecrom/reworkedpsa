<?php
function sendEmail($to, $subject, $body, $name = '') {
  $headers = "From: noreply@malawipsa.org\r\n";
  $headers .= "Reply-To: noreply@malawipsa.org\r\n";
  $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
  
  return mail($to, $subject, $body, $headers);
}
?>
