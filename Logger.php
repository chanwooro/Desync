<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Logger
{
    public function log($message, $level = null, $filepath = null)
    {
        $logChannel = new MonologLogger('dsync');
        if (!$level) {
            $level = MonologLogger::DEBUG;
        }
        if (is_array($message)) {
            $message = print_r($message, true);
        }
        $streamHandler = new StreamHandler($filepath, $level);
        $streamHandler->setFormatter(new LineFormatter(null, null, true));

        $logChannel->pushHandler($streamHandler);
        $logChannel->log($level, $message);
    }
}
