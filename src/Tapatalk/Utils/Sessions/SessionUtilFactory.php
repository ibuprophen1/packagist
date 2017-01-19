<?php

namespace Tapatalk\Utils\Sessions;

use InvalidArgumentException;

class SessionUtilFactory
{
    private function __construct()
    {
        // a factory constructor should never be invoked
    }

    /**
     * SessionUtil generation.
     *
     * @param PersistentDataInterface|string|null $handler
     *
     * @throws InvalidArgumentException If the persistent data handler isn't "session", "memory", or an instance of Facebook\PersistentData\PersistentDataInterface.
     *
     * @return SessionUtilInterface
     */
    public static function createSessionUtil($handler)
    {
        if ('php_file_session' === $handler) {
            return new TapatalkPHPFileSessionUtil();
        }
        // if ('memory' === $handler) {
        //     return new TapatalkMemorySessionUtil();
        // }

        throw new InvalidArgumentException('The persistent data handler must be set to "php_file_session", "memory", or be an instance of Facebook\PersistentData\PersistentDataInterface');
    }
}
