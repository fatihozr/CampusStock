<?php

namespace App\Services;

use App\Config\App;
use Exception;

class MailService
{
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private string $encryption;
    private string $fromAddress;
    private string $fromName;

    public function __construct()
    {
        $dbModel = new \App\Models\SmtpSetting();
        $dbSettings = $dbModel->get();

        if (!empty($dbSettings['smtp_host']) && !empty($dbSettings['smtp_username'])) {
            $this->host        = trim($dbSettings['smtp_host']);
            $this->port        = (int)($dbSettings['smtp_port'] ?? 587);
            $this->username    = trim($dbSettings['smtp_username']);
            $this->password    = (string)($dbSettings['smtp_password'] ?? '');
            $this->encryption  = strtolower(trim($dbSettings['smtp_encryption'] ?? 'tls'));
            $this->fromAddress = trim($dbSettings['smtp_from_address'] ?? 'envanter@belediye.bel.tr');
            $this->fromName    = trim($dbSettings['smtp_from_name'] ?? 'T.C. Belediye Envanter Sistem Raporu');
        } else {
            $this->host        = trim((string)App::get('MAIL_HOST', ''));
            $this->port        = (int)App::get('MAIL_PORT', 587);
            $this->username    = trim((string)App::get('MAIL_USERNAME', ''));
            $this->password    = (string)App::get('MAIL_PASSWORD', '');
            $this->encryption  = strtolower(trim((string)App::get('MAIL_ENCRYPTION', 'tls')));
            $this->fromAddress = trim((string)App::get('MAIL_FROM_ADDRESS', 'envanter@belediye.bel.tr'));
            $this->fromName    = trim((string)App::get('MAIL_FROM_NAME', 'T.C. Belediye Envanter Sistem Raporu'));
        }
    }

    /**
     * Check if SMTP settings are configured in DB or .env
     */
    public function isConfigured(): bool
    {
        return !empty($this->host) && !empty($this->username);
    }

