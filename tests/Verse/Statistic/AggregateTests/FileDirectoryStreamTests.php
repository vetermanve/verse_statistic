<?php

namespace Verse\Statistic\AggregateTests;

use Verse\Statistic\Aggregate\EventsContainer;
use Verse\Statistic\Aggregate\EventStream\Decoder\JsonDecoder;
use Verse\Statistic\Aggregate\EventStream\Event\EventStreamItem;
use Verse\Statistic\Aggregate\EventStream\FilesDirectoryEventStream;
use Verse\Statistic\Aggregate\Reader\EventStreamReader;

class FileDirectoryStreamTests extends \PHPUnit\Framework\TestCase
{
    public function testStreamReading () 
    {
        $dir = __DIR__.'/test-stats';
        
        $stream = new FilesDirectoryEventStream();
        $stream->setStatFilesDirectory($dir);
        $stream->forgetStreamPosition();
        
        $item = $stream->get();
        
        $this->assertNotEmpty($item);
        $this->assertInstanceOf(EventStreamItem::class, $item);
    }

    public function testStreamReadingTwoFiles ()
    {
        $dir = __DIR__.'/test-stats';

        $stream = new FilesDirectoryEventStream();
        $stream->setStatFilesDirectory($dir);
        $stream->forgetStreamPosition();
        
        $items = [];
        while ($item = $stream->get()) {
            $items[] = $item;
            $this->assertNotEmpty($item);
            $this->assertInstanceOf(EventStreamItem::class, $item);
        }

        $this->assertCount(6, $items);
    }

    public function testReaderReadingStream ()
    {
        $dir = __DIR__.'/test-stats';

        $stream = new FilesDirectoryEventStream();
        $stream->forgetStreamPosition();
        $stream->setStatFilesDirectory($dir);
        
        $reader = new EventStreamReader();
        $reader->setEventStream($stream);

        $reader->prepareCycle();
        $reader->readChunk();
        
        // check all read 
        $this->assertCount(6, $reader->getEventsContainer()->events);
        
        $reader->acknowledgeEvents();
        $reader->prepareCycle();

        $reader->readChunk();
        
        // check all files listed and read not repeated
        $this->assertCount(0, $reader->getEventsContainer()->events);
    }

    public function testReaderReadingStreamByChunks ()
    {
        $dir = __DIR__.'/test-stats';

        $stream = new FilesDirectoryEventStream();
        $stream->setStatFilesDirectory($dir);
        $stream->forgetStreamPosition();

        $reader = new EventStreamReader();
        $reader->setDecoder(new JsonDecoder());
        $reader->setEventStream($stream);
        $reader->setChunkSize(4);

        ///////////
        //// first cycle
        $reader->prepareCycle();
        $reader->readChunk();

        // check all read 
        $this->assertCount(4, $reader->getEventsContainer()->events);
        $reader->acknowledgeEvents();

        ///////////
        /// second cycle
        $reader->prepareCycle();
        $reader->readChunk();

        // check all files listed 
        $this->assertCount(2, $reader->getEventsContainer()->events);
        $reader->acknowledgeEvents();

        ///////////
        //// third cycle
        $reader->prepareCycle();
        $reader->readChunk();

        // check all files listed and read not repeated
        $this->assertCount(0, $reader->getEventsContainer()->events);
        $reader->acknowledgeEvents();
    }

    public function testReaderReadingStreamWithStreamRecreating ()
    {
        $dir = __DIR__.'/test-stats';

        $stream = new FilesDirectoryEventStream();
        $stream->setStatFilesDirectory($dir);
        $stream->forgetStreamPosition();

        $reader = new EventStreamReader();
        $reader->setDecoder(new JsonDecoder());
        $reader->setEventStream($stream);
        $reader->setChunkSize(4);

        ///////////
        //// first cycle
        $reader->prepareCycle();
        $reader->readChunk();

        // check all read 
        $this->assertCount(4, $reader->getEventsContainer()->events);
        $reader->acknowledgeEvents();
        
        /// We dropping existing $stream dirver
        unset($stream);
        $stream = new FilesDirectoryEventStream();
        $stream->setStatFilesDirectory($dir);
        $reader->setEventStream($stream);

        ///////////
        /// second cycle
        $reader->prepareCycle();
        $reader->readChunk();

        // check all files listed 
        $this->assertCount(2, $reader->getEventsContainer()->events);
        $reader->acknowledgeEvents();

        /// We dropping existing $stream dirver
        unset($stream);
        $stream = new FilesDirectoryEventStream();
        $stream->setStatFilesDirectory($dir);
        $reader->setEventStream($stream);

        ///////////
        //// third cycle
        $reader->prepareCycle();
        $reader->readChunk();

        // check all files listed and read not repeated
        $this->assertCount(0, $reader->getEventsContainer()->events);
        $reader->acknowledgeEvents();
    }
}