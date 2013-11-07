<?php
/**
 * Tumblr strategy for Opauth
 * based on http://www.tumblr.com/docs/en/api/v2#auth
 * Author: Benjamin Bjurstrom
 *
 * tumblroauth library from: https://groups.google.com/forum/#!msg/tumblr-api/g6SeIBWvsnE/gnWqT9jFSlEJ
 *
 * More information on Opauth: http://opauth.org
 *
 * @copyright    Copyright © 2012 U-Zyn Chua (http://uzyn.com)
 * @link         http://opauth.org
 * @package      Opauth.TumblrStrategy
 * @license      MIT License
 */


class TumblrStrategy extends OpauthStrategy
{

    public function __construct($strategy, $env)
    {
        parent::__construct($strategy, $env);
        require dirname(__FILE__) . '/tumblroauth/tumblroauth.php';

        if (!isset($_SESSION)) session_start();

        //instantiate tumblr object and check whether we already have an oauth_token
        if(empty($_SESSION['_opauth_tumblr']['oauth_token'])){
            //initial run goes here
            $this->tum_oauth = new TumblrOAuth\TumblrOAuth($this->strategy['consumer_key'], $this->strategy['consumer_secret']);
        }else{
            //On the callback we have an oauth tokens so include them when instantiating tum_oauth object.
            $this->tum_oauth = new TumblrOAuth\TumblrOAuth($this->strategy['consumer_key'], $this->strategy['consumer_secret'], $_SESSION['_opauth_tumblr']['oauth_token'], $_SESSION['_opauth_tumblr']['oauth_token_secret']);

            //clear _opauth_tumblr session data now that we've hit the callback
            unset($_SESSION['_opauth_tumblr']);

        }

    }


    /**
     * Compulsory config keys, listed as unassociative arrays
     * eg. array('app_id', 'app_secret');
     */
    public $expects = array('consumer_key', 'consumer_secret');

    /**
     * Optional config keys with respective default values, listed as associative arrays
     * eg. array('scope' => 'email');
     */
    public $defaults = array(
        'redirect_uri' => '{complete_url_to_strategy}oauth_callback'
    );

    /**
     * Auth request
     */
    public function request()
    {
        $callback_url = NULL;

        //generate request token
        $request_token = $this->tum_oauth->getRequestToken($callback_url);
        if(!empty($request_token['oauth_token'])){
            $_SESSION['_opauth_tumblr'] = $request_token;

            $url = 'http://www.tumblr.com/oauth/authorize?oauth_token=' . $request_token['oauth_token'];

            //load url
            $this->redirect($url);
        }else{
            $error = array(
                'provider' => 'Tumblr',
                'code' => 'oauth_token_error',
                'message' => 'Failed when attempting to obtain oauth token',
                'raw' => array(
                    'headers' => $this->tum_oauth->http_header,
                    'response' => $request_token,
                    'http_info' => $this->tum_oauth->http_info,
                )
            );

            $this->errorCallback($error);
        }

    }

    /**
     * Internal callback, after Tumblr's OAuth
     */
    public function oauth_callback()
    {
        $access_token = $this->get_access_token($_REQUEST['oauth_verifier']);

        $response = $this->get_user_info();

        $user = $response->response->user;

        $this->auth = array(
            'provider' => 'Tumblr',
            'uid' => $user->name,
            'info' => array(
                'name' => $user->name,
                'likes' => $user->likes,
                'following' => $user->following,
                'blogs' => $user->blogs,
                // etc...
            ),
            'credentials' => array(
                'token' => $access_token['oauth_token'],
                'secret' => $access_token['oauth_token_secret']
            ),
            'raw' => $response
        );

        $this->callback();
    }

    private function get_access_token($oauth_verifier){
        $access_token = $this->tum_oauth->getAccessToken($oauth_verifier);

        if(!empty($access_token['oauth_token'])){
            return $access_token;
        } else {
            sleep(1);
            $error = array(
                'provider' => 'Tumblr',
                'code' => 'oauth_token_error',
                'message' => 'Failed when attempting to obtain oauth token',
                'raw' => array(
                    'headers' => $this->tum_oauth->http_header,
                    'response' => $access_token,
                    'http_info' => $this->tum_oauth->http_header,
                )
            );

            $this->errorCallback($error);
        }
    }

    private function get_user_info()
    {
        $response = $this->tum_oauth->get('http://api.tumblr.com/v2/user/info');

        if (!empty($response->meta) && $response->meta->status == 200) {
            return $response;
        } else {
            $error = array(
                'provider' => 'Tumblr',
                'code' => 'oauth_token_error',
                'message' => 'Failed when attempting to obtain oauth token',
                'raw' => array(
                    'headers' => $this->tum_oauth->http_header,
                    'response' => $request_token,
                    'http_info' => $this->tum_oauth->http_info,
                )
            );

            $this->errorCallback($error);
        }
    }

}