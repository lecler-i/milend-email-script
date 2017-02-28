![Logo of the project](https://milend.com/wp-content/uploads/2016/06/milend-logo-transparent-new.png)

# Milend Email Script

A PHP script that will scan a Inbox and send a follow-up email to a possible customer.
You will find logs in the directory "logs"

## Installing / Getting started

A quick introduction of the minimal setup you need to get the script up &
running.

###Get composer

This project require "Composer" a package manager, to install various dependencies.
https://getcomposer.org/

####Configuration

Edit the file config.ini to set the credentials for the ingoing/outgoing emails.

```config
[inbox]
imap = "{imap.gmail.com:993/imap/ssl}INBOX"
username = "to-scan@milend.com"
password = "password"

[outgoing]
host = "smtp.gmail.com"
username = "info@milend.com"
password = "password"
```

####Start the script

```shell
composer install
php cli.php --help  # prints the help
php cli.php --dry-run # launch the script without sending the messages (for test purpose)
php cli.php --log-level debug # set log to debug
```

Here you should say what actually happens when you execute the code above.

## Developing

Here's a brief intro about what a developer must do in order to start developing
the project further:

```shell
git clone https://github.com/lecler-i/milend-email-script.git
cd milend-email-script/
composer install
```
