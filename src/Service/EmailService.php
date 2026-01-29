<?php

namespace App\Service;

use App\Config\Constants;
use App\Utility\Utility;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\Service\Attribute\Required;

class EmailService
{
  private string $to;
  private string $subject;
  private string $body;
  private string $html;
  private MailerInterface $mailer;
  #[Target(Constants::LOG_CHANNELS['email_content'])]
  private LoggerInterface $mailContentLogger;
  #[Target(Constants::LOG_CHANNELS['email_service'])]
  private LoggerInterface $mailServiceLogger;
  #[Autowire('%kernel.environment%')]
  private string $environment;

  #[Required]
  public function initService(
    MailerInterface $mailer,
    LoggerInterface $mailContentLogger,
    LoggerInterface $mailServiceLogger
  ): void {
    $this->mailer = $mailer;
    $this->mailContentLogger = $mailContentLogger;
    $this->mailServiceLogger = $mailServiceLogger;
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
    $this->mailContentLogger->info('Email content', [
      'email_id' => $emailId,
      'to' => $this->to,
      'subject' => $this->subject,
      'body' => $this->body,
      'html' => $this->html,
    ]);

    try {
      $this->mailServiceLogger->debug('Sending email', [
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
      $this->mailServiceLogger->error('Email sending process failed', [
        'email_id' => $emailId,
        'error' => $e->getMessage(),
      ]);

      if ($this->environment ===  Constants::APP_ENV['production']) {
        return false;
      }
    }

    return true;
  }
}
