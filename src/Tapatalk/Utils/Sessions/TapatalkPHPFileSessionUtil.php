<?php

namespace Tapatalk\Utils\Sessions;

use Tapatalk\Exceptions\TapatalkSDKException;

/**
 * Class TapatalkPHPFileSessionUtil
 *
 * @package Tapatalk
 */
class TapatalkPHPFileSessionUtil implements SessionUtilInterface
{
    /**
     * @var string Prefix to use for session variables.
     */
    protected $sessionPrefix = 'TTPFSU_';

    /**
     * Init the session handler.
     *
     * @param boolean $enableSessionCheck
     *
     * @throws FacebookSDKException
     */
    public function __construct($enableSessionCheck = true)
    {
        if ($enableSessionCheck && session_status() !== PHP_SESSION_ACTIVE) {
            throw new TapatalkSDKException(
                'Sessions are not active. Please make sure session_start() is at the top of your script.',
                720
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        if (isset($_SESSION[$this->sessionPrefix . $key])) {
            return $_SESSION[$this->sessionPrefix . $key];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        $_SESSION[$this->sessionPrefix . $key] = $value;
    }
}
