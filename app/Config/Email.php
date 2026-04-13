<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail  = 'qinjiwei5@gmail.com';
    public string $fromName   = 'General Affairs Inventory System';
    public string $recipients = '';
    public string $userAgent  = 'CodeIgniter';
    public string $protocol   = 'smtp';
    public string $SMTPHost   = 'smtp.gmail.com';
    public string $SMTPAuthMethod = 'login';
    public string $SMTPUser   = 'qinjiwei5@gmail.com';
    public string $SMTPPass   = 'qyie bkht aoqf ipbt';

    // This is the fix: Type is int, Value is 465. No quotes.
    public int $SMTPPort = 587; 

    public int    $SMTPTimeout = 10;
    public bool   $SMTPKeepAlive = false;
    public string $SMTPCrypto    = 'tls';
    public string $mailType      = 'html';
    public string $charset       = 'UTF-8';
    public string $CRLF          = "\r\n";
    public string $newline       = "\r\n";

    public array $SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];
}