<?php

namespace Tapatalk\Utils\Sessions;

/**
 * Interface Sessions
 *
 * @package Tapatalk
 */
interface SessionUtilInterface
{
    /**
     * Get a value from a persistent data store.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * Set a value in the persistent data store.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value);
}
