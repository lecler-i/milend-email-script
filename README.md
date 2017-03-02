![Logo of the project](https://milend.com/wp-content/uploads/2016/06/milend-logo-transparent-new.png)

# Milend Email Script

A PHP script that will scan a Inbox and send a follow-up email to a possible customer.

You will find logs in the directory "logs"

## Installing / Getting started

A quick introduction of the minimal setup you need to get the script up &
running.

####Get composer

This project require "Composer" a package manager, to install various dependencies.

https://getcomposer.org/

####PHP requirements
The following modules should be enabled
> php_mbstring php_imap php_openssl

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

####Inbox config

You will have to create a new Label named "Processed", where all the email aldready processed will be moved.
In your Email Inbox left menu, click on "Create new label"

![Gmail Label](http://c.thomas.sh/index.php/apps/files_sharing/ajax/publicpreview.php?x=1896&y=508&a=true&file=ocss_2017-02-28_18.44.48.png&t=2XKG4rxHuRNpWv8&scalingup=0)

And set the name as

>Processed

####Start the script

Install the depencies

```shell
php composer.phar install
```

Run the script

```shell
php cli.php --dry-run # launch the script without sending the messages (for test purpose)
php cli.php --log-level debug # set log to debug
```

You can find logs in the "logs/" folder.

Adjust the log-level (to debug for more infos) by excuting with the following flag
> --log-level debug

## Developing

Here's a brief intro about what a developer must do in order to start developing
the project further:

```shell
git clone https://github.com/lecler-i/milend-email-script.git
cd milend-email-script/
composer install
```
