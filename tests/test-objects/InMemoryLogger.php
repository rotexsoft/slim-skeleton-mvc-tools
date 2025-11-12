<?php
declare(strict_types=1);

namespace SMVCTools\Tests\TestObjects;

/**
 * Description of InMemoryLogger
 *
 * @author rotimi
 */
class InMemoryLogger extends \Psr\Log\AbstractLogger {

    /**
     * @var string[]
     */
    protected array $log_entries = [];


    /**
     * Logs with an arbitrary level.
     *
     * @param mixed   $level
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, string|\Stringable $message, array $context=[]) {
        
        $this->log_entries[] = "[LEVEL: {$level}] [MESSAGE: {$message}]";
    }
    
    public function reset():void {
        
        $this->log_entries = [];
    }
    
    public function getLogEntries(): array {
        
        return $this->log_entries;
    }
}
