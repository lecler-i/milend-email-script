<?php

/**
* 
*/
class Inbox
{
  
  public $results = [];
  public $inbox = false;

  function __construct($host, $username, $password, $options)
  {
    global $logger;
    $this->options = $options;

    /* try to connect */
    $logger->info('Connection to inbox : ' . $username);
    $this->inbox = imap_open($host, $username, $password);
    if ($this->inbox == false) {
      $logger->error('Error : ', imap_errors());
    }
    /* grab emails */
  }

  public function loggedIn()
  {
    return (bool)$this->inbox;
  }

  public function scanEmails($fromEmail = false, $seenCheck = true)
  {
    global $logger;

    $emails = imap_search($this->inbox, 'ALL');
    $this->emails_count = count($emails);
    
    $logger->info('Fetched email from Inbox : ' . $this->emails_count . ' emails');
    if ($emails) {
      /* put the newest emails on top */
      rsort($emails);

      /* for every email... */
      foreach ($emails as $email_number) {
        /* get information specific to this email */
        $overview = imap_fetch_overview($this->inbox, $email_number, 0);

        $logger->info('Checking email #' . $email_number . ' from ' . $overview[0]->from);
        /* If the email is from... */

        
        if ($fromEmail == false || preg_match($fromEmail, $overview[0]->from)) {
          $logger->debug('Reading email', (array)$overview[0]);

          /*Get the body of the email */
          $message = imap_fetchbody($this->inbox, $email_number, 1, FT_PEEK);
          $matches = [];

          /* Loading the customer infos from the body */
          if (preg_match_all("/\*(.*)\* (.*)/", $message, $matches) == false) {
            /* Couldn't find a proprely formatted table with user data */
            $logger->debug('No customer data found');
          } else {
            $customer_data = array_combine($matches[1], array_map('trim', $matches[2]));

            /* If customer Email is found */
            if (empty($customer_data["Email"])) {
              $logger->warning('Couldn\'t find customer email');
            } else {
              if ($overview[0]->seen == true && false) {
                /* We encounter a seen message... stopping the script */
                $logger->info('Aldready processed email reached... Stopping');
                break;
              }
              /* Processing the email */
              $this->emailMatch($overview[0], $message, $customer_data);
            }
          }
        } else {
          $logger->info('Doen\'t match');
        }
      }
    }
    return $this->results;
  }

  private function emailMatch($emailInfos, $content, $customerData)
  {
    global $logger;
  
    $email = [
      'emailInfos' => $emailInfos,
      'content' => $content,
      'customerData' => $customerData
    ];
    $this->results[] = $email;

    $logger->info('Email match, customer : ' . $customerData['Email']);
    $logger->debug('Data : ', $customerData);
  }

  public function flagEmail($msgno) {
    if ($this->options['dry-run'] == false) {
      //print_r($emailNb);
      //imap_setflag_full($this->inbox, $msgno, "\\Seen");
      imap_mail_move($this->inbox, $msgno, 'Processed'); 
    }
  }


}