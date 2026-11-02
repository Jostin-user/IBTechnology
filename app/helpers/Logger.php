<?php

class Logger
{
    private $logDir;
    private $levels = ['access', 'error', 'security', 'app'];

    public function __construct($logDir = null)
    {
        if ($logDir === null) {
            $logDir = __DIR__ . '/../../storage/Logs';
        }
        $this->logDir = rtrim($logDir, DIRECTORY_SEPARATOR);
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    private function getLogPath($type)
    {
        $type = in_array($type, $this->levels) ? $type : 'app';
        $date = date('Y-m-d');
        return $this->logDir . DIRECTORY_SEPARATOR . $type . "." . $date . ".log";
    }

    private function formatLine($level, $message, $context = [])
    {
        $time = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $ctx = '';
        if (!empty($context)) {
            $ctx = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        // ensure line is single-line (no newlines in message or context)
        $message = str_replace(["\r", "\n"], [' ', ' '], $message);
        $ctx = str_replace(["\r", "\n"], [' ', ' '], $ctx);
        return "[{$time}] [{$level}] [{$ip}] [{$method}] [{$uri}] {$message} {$ctx}" . PHP_EOL;
    }

    private function write($type, $level, $message, $context = [])
    {
        $path = $this->getLogPath($type);
        $line = $this->formatLine($level, $message, $context);
        // atomic append
        file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
    }

    public function access($message, $context = [])
    {
        $this->write('access', 'INFO', $message, $context);
    }

    public function info($message, $context = [])
    {
        $this->write('app', 'INFO', $message, $context);
    }

    public function error($message, $context = [])
    {
        $this->write('error', 'ERROR', $message, $context);
    }

    public function security($message, $context = [])
    {
        $this->write('security', 'WARN', $message, $context);
    }

    // Generic logger for custom types if needed
    public function log($type, $level, $message, $context = [])
    {
        $this->write($type, $level, $message, $context);
    }
}

?>