<?php


namespace Verse\Statistic\WriteClient\Transport;


class LocalFileTransport implements StatisticWriteClientTransportInterface
{
    protected $statFilesDirectory = '/tmp/stats/';
    
    private $isDirectoryChecked = false;
    
    public function getCurrentFileName () : string
    {
        return $this->statFilesDirectory.'/'.date("Ymd").'.stats.log';
    }
    
    public function send(string $payload)
    {
        !$this->isDirectoryChecked && $this->_checkDir();
        return (bool)file_put_contents($this->getCurrentFileName(), $payload."\n", FILE_APPEND);   
    }
    
    private function _checkDir() {
        if (!file_exists($this->statFilesDirectory) && !mkdir($concurrentDirectory = $this->statFilesDirectory, 0777, true) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
        $this->isDirectoryChecked = true;
    }
    

    /**
     * @return string
     */
    public function getStatFilesDirectory() : string
    {
        return $this->statFilesDirectory;
    }

    /**
     * @param string $statFilesDirectory
     */
    public function setStatFilesDirectory(string $statFilesDirectory)
    {
        $this->statFilesDirectory = $statFilesDirectory;
    }
}