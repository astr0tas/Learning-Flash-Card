<?php

namespace App\Service;

use App\Config\Constants;
use App\Utility\Logger;
use App\Utility\Utility;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class EmailService extends BaseService
{
  private string $to;
  private string $subject;
  private string $body;
  private string $html;
  private Logger $mailContentLogger;
  private Logger $mailServiceLogger;

  public function __construct(
    private MailerInterface $mailer,
    #[Autowire('%kernel.environment%')]
    private string $environment
  ) {
    $this->mailContentLogger = new Logger(Logger::EMAIL_CONTENT_LOGGER_CONFIG);
    $this->mailServiceLogger = new Logger(Logger::EMAIL_SERVICE_LOGGER_CONFIG);
  }

  public function setTo(string $to): self
  {
    $this->to = $to;
    return $this;
  }

  public function setSubject(string $subject): self
  {
    $this->subject = $subject;
    return $this;
  }

  public function setBody(string $body): self
  {
    $this->body = $body;
    return $this;
  }

  public function setHtml(string $html): self
  {
    $this->html = $html;
    return $this;
  }

  public function sendEmail(): bool
  {
    // Check if all required fields are set
    if (empty($this->to) || empty($this->subject) || (empty($this->body) && empty($this->html))) {
      return false;
    }

    // Write down the email details to the log for debugging
    $emailId = Utility::generateRandomToken();
    $this->mailContentLogger->writeLog(Logger::LOG_LEVEL_INFO, 'Email content', [
      'email_id' => $emailId,
      'to' => $this->to,
      'subject' => $this->subject,
      'body' => $this->body,
      'html' => $this->html,
    ]);

    try {
      $this->mailServiceLogger->writeLog(Logger::LOG_LEVEL_DEBUG, 'Sending email', [
        'email_id' => $emailId,
        'to' => $this->to,
      ]);

      $this->mailer->send((new Email())
          ->to($this->to)
          ->subject($this->subject)
          ->text($this->body ?? '')
          ->html($this->html ?? '')
      );
    } catch (TransportExceptionInterface $e) {
      $this->mailServiceLogger->writeLog(Logger::LOG_LEVEL_ERROR, 'Email sending process failed', [
        'email_id' => $emailId,
        'error' => $e->getMessage(),
      ]);

      if ($this->environment ===  Constants::APP_ENV_DEVELOPMENT) {
        return false;
      }
    }

    return true;
  }
}
