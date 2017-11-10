<?php
/**
 * Simple logger 
 * 
 */

namespace De\Uniwue\RZ\Lyra\URL;

class Logger{

    /**
     * Constructor
     * 
     * @param string $name The name of the logger used
     */
    public function __construct($name){
        $this->name = $name;
    }

    /**
     * Logs the give message.
     * 
     */
    public function log($level, $message, $context){
        echo "level: $level"." message: ".$message. " context ". implode(",", $context) . "\n";
    }
}