<?php

class ErrorMonitor
{
    private $log;

    public function __construct($logger)
    {
        $this->log = $logger;
    }

    // =========================
    // 🧠 PHP ERROR HANDLER
    // =========================
    public function register()
    {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);

        register_shutdown_function([$this, 'handleFatal']);
    }

    // =========================
    // ❌ NORMAL ERROR
    // =========================
    public function handleError($severity, $message, $file, $line)
    {
        $this->log->error("PHP ERROR", [
            "message" => $message,
            "file" => $file,
            "line" => $line,
            "severity" => $severity
        ]);
    }

    // =========================
    // 💥 EXCEPTION
    // =========================
    public function handleException($e)
    {
        $this->log->critical("UNCAUGHT EXCEPTION", [
            "message" => $e->getMessage(),
            "file" => $e->getFile(),
            "line" => $e->getLine()
        ]);
    }

    // =========================
    // 💣 FATAL ERROR
    // =========================
    public function handleFatal()
    {
        $error = error_get_last();

        if ($error) {
            $this->log->critical("FATAL ERROR", $error);
        }
    }
}
?>
