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

    // Send the visitor a "we received your request" email (max 2 per address per day)
    'send_confirmation' => true,
    'reply_to' => 'hello@consummo.com',
    'whatsapp_link' => 'https://wa.me/910000000000',

    // Abuse limits
    'rate_limit_per_hour' => 5,   // per visitor
    'daily_cap' => 150,           // total submissions per day

    // Optional: store leads outside the public website folder, e.g. '/home/u123456789/consummo-data'
    // 'data_dir' => '/home/u123456789/consummo-data',

    // Extra site addresses allowed to post the form (the current domain is always allowed)
    'allowed_hosts' => ['consummo.com', 'www.consummo.com'],

    // Set to true only if the site sits behind Cloudflare
    'trust_cloudflare' => false,

    // Recommended: send through your Hostinger mailbox (better delivery to Gmail).
    // Leave 'password' empty to fall back to PHP mail().
    'smtp' => [
        'host'     => 'smtp.hostinger.com',
        'port'     => 465,
        'username' => 'no-reply@consummo.com',
        'password' => '',
    ],
];
