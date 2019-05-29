<?php

class Logger
{
    protected $debug_mode = true;
    
    public function __construct($debug_mode)
    {
        $this->debug_mode = $debug_mode;
        $log = "<p style=\"color:#000000;\">[".$this->getTime()."] >>> The Logger has been activated.</p>";
        if($this->debug_mode) print($log);
    }

    public function logRegular($text)
    {
        $log = "<p style=\"color:#000000;\">[".$this->getTime()."] >>> ".$text."</p>";
        if($this->debug_mode) print($log);
        $this->writeToFile($log);
    }
    
    public function logMethod($text)
    {
        $log = "<p style=\"color:#046bbf;\">[".$this->getTime()."] >>> ".$text."</p>";
        if($this->debug_mode) print($log);
        $this->writeToFile($log);
    }
    
    public function logStatus($text)
    {
        $log = "<p style=\"color:#009900;\">[".$this->getTime()."] >>> ".$text."</p>";
        if($this->debug_mode) print($log);
        $this->writeToFile($log);
    }
    
    public function logError($text)
    {
        $log = "<p style=\"color:#ea0000;\">[".$this->getTime()."] >>> ".$text."</p>";
        if($this->debug_mode) print($log);
        $this->writeToFile($log);
    }
    
    private function writeToFile($log)
    {
        $file = file_put_contents("logs/" . date("d-m-y") . ".html", $log, FILE_APPEND | LOCK_EX);
    }
    
    private function getTime()
    {
        return date('H:i:s', time());
    }
}
