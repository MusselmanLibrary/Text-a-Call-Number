# Text-a-Call-Number
Text a call number code for use with Twilio and Primo VE

Adapted from the [Williams College SMS code](https://github.com/emery-williams/sms) by [Emery Williams](https://github.com/emery-williams)

## Requirements
* Alma API key (Bibs Production Read/Write)
* Web server with PHP and SSL enabled
* Twilio SMS account and PHP Helper Library

## Instructions

1. Create a [Twilio](https://twilio.com) account and get an SMS phone number, account SID, and auth token
2. Put the [PHP Helper Library](https://www.twilio.com/docs/libraries/php) on a PHP-enabled webserver with an SSL certificate
3. Update the `alma-api-for-sms.php` file with your Alma API key for Bibs - production read/write
4. Update the `send-sms.php` file with your Twilio account ID and auth token, as well as your Twilio SMS number
5. Update the `text-a-call-number.php` file with the appropriate branding
6. Update the image files with the appropriate branding
7. Place all files on the server
8. In Alma, create an item-level General Electronic Service with the URL template `https://path/to/text-a-call-number.php?title={rft.title}&mms={rft.mms_id}`
