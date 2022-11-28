<?php
/**
 * Classes to create, write and read the project logs
 */
class ProjectLog {
    protected string $dir;
    protected string $filePath;
    protected $file;

    function __construct(int $projectID=null) {
        $this->setDir();
        if ($projectID) {
            $this->setFile($projectID);
        }
    }

    function __destruct() {
        fclose($this->file);
    }

    private function setDir() {
        $this->dir = "../project_logs/";
    }
    
    public function setFile(int $projectID) {
        $this->filePath = $this->dir . "project_log_" . $projectID . ".log";
        $this->file = fopen($this->filePath, "a");
    }
}

class WriteProjectLog extends ProjectLog {
    private string $userEmail;

    function __construct(string $userEmail, int $projectID=null) {
        parent::__construct($projectID);
        $this->userEmail = $userEmail;
    }

    public function writeToLog(string $logMessage) {
        $prefix = "[" . date("d.m.y, H:i:s") . ", " . $this->userEmail . "] "; // writes local server time
        $entry = $prefix . $logMessage . "\n";
        fwrite($this->file, $entry);
    }

    public function deleteLogFile() {
        fclose($this->file);
        unlink($this->filePath);
    }
}

class ReadProjectLog extends ProjectLog {

    function __construct(int $projectID) {
        parent::__construct($projectID);
    }

    public function getFileContent() {
        return nl2br(file_get_contents($this->filePath));
    }

    public function getFilePath() {
        return $this->filePath;
    }
}
?>