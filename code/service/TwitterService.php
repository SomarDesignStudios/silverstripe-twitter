<?php

use Abraham\TwitterOAuth\TwitterOAuth;
use Abraham\TwitterOAuth\TwitterOAuthException;

class TwitterService
{
    /**
     * @config
     *
     * @var string
     */
    private static $consumer_key;

    /**
     * @config
     *
     * @var string
     */
    private static $consumer_secret;

    /**
     * @config
     *
     * @var string
     */
    private static $oauth_callback;

    /**
     * Create a Twitter API interface.
     *
     * @param string $token
     *
     * @param string $secret
     *
     * @return Abraham\TwitterOAuth\TwitterOAuth
     */
    public function getClient($token = null, $secret = null)
    {
        $consumer_key = Config::inst()->get('TwitterService', 'consumer_key');
        $consumer_secret = Config::inst()->get('TwitterService', 'consumer_secret');

        if (!$consumer_key) {
            user_error(
                'Add a consumer_key to config (TwitterService::consumer_key)',
                E_USER_ERROR
            );
        }

        if (!$consumer_secret) {
            user_error(
                'Add a consumer_secret to config (TwitterService::consumer_secret)',
                E_USER_ERROR
            );
        }

        return new TwitterOAuth($consumer_key, $consumer_secret, $token, $secret);
    }

    /**
     * Gets the OAuth callback URL.
     *
     * @return string
     */
    public function getOAuthCallback()
    {
        $oauth_callback = Config::inst()->get('TwitterService', 'oauth_callback');

        if (!$oauth_callback) {
            user_error(
                'Add a oauth_callback to config (TwitterService::oauth_callback)',
                E_USER_ERROR
            );
        }

        return Director::absoluteBaseURL() . $oauth_callback;
    }

    /**
     * Gets a temporary request token to kick off the OAuth flow.
     *
     * @return array
     */
    public function getOAuthToken()
    {
        $requestToken = [
            'oauth_token' => '',
            'oauth_token_secret' => '',
        ];

        $client = $this->getClient();

        try {
            $response = $client->oauth('oauth/request_token', ['oauth_callback' => $this->getOAuthCallback()]);

            $requestToken['oauth_token'] = $response['oauth_token'];
            $requestToken['oauth_token_secret'] = $response['oauth_token_secret'];
        } catch (TwitterOAuthException $e) {
            user_error($e->getMessage(), E_USER_WARNING);
        }

        return $requestToken;
    }

    /**
     * Gets the login URL for users to authenticate with.
     *
     * @param array $token
     *
     * @return string
     */
    public function getAuthoriseUrl($token)
    {
        $authoriseUrl = '';
        $client = $this->getClient();

        if (array_key_exists('oauth_token', $token)) {
            $authoriseUrl = $client->url('oauth/authorize', ['oauth_token' => $token['oauth_token']]);
        }

        return $authoriseUrl;
    }

    /**
     * Sets the temporary request token in session.
     *
     * @param array $token
     */
    public function setSessionOAuthToken($token)
    {
        $sessionRequestToken = Session::get('TwitterOAuthToken');
        $sessionRequestToken = $sessionRequestToken ? $sessionRequestToken : [];

        $sessionRequestToken['oauth_token'] = $token['oauth_token'];
        $sessionRequestToken['oauth_token_secret'] = $token['oauth_token_secret'];

        Session::set('TwitterOAuthToken', $sessionRequestToken);
    }

    /**
     * Gets the temporary request token from session.
     *
     * @return array
     */
    public function getSessionOAuthToken() {
        return Session::get('TwitterOAuthToken');
    }

    /**
     * Clears the temporary token from session.
     */
    public function clearSessionOAuthToken()
    {
        Session::clear('TwitterOAuthToken');
    }

    /**
     * Gets an access token used for making authenticated requests to the Twitter API.
     *
     * @param string $token
     *
     * @param string $secret
     *
     * @param string $verifier
     *
     * @return array|null
     */
    public function getAccessToken($token, $secret, $verifier)
    {
        $accessToken = null;

        $client = $this->getClient($token, $secret);

        try {
            $accessToken = $client->oauth('oauth/access_token', ['oauth_verifier' => $verifier]);
        } catch (TwitterOAuthException $e) {
            user_error($e->getMessage(), E_USER_WARNING);
        }

        return $accessToken;
    }
}
