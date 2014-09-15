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

}


