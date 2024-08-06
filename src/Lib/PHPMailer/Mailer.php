<?php

namespace Lib\PHPMailer;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Lib\Validator;

class Mailer
{
    private PHPMailer $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->setup();
    }

    private function setup(): void
    {
        $this->mail->isSMTP();
        $this->mail->SMTPDebug = 0;
        $this->mail->Host = $_ENV['SMTP_HOST'];
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $_ENV['SMTP_USERNAME'];
        $this->mail->Password = $_ENV['SMTP_PASSWORD'];
        $this->mail->SMTPSecure = $_ENV['SMTP_ENCRYPTION'];
        $this->mail->Port = (int) $_ENV['SMTP_PORT'];
        $this->mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
    }

    /**
     * Send an email.
     *
     * @param string $to The recipient's email address.
     * @param string $subject The subject of the email.
     * @param string $body The HTML body of the email.
     * @param array $options (optional) Additional email options like name, altBody, CC, and BCC.
     *
     * @return bool Returns true if the email is sent successfully, false otherwise.
     *
     * @throws Exception Throws an exception if the email could not be sent.
     */
    public function send(string $to, string $subject, string $body, array $options = []): bool
    {
        try {
            // Validate and sanitize inputs
            $to = Validator::email($to);
            if (!$to) {
                throw new \Exception('Invalid email address for the main recipient');
            }

            $subject = Validator::string($subject);
            $body = Validator::html($body);
            $altBody = $this->convertToPlainText($body);

            $name = $options['name'] ?? '';
            $addCC = $options['addCC'] ?? [];
            $addBCC = $options['addBCC'] ?? [];

            $name = Validator::string($name);

            // Handle CC recipients
            $this->handleRecipients($addCC, 'CC');
            // Handle BCC recipients
            $this->handleRecipients($addBCC, 'BCC');

            // Set the main recipient and other email properties
            $this->mail->addAddress($to, $name);
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            $this->mail->AltBody = $altBody;

            // Send the email
            return $this->mail->send();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Handle adding CC or BCC recipients.
     *
     * @param string|array $recipients Email addresses to add.
     * @param string $type Type of recipient ('CC' or 'BCC').
     *
     * @throws Exception Throws an exception if any email address is invalid.
     */
    private function handleRecipients(string|array $recipients, string $type): void
    {
        if (!empty($recipients)) {
            $method = $type === 'CC' ? 'addCC' : 'addBCC';

            if (is_array($recipients)) {
                foreach ($recipients as $recipient) {
                    $recipient = Validator::email($recipient);
                    if ($recipient) {
                        $this->mail->{$method}($recipient);
                    } else {
                        throw new \Exception("Invalid email address in $type");
                    }
                }
            } else {
                $recipient = Validator::email($recipients);
                if ($recipient) {
                    $this->mail->{$method}($recipient);
                } else {
                    throw new \Exception("Invalid email address in $type");
                }
            }
        }
    }

    /**
     * Convert HTML content to plain text.
     *
     * @param string $html The HTML content to convert.
     * @return string The plain text content.
     */
    private function convertToPlainText(string $html): string
    {
        return strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $html));
    }
}
