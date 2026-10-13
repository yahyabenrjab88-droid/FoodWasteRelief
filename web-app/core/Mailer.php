<?php
declare(strict_types=1);

class Mailer
{
    private string $host;
    private int    $port;
    private string $username;
    private string $password;
    private string $from;
    private string $fromName;
    private array  $errors = [];

    public function __construct()
    {
        $cfg = require __DIR__ . '/../config/mail.php';
        $this->host     = $cfg['host'];
        $this->port     = (int)$cfg['port'];
        $this->username = $cfg['username'];
        $this->password = $cfg['password'];
        $this->from     = $cfg['from'];
        $this->fromName = $cfg['from_name'];
    }

    public function send(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        if (empty($textBody)) {
            $textBody = strip_tags($htmlBody);
        }

        $errStr = '';
        $errNo  = 0;
        $conn   = @fsockopen($this->host, $this->port, $errNo, $errStr, 10);

        if (!$conn) {
            error_log("Mailer: Could not connect to {$this->host}:{$this->port} — $errStr");
            return false;
        }

        try {
            $this->expect($conn, '220');

            $this->send_cmd($conn, "EHLO " . gethostname());
            $this->read($conn); 

            $this->send_cmd($conn, "STARTTLS");
            $this->expect($conn, '220');

            if (!stream_socket_enable_crypto($conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new \RuntimeException("Mailer: STARTTLS crypto upgrade failed.");
            }

            $this->send_cmd($conn, "EHLO " . gethostname());
            $this->read($conn);

            $this->send_cmd($conn, "AUTH LOGIN");
            $this->expect($conn, '334');
            $this->send_cmd($conn, base64_encode($this->username));
            $this->expect($conn, '334');
            $this->send_cmd($conn, base64_encode($this->password));
            $this->expect($conn, '235'); 

            $this->send_cmd($conn, "MAIL FROM:<{$this->from}>");
            $this->expect($conn, '250');

            $this->send_cmd($conn, "RCPT TO:<$to>");
            $this->expect($conn, '250');

            $this->send_cmd($conn, "DATA");
            $this->expect($conn, '354');

            $boundary = md5(uniqid((string)time(), true));
            $headers  = implode("\r\n", [
                "From: {$this->fromName} <{$this->from}>",
                "To: $to",
                "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=",
                "MIME-Version: 1.0",
                "Content-Type: multipart/alternative; boundary=\"$boundary\"",
                "X-Mailer: FoodWasteRelief-PHP-Mailer",
            ]);

            $body = "--$boundary\r\n"
                  . "Content-Type: text/plain; charset=UTF-8\r\n\r\n"
                  . $textBody . "\r\n\r\n"
                  . "--$boundary\r\n"
                  . "Content-Type: text/html; charset=UTF-8\r\n\r\n"
                  . $htmlBody . "\r\n\r\n"
                  . "--$boundary--";

            fwrite($conn, $headers . "\r\n\r\n" . $body . "\r\n.\r\n");
            $this->expect($conn, '250');

            $this->send_cmd($conn, "QUIT");
            fclose($conn);
            return true;

        } catch (\RuntimeException $e) {
            error_log("Mailer error: " . $e->getMessage());
            if (is_resource($conn)) fclose($conn);
            return false;
        }
    }

    private function send_cmd($conn, string $cmd): void
    {
        fwrite($conn, $cmd . "\r\n");
    }

    private function read($conn): string
    {
        $response = '';
        while ($line = fgets($conn, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') break; 
        }
        return $response;
    }

    private function expect($conn, string $code): void
    {
        $response = $this->read($conn);
        if (strpos($response, $code) !== 0) {
            throw new \RuntimeException("SMTP expected $code, got: " . trim($response));
        }
    }
}
