<?php

namespace OCA\crate_it\lib;

class Mailer {
  
  public function send($to, $from, $subject, $content) {
    $result = (mail($to, $subject, $content, "From: $from\n"));
    if(!$result) {
      throw new Exception('Unable to send email at this time');
    }
    return $result;
  }

  public function sendHtml($to, $from, $subject, $content) {
    $headers= "From: $from\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $result = (mail($to, $subject, $content, $headers));
    if(!$result) {
      throw new Exception('Unable to send email at this time');
    }
    return $result;
  }

}


