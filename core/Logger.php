<?php

class Logger
{
    private $path;
    private $maxSize = 5242880; // 5MB per file
    private $retentionDays = 7; // auto delete old logs

    public function __construct($path = null)
    {
        $this->path = $path ?? __DIR__ . '/../storage/logs/';

        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }

        $this->cleanup(); // auto cleanup old logs
    }

    // =========================
    // 🧠 CORE WRITER
    // =========================
    private function write($level, $message, $context = [])
    {
        $date = date("Y-m-d");
        $time = date("Y-m-d H:i:s");

        $file = $this->path . "app-$date.log";

        // rotate if file too large
        if (file_exists($file) && filesize($file) > $this->maxSize) {
            rename($file, $this->path . "app-$date-" . time() . ".log");
        }

        if (!empty($context)) {
            $message .= " | " . json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        $line = "[$time] [$level] $message" . PHP_EOL;

        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    // =========================
    // 📊 LOG LEVELS
    // =========================
    public function info($msg, $ctx = [])     { $this->write("INFO", $msg, $ctx); }
    public function warning($msg, $ctx = [])  { $this->write("WARNING", $msg, $ctx); }
    public function error($msg, $ctx = [])    { $this->write("ERROR", $msg, $ctx); }
    public function debug($msg, $ctx = [])    { $this->write("DEBUG", $msg, $ctx); }
    public function security($msg, $ctx = []) { $this->write("SECURITY", $msg, $ctx); }
    public function cache($msg, $ctx = [])    { $this->write("CACHE", $msg, $ctx); }
    public function sql($query, $params = []) { $this->write("SQL", $query, $params); }

    // =========================
    // 🧹 CLEANUP OLD LOGS
    // =========================
    private function cleanup()
    {
        foreach (glob($this->path . "*.log") as $file) {

            if (time() - filemtime($file) > ($this->retentionDays * 86400)) {
                unlink($file);
            }
        }
    }

    // =========================
    // 📖 READ LOGS (FOR DASHBOARD)
    // =========================
    public function read($date = null)
    {
        $date = $date ?? date("Y-m-d");
        $file = $this->path . "app-$date.log";

        if (!file_exists($file)) return [];

        return file($file, FILE_IGNORE_NEW_LINES);
    }

    // =========================
    // 🔍 FILTER LOGS
    // =========================
    public function filter($level, $date = null)
    {
        $logs = $this->read($date);

        return array_values(array_filter($logs, function ($line) use ($level) {
            return str_contains($line, "[$level]");
        }));
    }
}
?>
