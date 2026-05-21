<?php

namespace App\Utility;

use App\Config\Constants;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;
use MonoLog\Level as LogLevel;

class Logger
{  // Log channels
  public const logDirectoryPath = __DIR__ . '/../../var/log/';
  public const EMAIL_SERVICE_LOGGER_CONFIG = [
    'channel' => 'email_service',
    'location' => Logger::logDirectoryPath . 'email_service.log',
  ];
  public const EMAIL_CONTENT_LOGGER_CONFIG = [
    'channel' => 'email_content',
    'location' => Logger::logDirectoryPath . 'email_content.log',
  ];

  // Log levels
  public const LOG_LEVEL_INFO = 'INFO';
  public const LOG_LEVEL_DEBUG = 'DEBUG';
  public const LOG_LEVEL_NOTICE = 'NOTICE';
  public const LOG_LEVEL_WARNING = 'WARNING';
  public const LOG_LEVEL_ERROR = 'ERROR';
  public const LOG_LEVEL_CRITICAL = 'CRITICAL';
  public const LOG_LEVEL_ALERT = 'ALERT';
  public const LOG_LEVEL_EMERGENCY = 'EMERGENCY';

  // Logger
  private MonologLogger $logger;

  public function __construct(array $config)
  {
    $this->logger = new MonologLogger($config['channel']);

    // Create a handler: log records to a log file
    $fileHandler = new StreamHandler($config['location'], LogLevel::Debug);

    // Optional: Add a different handler, e.g., to output critical errors to the console
    $consoleHandler = new StreamHandler('php://stderr', LogLevel::Critical);

    // Add the handlers to the logger
    $this->logger->pushHandler($fileHandler);
    $this->logger->pushHandler($consoleHandler);
  }

  public function writeLog(string $level, string $message, array $context)
  {
    switch ($level) {
      case Logger::LOG_LEVEL_INFO:
        $this->logger->info($message, $context);
        break;
      case Logger::LOG_LEVEL_DEBUG:
        $this->logger->debug($message, $context);
        break;
      case Logger::LOG_LEVEL_NOTICE:
        $this->logger->notice($message, $context);
        break;
      case Logger::LOG_LEVEL_WARNING:
        $this->logger->warning($message, $context);
        break;
      case Logger::LOG_LEVEL_ERROR:
        $this->logger->error($message, $context);
        break;
      case Logger::LOG_LEVEL_CRITICAL:
        $this->logger->critical($message, $context);
        break;
      case Logger::LOG_LEVEL_ALERT:
        $this->logger->alert($message, $context);
        break;
      case Logger::LOG_LEVEL_EMERGENCY:
        $this->logger->emergency($message, $context);
        break;
      default:
        throw new \Exception(Constants::UNKNOWN_LOG_LEVEL);
    }
  }
}
