<?php
declare(strict_types=1);

/**
 * Copy this file to config.php and fill in your real values.
 * You can also set any of these via environment variables and leave the constants empty.
 */

// Twilio
const TWILIO_ACCOUNT_SID = 'SID';   // getenv('TWILIO_ACCOUNT_SID') ?: 'AC...';
const TWILIO_AUTH_TOKEN  = 'TOKEN';   // getenv('TWILIO_AUTH_TOKEN') ?: '...';
const TWILIO_FROM_NUMBER = 'NUMBER';   // getenv('TWILIO_FROM_NUMBER') ?: '+1XXXXXXXXXX';

// Alma
const ALMA_API_KEY       = 'KEY';   // getenv('ALMA_API_KEY') ?: '...';
// Base Alma API endpoint for your region. Typical values:
//   North America: https://api-na.hosted.exlibrisgroup.com
//   Europe:       https://api-eu.hosted.exlibrisgroup.com
//   APAC:         https://api-ap.hosted.exlibrisgroup.com
const ALMA_API_BASE      = 'https://api-na.hosted.exlibrisgroup.com';

/**
 * Optional: set a default time zone for logs and date handling.
 */
date_default_timezone_set('America/New_York');
