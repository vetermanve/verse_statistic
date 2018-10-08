<?php


namespace Verse\Statistic\Aggregate\EventStream;


use Verse\Statistic\Aggregate\EventStream\Event;
use Verse\Statistic\Aggregate\EventStream\EventStreamInterface;

class FilesDirectoryEventStream implements EventStreamInterface
{
    const STREAM_CONFIG_DIR  = 'stats_stream';
    const STREAM_CONFIG_FILE = 'stream.lock';
    
    const CONFIG_TAG = 'tag';
    
    const TAG_SEPARATOR = ':';
    
    protected $statFilesDirectory = '/tmp/stats/';
    
    protected $_readBufferSize = 20;

    /**
     * @var Event\EventStreamItem
     */
    private $_eventsPool = [];
    
    private $_dirFiles = [];
    
    private $_currentStreamPosition = 0 ;
    
    private $_currentFileName = '';
    private $_currentFilePath = '';
    private $_currentFileLine = 0;
    private $_currentFileHandler;
    
    private $_isCurrentFileReadingAvailable = false;
    private $_restoredFileStartLine = 0;
    
    private $_streamPositionTag;
    
    protected $_streamPositionLoaded = false;
    

    /**
     * @return Event\EventStreamItem
     */
    public function get()
    {
        !$this->_streamPositionLoaded && $this->_restoreStreamPosition(); 
        
        if (!isset($this->_eventsPool[$this->_currentStreamPosition])) {
            $this->_loadForward();
        }
        
        if ($event = $this->_eventsPool[$this->_currentStreamPosition] ?? null) {
            unset($this->_eventsPool[$this->_currentStreamPosition]);
            $this->_streamPositionTag = $event->tag;
            $this->_currentStreamPosition++;
        };
        
        return $event;
    }
    
    private function _restoreStreamPosition() {
        $file = $this->_getSettingsFileName();
        
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), 1);
            $tag = $data[self::CONFIG_TAG] ?? '';
            if ($tag) {
                $this->_streamPositionTag = $tag;
                list ($file, $line) = $this->_unpackTag($tag);

                $this->_selectCurrentFile($file);

                if ($this->_isCurrentFileReadingAvailable) {
                    $this->_restoredFileStartLine = $line;
                }    
            }
        }
        
        $this->_streamPositionLoaded = true;
    }
    
    private function _getSettingsFileName() {
        $streamDataDir = $this->statFilesDirectory.DIRECTORY_SEPARATOR.self::STREAM_CONFIG_DIR;
        $this->_checkDir($streamDataDir);
        return $this->statFilesDirectory.DIRECTORY_SEPARATOR.self::STREAM_CONFIG_DIR.DIRECTORY_SEPARATOR.self::STREAM_CONFIG_FILE;
    }
    
    public function acknowledgePosition()
    {
        $file = $this->_getSettingsFileName();
        
        $data = json_encode([
            self::CONFIG_TAG => $this->_streamPositionTag,
        ]);
        
        file_put_contents($file, $data);
    }
    
    public function forgetStreamPosition () 
    {
        $file = $this->_getSettingsFileName();
        file_exists($file) && unlink($file);
    }
    
    private function _selectCurrentFile($findFileWithName = null) {
        !$this->isDirectoryChecked && $this->_checkDir($this->statFilesDirectory) && $this->isDirectoryChecked = true;

        if (empty($this->_dirFiles)) {
            $this->_dirFiles = scandir($this->statFilesDirectory, SORT_NATURAL);    
        }
        
        while ($fileName = \current($this->_dirFiles)) {
            \next($this->_dirFiles);
            
            if ($findFileWithName && $fileName !== $findFileWithName) {
                continue;
            }
            
            $filePath = $this->statFilesDirectory.DIRECTORY_SEPARATOR.$fileName;
            if (!is_dir($filePath)) {
                $this->_currentFileName = $fileName;
                $this->_currentFilePath = $filePath;
                $this->_isCurrentFileReadingAvailable = true;
                break;
            }
        }
        
        if ($findFileWithName && !$this->_isCurrentFileReadingAvailable) {
            \reset($this->_dirFiles);
        }
    }
    
    private function _loadForward()
    {
        if (!$this->_isCurrentFileReadingAvailable) {
            $this->_selectCurrentFile();
        }
        
        if ($this->_isCurrentFileReadingAvailable) {
            $this->_loadFileEvents();
        }
    }
    
    private function _loadFileEvents() {
        $fileName = $this->_currentFileName;
        
        if (!$this->_currentFileHandler) {
            $this->_currentFileHandler = \fopen($this->_currentFilePath, 'rb');;
            $this->_currentFileLine = 0;
        }
        
        $addedEventsCount = 0;
        while ($readResult = fgets($this->_currentFileHandler)) {
            $this->_currentFileLine++;
            
            if ($this->_currentFileLine <= $this->_restoredFileStartLine) {
                continue;
            }
            
            $streamPosition = $this->_currentStreamPosition + $addedEventsCount;
            
            $event = new Event\EventStreamItem();
            $event->tag  = $this->_packTag($fileName, $this->_currentFileLine);
            $event->body = trim($readResult);
            
            $this->_eventsPool[$streamPosition] = $event;
            
            if ($addedEventsCount++ > $this->_readBufferSize) {
                break;
            }
        }
        // End of file 
        if ($readResult === false) {
            fclose($this->_currentFileHandler);
            $this->_currentFileHandler = null;
            $this->_currentFileLine = 0;
            $this->_isCurrentFileReadingAvailable = false;
            $this->_restoredFileStartLine = 0;
        }
    }

    private $isDirectoryChecked = false;


    private function _checkDir($concurrentDirectory) {
        if (!file_exists($concurrentDirectory) && !mkdir($concurrentDirectory, 0777, true) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
        
        return true;
    }

    private function _packTag($file, $line) : string {
        return $file.self::TAG_SEPARATOR.$line;
    }
    
    private function _unpackTag($tag) : array {
        $pos = strrpos($tag, self::TAG_SEPARATOR);
        $file = substr($tag, 0, $pos);
        $line = (int)substr($tag, $pos + 1);
        return [$file, $line];        
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