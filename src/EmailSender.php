<?php

/**
* 
*/
class EmailSender
{
  private $mailer = false;

  function __construct($host, $username, $password, $inbox, $options)
  {
    global $logger;

    $this->inbox = $inbox;
    $this->options = $options;

    
    /* Connect for outgoing email address */
    try {
      $logger->info('Connecting to outgoing mailbox : ' . $username);
      $transport = Swift_SmtpTransport::newInstance($host, 465, "ssl")
        ->setUsername($username)
        ->setPassword($password)
      ;
      $transport->start();
      $this->mailer = Swift_Mailer::newInstance($transport);
    } catch (Exception $e) {
      $logger->error($e->getMessage());
    }
  }

  public function loggedIn() {
    return (bool)$this->mailer;
  }

  public function processEmails($emails)
  {
    global $logger;

    if (!$this->loggedIn()) {
      return false;
    }

    $emailCounter = 0;
    $logger->info('Sending ' . count($emails) . ' emails');
    foreach ($emails as $email) {
      $emailAddress = $email['customerData']['Email'];
      $name = $email['customerData']['First Name'];

      /* If no name found, we set the name as the email */
      if (empty($name)) {
        $name = $email;
      }

      $logger->info('Sending email to : ' . $emailAddress);
      if ($this->sendEmail($emailAddress, $name)) {
        $emailCounter++;
        $this->inbox->flagEmail($email['emailInfos']->msgno);
      }
    }    
    return $emailCounter;
  }

  private function sendEmail($emailAddress, $name)
  {
    global $logger;

    try {
      /* Getting HTML and plain text body */
      ob_start();
      include __DIR__ . DIRECTORY_SEPARATOR . 'templates'. DIRECTORY_SEPARATOR .'email_body.html';
      $html_body = ob_get_clean();
      ob_start();
      include __DIR__ . DIRECTORY_SEPARATOR . 'templates'. DIRECTORY_SEPARATOR .'email_body.txt';
      $txt_body = ob_get_clean();

      /* Create the outgoing email */
      $message = Swift_Message::newInstance()
        ->setSubject('RE: Your home loan inquiry')
        ->setFrom(['info@milend.com'])
        ->setTo(['test@thomas.sh'])
        ->setBody($txt_body)
        ->addPart($html_body, 'text/html')
      ;

      /* Sending the email */
      if ($this->options['dry-run'] == false) {
        $result = $this->mailer->send($message);
      }
      $logger->info('Email sent with success');
      return true;
    } catch (Exception $e) {
      $logger->error($e->getMessage());
      return false;
    }

  }

}