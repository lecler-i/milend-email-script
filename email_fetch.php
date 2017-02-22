<?php
/* Require composer autoloader */
require __DIR__ . '/vendor/autoload.php';

/* Infos for mailbox to scan */
$in_hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$in_username = 'ttest@milend.com';
$in_password = 'thomas1234';

/* Infos for mailbox to send message from */
$out_username = 'ttest@milend.com';
$out_password = 'thomas1234';

/* Connect for outgoing email address */
$transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, "ssl")
  ->setUsername($out_username)
  ->setPassword($out_password);
$mailer = Swift_Mailer::newInstance($transport);

/* try to connect */
$inbox = imap_open($in_hostname, $in_username, $in_password) or die('Cannot connect to Gmail: ' . imap_last_error());

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
        if ($overview[0]->seen == false) {
          /* We encounter a seen message... stopping the script */
          echo 'Aldready processed message reached... Stopping';
          break;
        }

        /* Processing the email */
        found_email_match($overview[0], $message, $matches[1]);
      }
    }
  }
}

/* Function that process a email matching with customer infos */
function found_email_match($emailInfos, $content, $customer_email) {
  echo "Processing email : " . $emailInfos->msgno . "\n";
  echo "\tDate :\t\t" . $emailInfos->date . "\n";
  echo "\tCustomer :\t" . $customer_email . "\n";

  /* To access $mailer object (used to send emails) */
  global $mailer;

  /* Create the outgoing email */
  $message = Swift_Message::newInstance()
    ->setSubject('RE: Your home loan inquiry')
    ->setFrom(['info@milend.com' => 'John Doe'])
    ->setTo(['degnus@gmail.com'])
    ->setBody('Here is the message itself')
    ->addPart('<q>Here is the message itself</q>', 'text/html') ;

  /* Sending the email */
  $result = $mailer->send($message);
  echo "Done.\n";
}

/* close the connection */
imap_close($inbox);