    /**
     * Test SMTP socket connection for configuration diagnostics
     */
    public function testSmtpConnection(?array $customSettings = null): array
    {
        $host       = !empty($customSettings['smtp_host']) ? trim($customSettings['smtp_host']) : $this->host;
        $port       = !empty($customSettings['smtp_port']) ? (int)$customSettings['smtp_port'] : $this->port;
        $username   = !empty($customSettings['smtp_username']) ? trim($customSettings['smtp_username']) : $this->username;
        $password   = isset($customSettings['smtp_password']) && $customSettings['smtp_password'] !== '********' ? (string)$customSettings['smtp_password'] : $this->password;
        $encryption = !empty($customSettings['smtp_encryption']) ? strtolower(trim($customSettings['smtp_encryption'])) : $this->encryption;

        if (empty($host) || empty($username)) {
            return [
                'success' => false,
                'code'    => 'NOT_CONFIGURED',
                'message' => 'SMTP yapılandırması yapılmamış. Ayarlar → E-posta Ayarları bölümünden SMTP bilgilerini girin.'
            ];
        }

        try {
            $socketHost = ($encryption === 'ssl' ? 'ssl://' : '') . $host;
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true
                ]
            ]);

            $socket = @stream_socket_client($socketHost . ':' . $port, $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);
            if (!$socket) {
                throw new Exception("SMTP sunucusuna bağlanılamadı ({$host}:{$port}) - {$errstr}");
            }

            stream_set_timeout($socket, 10);

            $readResponse = function() use ($socket) {
                $response = '';
                while ($str = fgets($socket, 512)) {
                    $response .= $str;
                    if (substr($str, 3, 1) === ' ') break;
                }
                return $response;
            };

            $sendCommand = function(string $cmd) use ($socket, $readResponse) {
                fputs($socket, $cmd . "\r\n");
                return $readResponse();
            };

            $res = $readResponse();
            if (substr($res, 0, 3) !== '220') {
                fclose($socket);
                throw new Exception("SMTP Sunucu Karşılama Hatası: " . trim($res));
            }

            $res = $sendCommand('EHLO ' . gethostname());

            if ($encryption === 'tls') {
                $res = $sendCommand('STARTTLS');
                if (substr($res, 0, 3) === '220') {
                    if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT)) {
                        fclose($socket);
                        throw new Exception("TLS Şifreleme handshake başarısız.");
                    }
                    $res = $sendCommand('EHLO ' . gethostname());
                }
            }

            if (!empty($username)) {
                $res = $sendCommand('AUTH LOGIN');
                if (substr($res, 0, 3) === '334') {
                    $res = $sendCommand(base64_encode($username));
                    if (substr($res, 0, 3) === '334') {
                        $res = $sendCommand(base64_encode($password));
                        if (substr($res, 0, 3) !== '235') {
                            fclose($socket);
                            throw new Exception("Kullanıcı adı veya şifre hatalı (AUTH LOGIN reddedildi).");
                        }
                    }
                }
            }

            $sendCommand('QUIT');
            fclose($socket);

            return [
                'success' => true,
                'message' => 'SMTP bağlantısı başarılı.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'SMTP bağlantı testi başarısız: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Parse and validate comma or semicolon separated email addresses
     */
    public function parseEmails(string $emailStr): array
    {
        if (empty(trim($emailStr))) return [];
        $raw = preg_split('/[,;]+/', $emailStr);
        $valid = [];
        foreach ($raw as $e) {
            $clean = trim($e);
            if (!empty($clean) && filter_var($clean, FILTER_VALIDATE_EMAIL)) {
                $valid[] = $clean;
            }
        }
        return array_unique($valid);
    }

    /**
     * Sanitize header string to prevent header injection
     */
    private function sanitizeHeader(string $header): string
    {
        return str_replace(["\r", "\n", "%0A", "%0D"], '', trim($header));
    }

    /**
     * Send report mail with attachment via SMTP socket
     */
    public function sendReportMail(array $params): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'code'    => 'NOT_CONFIGURED',
                'message' => 'SMTP yapılandırması yapılmamış. Lütfen .env dosyasındaki MAIL_HOST, MAIL_USERNAME ve MAIL_PASSWORD ayarlarını giriniz.'
            ];
        }

        $toStr  = $params['to'] ?? '';
        $ccStr  = $params['cc'] ?? '';
        $bccStr = $params['bcc'] ?? '';

        $toEmails  = $this->parseEmails($toStr);
        $ccEmails  = $this->parseEmails($ccStr);
        $bccEmails = $this->parseEmails($bccStr);

        if (empty($toEmails)) {
            return [
                'success' => false,
                'code'    => 'INVALID_RECIPIENT',
                'message' => 'Lütfen en az bir geçerli alıcı e-posta adresi giriniz!'
            ];
        }

        $subject = $this->sanitizeHeader($params['subject'] ?? 'Sistem Raporu');
        $body    = trim($params['body'] ?? 'Merhaba, Talep edilen rapor ekte sunulmuştur.');

        $attachmentPath = $params['attachment_path'] ?? '';
        $attachmentName = $this->sanitizeHeader($params['attachment_name'] ?? 'Rapor.pdf');

        if (!file_exists($attachmentPath) || !is_readable($attachmentPath)) {
            return [
                'success' => false,
                'code'    => 'FILE_NOT_FOUND',
                'message' => 'Gönderilecek rapor dosyası oluşturulamadı veya bulunamadı!'
            ];
        }

        try {
            $this->sendViaSmtpSocket($toEmails, $ccEmails, $bccEmails, $subject, $body, $attachmentPath, $attachmentName);
            return [
                'success' => true,
                'message' => 'Rapor başarıyla e-posta ile gönderildi.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'code'    => 'SMTP_ERROR',
                'message' => 'Rapor gönderilemedi. SMTP ayarlarını veya alıcı adresini kontrol edin. (' . $e->getMessage() . ')'
            ];
        }
    }

    /**
     * Native Socket-based SMTP Client (EHLO, STARTTLS, AUTH LOGIN, MIME Multipart)
     */
    private function sendViaSmtpSocket(
        array $toEmails,
        array $ccEmails,
        array $bccEmails,
        string $subject,
        string $body,
        string $attachmentPath,
        string $attachmentName
    ): void {
        $socketHost = ($this->encryption === 'ssl' ? 'ssl://' : '') . $this->host;
        $context = stream_context_create([
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ]);

        $socket = @stream_socket_client($socketHost . ':' . $this->port, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
        if (!$socket) {
            throw new Exception("SMTP sunucusuna bağlanılamadı ({$this->host}:{$this->port}) - {$errstr}");
        }

        stream_set_timeout($socket, 15);

        $readResponse = function() use ($socket) {
            $response = '';
            while ($str = fgets($socket, 512)) {
                $response .= $str;
                if (substr($str, 3, 1) === ' ') break;
            }
            return $response;
        };

        $sendCommand = function(string $cmd) use ($socket, $readResponse) {
            fputs($socket, $cmd . "\r\n");
            return $readResponse();
        };

        $res = $readResponse();
        if (substr($res, 0, 3) !== '220') {
            fclose($socket);
            throw new Exception("SMTP Sunucu Karşılama Hatası: " . trim($res));
        }

        $res = $sendCommand('EHLO ' . gethostname());

        if ($this->encryption === 'tls') {
            $res = $sendCommand('STARTTLS');
            if (substr($res, 0, 3) === '220') {
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT)) {
                    fclose($socket);
                    throw new Exception("TLS Şifreleme başlatılamadı.");
                }
                $res = $sendCommand('EHLO ' . gethostname());
            }
        }

        if (!empty($this->username)) {
            $res = $sendCommand('AUTH LOGIN');
            if (substr($res, 0, 3) === '334') {
                $res = $sendCommand(base64_encode($this->username));
                if (substr($res, 0, 3) === '334') {
                    $res = $sendCommand(base64_encode($this->password));
                    if (substr($res, 0, 3) !== '235') {
                        fclose($socket);
                        throw new Exception("SMTP Kullanıcı Kimlik Doğrulama Hatası (AUTH LOGIN).");
                    }
                }
            }
        }

        $sendCommand('MAIL FROM: <' . $this->fromAddress . '>');

        $allRecipients = array_unique(array_merge($toEmails, $ccEmails, $bccEmails));
        foreach ($allRecipients as $rcpt) {
            $sendCommand('RCPT TO: <' . $rcpt . '>');
        }

        $sendCommand('DATA');

        // Build MIME Multipart Message
        $boundary = '=_Rand_Bound_' . md5(uniqid(time(), true));
        $fileData = file_get_contents($attachmentPath);
        $fileBase64 = chunk_split(base64_encode($fileData));

        $ext = strtolower(pathinfo($attachmentName, PATHINFO_EXTENSION));
        $mimeType = match($ext) {
            'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default => 'application/pdf',
        };

        $headers = [];
        $headers[] = 'From: ' . "=?UTF-8?B?" . base64_encode($this->fromName) . "?=" . ' <' . $this->fromAddress . '>';
        $headers[] = 'To: ' . implode(', ', $toEmails);
        if (!empty($ccEmails))  $headers[] = 'Cc: ' . implode(', ', $ccEmails);
        $headers[] = 'Subject: ' . "=?UTF-8?B?" . base64_encode($subject) . "?=";
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';

        $msgBody  = implode("\r\n", $headers) . "\r\n\r\n";
        $msgBody .= "--" . $boundary . "\r\n";
        $msgBody .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $msgBody .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $msgBody .= $body . "\r\n\r\n";

        $msgBody .= "--" . $boundary . "\r\n";
        $msgBody .= "Content-Type: {$mimeType}; name=\"{$attachmentName}\"\r\n";
        $msgBody .= "Content-Disposition: attachment; filename=\"{$attachmentName}\"\r\n";
        $msgBody .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $msgBody .= $fileBase64 . "\r\n";
        $msgBody .= "--" . $boundary . "--\r\n.";

        $res = $sendCommand($msgBody);
        $sendCommand('QUIT');
        fclose($socket);

        if (substr($res, 0, 3) !== '250') {
            throw new Exception("E-posta gönderimi tamamlanamadı: " . trim($res));
        }
    }
}
