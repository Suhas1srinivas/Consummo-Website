<?php
/**
 * Consummo form settings: TEMPLATE.
 *
 * On the server: copy this file to config.php in the same folder and fill it in.
 * config.php is excluded from Git, so passwords never reach GitHub.
 */
return [
    // Where new leads are sent
    'to_email' => 'your-inbox@example.com',

    // Sender. Must be a mailbox that exists on your domain (create it in Hostinger hPanel > Emails)
    'from_email' => 'no-reply@consummo.com',
    'from_name'  => 'Consummo Website',

    // Send the visitor a "we received your request" email
    'send_confirmation' => true,
    'reply_to' => 'hello@consummo.com',
    'whatsapp_link' => 'https://wa.me/910000000000',

    // Max submissions per visitor per hour
    'rate_limit_per_hour' => 5,

    // Recommended: send through your Hostinger mailbox (better delivery to Gmail).
    // Leave 'password' empty to fall back to PHP mail().
    'smtp' => [
        'host'     => 'smtp.hostinger.com',
        'port'     => 465,
        'username' => 'no-reply@consummo.com',
        'password' => '',
    ],
];
