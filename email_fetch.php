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
$emails_count = count($emails);

/* if emails are returned, cycle through each... */
echo 'Fetched email from Inbox : ' . $emails_count . " emails\n";
if($emails) {
  
  /* put the newest emails on top */
  rsort($emails);
  
  /* for every email... */
  foreach($emails as $email_number) {
    
    /* get information specific to this email */
    $overview = imap_fetch_overview($inbox, $email_number, 0);

    /* If the email is from... */
    if ($overview[0]->from == "no-reply@leadpoint.com") {
      echo "Processing email " . $email_number . " from : " . $overview[0]->date . ": ";
      /*Get the body of the email */
      $message = imap_fetchbody($inbox,$email_number,1);
      $matches = [];

      /* Loading the customer infos from the body */
      if (preg_match_all("/\*(.*)\* (.*)/", $message, $matches) == false) {
        /* Couldn't find a proprely formatted table with user data */
        echo "No customer data found.\n";
      } else {
        $customer_data = array_combine($matches[1], $matches[2]);
        
        /* If customer Email is found */
        if (empty($customer_data["Email"])) {
          echo "Couldn't find customer email.\n";
        } else {
          if ($overview[0]->seen == true && false) {
            /* We encounter a seen message... stopping the script */
            echo "Aldready processed message reached... Stopping.\n";
            break;
          }
          /* Processing the email */
          found_email_match($overview[0], $message, $customer_data);
        }
      }
    }
  }
}

/* Function that process a email matching with customer infos */
function found_email_match($emailInfos, $content, $customer_data) {
  $email = $customer_data["Email"];
  $name = $customer_data["First Name"];

  /* If no name found, we set the name as the email */
  if (empty($name)) $name = $email;

  echo "Sending email to " . $email . "... ";

  /* Getting HTML and plain text body */
  ob_start();
  include 'email_body.html';
  $html_body = ob_get_clean();
  ob_start();
  include 'email_body.txt';
  $txt_body = ob_get_clean();

  try {
    /* Create the outgoing email */
    $message = Swift_Message::newInstance()
      ->setSubject('RE: Your home loan inquiry')
      ->setFrom(['info@milend.com'])
      ->setTo([$email])
      ->setBody($txt_body)
      ->addPart($html_body, 'text/html')
    ;

    global $mailer;

    /* Sending the email */
    $result = $mailer->send($message);
    echo "Done.\n";

  } catch (Exception $e) {
    echo "Failed\n";
  }
}

/* close the connection */
imap_close($inbox);