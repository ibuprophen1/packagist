<?php

namespace Tapatalk;

use Tapatalk\Exceptions\TapatalkSDKException;
use Tapatalk\Connect\TapatalkConnectLoginServices;
use Tapatalk\Utils\Sessions\SessionUtilFactory;

/**
 * Class Tapatalk
 *
 * @package Tapatalk
 */
class Tapatalk
{
    /**
     * @const string The name of the environment variable that contains the app ID.
     */
    const APP_ID_ENV_NAME = 'TAPATALK_APP_ID';

    /**
     * @const string The name of the environment variable that contains the app secret.
     */
    const APP_SECRET_ENV_NAME = 'TAPATALK_APP_SECRET';

    /**
     * The TapatalkApp instance
     *
     * @var  TapatalkApp
     */
    protected $app;

    /**
     * @var PersistentDataInterface|null The persistent data handler.
     */
    protected $sessionUtil;

    /**
     * Instantiates a new Tapatalk super-class object.
     *
     * @param array $config
     *
     * @throws TapatalkSDKException
     */
    public function __construct(array $config = [])
    {
        $config = array_merge([
            'app_id' => getenv(self::APP_ID_ENV_NAME),
            'app_secret' => getenv(self::APP_SECRET_ENV_NAME),
            // 'default_graph_version' => static::DEFAULT_GRAPH_VERSION,
            // 'enable_beta_mode' => false,
            // 'http_client_handler' => null,
            'session_type' => 'php_file_session',
            // 'pseudo_random_string_generator' => null,
            // 'url_detection_handler' => null,
        ], $config);

        if (!$config['app_id']) {
            throw new TapatalkSDKException('Required "app_id" key not supplied in config and could not find fallback environment variable "' . self::APP_ID_ENV_NAME . '"');
        }
        if (!$config['app_secret']) {
            throw new TapatalkSDKException('Required "app_secret" key not supplied in config and could not find fallback environment variable "' . self::APP_SECRET_ENV_NAME . '"');
        }

        $this->app = new TapatalkApp($config['app_id'], $config['app_secret']);

        // $this->client = new FacebookClient(
        //     HttpClientsFactory::createHttpClient($config['http_client_handler']),
        //     $config['enable_beta_mode']
        // );
        // $this->pseudoRandomStringGenerator = PseudoRandomStringGeneratorFactory::createPseudoRandomStringGenerator(
        //     $config['pseudo_random_string_generator']
        // );
        // $this->setUrlDetectionHandler($config['url_detection_handler'] ?: new FacebookUrlDetectionHandler());

        $this->sessionUtil = SessionUtilFactory::createSessionUtil(
            $config['session_type']
        );

        // if (isset($config['default_access_token'])) {
        //     $this->setDefaultAccessToken($config['default_access_token']);
        // }

        // @todo v6: Throw an InvalidArgumentException if "default_graph_version" is not set
        // $this->defaultGraphVersion = $config['default_graph_version'];
    }

    /**
     * Returns the FacebookApp entity.
     *
     * @return TapatalkApp
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * Returns the redirect login helper.
     *
     * @return TapatalkConnectLoginServices
     */
    public function getConnectService()
    {
        return new TapatalkConnectLoginServices(
            $this->app,
            // $this->getOAuth2Client(),
            $this->sessionUtil
            // $this->urlDetectionHandler,
            // $this->pseudoRandomStringGenerator
        );
    }        
}