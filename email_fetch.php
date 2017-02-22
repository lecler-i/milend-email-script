<?php

$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = 'ttest@milend.com';
$password = 'thomas1234';

/* try to connect */
$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());

/* grab emails */
$emails = imap_search($inbox,'ALL');

/* if emails are returned, cycle through each... */
if($emails) {
  
  /* begin output var */
  $output = '';
  
  /* put the newest emails on top */
  rsort($emails);
  
  /* for every email... */
  foreach($emails as $email_number) {
    
    /* get information specific to this email */
    $overview = imap_fetch_overview($inbox,$email_number,0);

    //var_dump($overview);
    /* If the email is from... */
    if ($overview[0]->from == "no-reply@leadpoint.com" || true) {

      /*Get the body of the email */
      $message = imap_fetchbody($inbox,$email_number,1);
      $matches = [];

      /* Check for customer email in the body */
      if (preg_match("/\*Email\* (.*@.*\..*)/", $message, $matches)) {

        /* Processing the email */
        found_email_match($overview, $message, $matches[1]);
      }
    }
  }
}

/* Function that process a email matching with customer infos */
function found_email_match($emailInfos, $content, $customer_email) {
  printf("Email is : %s\n", $customer_email);
}

/* close the connection */
imap_close($inbox);