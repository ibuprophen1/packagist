<?php

namespace Tapatalk\Connect;

use Tapatalk\TapatalkApp;
use Tapatalk\Utils\Curl;
use Tapatalk\Exceptions\TapatalkSDKException;
use Tapatalk\Utils\Sessions\SessionUtilInterface;
use Tapatalk\Utils\Sessions\TapatalkPHPFileSessionUtil;

class TapatalkConnectLoginServices
{
    /**
     * @const int The length of CSRF string to validate the login link.
     */
    const CSRF_LENGTH = 32;

    /**
     * @const string The base authorization URL.
     */
    private $BASE_AUTHORIZATION_URL = 'https://www.tapatalk.com';

    const REQUEST_ACCESS_TOKEN_URL = 'https://sso.tapatalk.com/tt_connect/access_token';

    private $EXTEND_AUTHORIZATION_URL = '';  // "/connect/login", "/connect/register"

    /**
     * The TapatalkApp instance.
     *
     * @var TapatalkApp
     */
    protected $app;

    public function __construct(
        TapatalkApp $app, 
        // OAuth2Client $oAuth2Client, 
        SessionUtilInterface $sessionUtil = null
        // UrlDetectionInterface $urlHandler = null, 
        // PseudoRandomStringGeneratorInterface $prsg = null
        )
    {
        $this->app = $app;
        // $this->oAuth2Client = $oAuth2Client;
        $this->sessionUtil = $sessionUtil ?: new TapatalkPHPFileSessionUtil();
        // $this->urlDetectionHandler = $urlHandler ?: new FacebookUrlDetectionHandler();
        // $this->pseudoRandomStringGenerator = PseudoRandomStringGeneratorFactory::createPseudoRandomStringGenerator($prsg);
        
        // siteowners-stage.tapatalk.com's tt connect, using stage.tapatalk.com's tt login:
        if ($this->app->getId() == '1489117253') {
            $this->BASE_AUTHORIZATION_URL = 'https://stage.tapatalk.com';
        }
    }

    /**
     * Stores CSRF state and returns a URL to which the user should be sent to in order to continue the login process with Facebook.
     *
     * @param string $redirectUrl The URL Facebook should redirect users to after login.
     * @param array  $scope       List of permissions to request during login.
     * @param array  $params      An array of parameters to generate URL.
     * @param string $separator   The separator to use in http_build_query().
     *
     * @return string
     */
    private function makeUrl($redirectUrl, array $scope, array $params = [], $separator = '&')
    {
        $state = $this->sessionUtil->get('state') ?: $this->generateRandomString(static::CSRF_LENGTH);

        $this->sessionUtil->set('state', $state);

        return $this->getAuthorizationUrl($redirectUrl, $state, $scope, $params, $separator);
    }

    private function generateRandomString($length)
    {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }

    /**
     * Returns the URL to send the user in order to login to Facebook.
     *
     * @param string $redirectUrl The URL Facebook should redirect users to after login.
     * @param array  $scope       List of permissions to request during login.
     * @param string $separator   The separator to use in http_build_query().
     *
     * @return string
     */
    public function getLoginUrl($redirectUrl, array $scope = [], $separator = '&')
    {
        $this->EXTEND_AUTHORIZATION_URL = '/connect/login';

        return $this->makeUrl($redirectUrl, $scope, [], $separator);
    }

    public function getRegisterUrl($redirectUrl, array $scope = [], $separator = '&')
    {
        $this->EXTEND_AUTHORIZATION_URL = '/connect/register';

        return $this->makeUrl($redirectUrl, $scope, [], $separator);
    }

    /**
     * Generates an authorization URL to begin the process of authenticating a user.
     *
     * @param string $redirectUrl The callback URL to redirect to.
     * @param array  $scope       An array of permissions to request.
     * @param string $state       The CSPRNG-generated CSRF value.
     * @param array  $params      An array of parameters to generate URL.
     * @param string $separator   The separator to use in http_build_query().
     *
     * @return string
     */
    public function getAuthorizationUrl($redirectUrl, $state, array $scope = [], array $params = [], $separator = '&')
    {
        $params += [
            'client_id' => $this->app->getId(),
            'state' => $state,
            'response_type' => 'code',
            // 'sdk' => 'php-sdk-' . Facebook::VERSION,
            'redirect_uri' => $redirectUrl,
            'scope' => implode(',', $scope)
        ];
        
        return $this->BASE_AUTHORIZATION_URL . $this->EXTEND_AUTHORIZATION_URL. '?' . http_build_query($params, null, $separator);
    } 

    /**
     * Takes a valid code from a login redirect, and returns an AccessToken entity.
     *
     * @param string|null $redirectUrl The redirect URL.
     *
     * @return AccessToken|null
     *
     * @throws TapatalkSDKException
     */
    public function getAccessToken($redirectUrl = null)
    {
        if (!$code = $this->getCode()) {
            return null;
        }

        $this->validateCsrf();
        $this->resetCsrf();

        // $redirectUrl = $redirectUrl ?: $this->urlDetectionHandler->getCurrentUrl();
        // // At minimum we need to remove the state param
        // $redirectUrl = FacebookUrlManipulator::removeParamsFromUrl($redirectUrl, ['state']);

        // return $this->getAccessTokenFromCode($code, $redirectUrl);
        return $this->getAccessTokenFromCode($code);
    }       

    /**
     * Validate the request against a cross-site request forgery.
     *
     * @throws TapatalkSDKException
     */
    protected function validateCsrf()
    {
        $state = $this->getState();

        if (!$state) {
            throw new TapatalkSDKException('Cross-site request forgery validation failed. Required GET param "state" missing.');
        }

        $savedState = $this->sessionUtil->get('state');

        if (!$savedState) {
            throw new TapatalkSDKException('Cross-site request forgery validation failed. Required param "state" missing from persistent data.');
        }

        if (\hash_equals($savedState, $state)) {
            return;
        }

        throw new TapatalkSDKException('Cross-site request forgery validation failed. The "state" param from the URL and session do not match.');
    }

    /**
     * Resets the CSRF so that it doesn't get reused.
     */
    private function resetCsrf()
    {
        $this->sessionUtil->set('state', null);
    }

    /**
     * Return the code.
     *
     * @return string|null
     */
    protected function getCode()
    {
        return $this->getInput('code');
    }

    /**
     * Return the state.
     *
     * @return string|null
     */
    protected function getState()
    {
        return $this->getInput('state');
    }

    /**
     * Returns a value from a GET param.
     *
     * @param string $key
     *
     * @return string|null
     */
    private function getInput($key)
    {
        return isset($_GET[$key]) ? $_GET[$key] : null;
    }    

    public function getAccessTokenFromCode($code)
    {
        $tapacurl = new Curl(self::REQUEST_ACCESS_TOKEN_URL); 

        $request = json_encode([
            'code'          => $code,
            'client_id'     => $this->app->getId(),
            'client_secret' => $this->app->getSecret(),
            ], JSON_UNESCAPED_SLASHES);

        // $request = str_replace('\\/', '/', $request);   // Apple won't accpet converting / to \\/ by json_encode
    
        $tapacurl->setPost($request);

        $tapacurl->createCurl();

        $api_response_json = $tapacurl->getResponse();

        $api_response = json_decode($api_response_json, true);
        
        return isset($api_response['data']['access_token']) ? $api_response['data']['access_token'] : '';
    }
}
