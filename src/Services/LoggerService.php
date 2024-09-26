<?php

class LoggerService
{
    private $errorLogFile;
    private $infoLogFile;

    public function __construct($errorLogFile = '../Logs/error.log', $infoLogFile = '../Logs/info.log')
    {
        $this->errorLogFile = $errorLogFile;
        $this->infoLogFile = $infoLogFile;
        $this->createLogFileIfNotExists($this->errorLogFile);
        $this->createLogFileIfNotExists($this->infoLogFile);
    }

    public function logError($message)
    {
        $this->logMessage($this->errorLogFile, 'ERROR', $message);
    }

    public function logInfo($message)
    {
        $this->logMessage($this->infoLogFile, 'INFO', $message);
    }

    private function logMessage($logFile, $level, $message)
    {
        $formattedMessage = "[" . date("Y-m-d H:i:s") . "] $level: " . $message . "\n";
        if (is_writable($logFile)) {
            error_log($formattedMessage, 3, $logFile);
        } else {
            // Handle error if the log file is not writable
            error_log("[$level] Unable to write to log file: " . $logFile, 0);
        }
    }

    private function createLogFileIfNotExists($logFile)
    {
        $logDir = dirname($logFile);

        // Check if directory exists, if not create it
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        // Check if the file exists, if not create it
        if (!file_exists($logFile)) {
            file_put_contents($logFile, ""); // Create empty log file
            chmod($logFile, 0666); // Set appropriate permissions
        }
    }
}
