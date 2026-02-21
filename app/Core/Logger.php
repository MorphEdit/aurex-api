<?php

declare(strict_types=1);

namespace App\Core;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;

class Logger
{
    private static ?MonologLogger $instance = null;

    public static function getInstance(): MonologLogger
    {
        if (self::$instance === null) {
            $logPath  = BASE_PATH . '/' . ($_ENV['LOG_PATH'] ?? 'logs/app.log');
            $logLevel = strtolower($_ENV['LOG_LEVEL'] ?? 'debug');

            $levelMap = [
                'debug'   => MonologLogger::DEBUG,
                'info'    => MonologLogger::INFO,
                'warning' => MonologLogger::WARNING,
                'error'   => MonologLogger::ERROR,
            ];

            $handler = new StreamHandler($logPath, $levelMap[$logLevel] ?? MonologLogger::DEBUG);
            $handler->setFormatter(new JsonFormatter());

            self::$instance = new MonologLogger('aurex');
            self::$instance->pushHandler($handler);
        }

        return self::$instance;
    }

    public static function info(string $message, array $context = []): void
    {
        self::getInstance()->info($message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::getInstance()->error($message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::getInstance()->warning($message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        self::getInstance()->debug($message, $context);
    }

    private function __construct() {}
    private function __clone()    {}
}
