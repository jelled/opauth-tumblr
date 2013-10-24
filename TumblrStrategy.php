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

        if (!isset($_SESSION)) {
            session_start();
        }

        $token = (empty($_SESSION['_opauth_tumblr']['oauth_token'])) ? NULL : $_SESSION['_opauth_tumblr']['oauth_token'];
        $token_secret = (empty($_SESSION['_opauth_tumblr']['oauth_token_secret']))? NULL : $_SESSION['_opauth_tumblr']['oauth_token_secret'];

        //instantiate tumblr object
        $this->tum_oauth = new TumblrOAuth\TumblrOAuth($this->strategy['consumer_key'], $this->strategy['consumer_secret'], $token, $token_secret);
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


        //TODO: verify response
        $_SESSION['_opauth_tumblr'] = $request_token;


        //get auth url
        //$url = $this->tum_oauth->getAuthorizeURL($request_token['request_token']);
        $url = 'http://www.tumblr.com/oauth/authorize?oauth_token=' . $request_token['oauth_token'];

        //load url
        $this->redirect($url);
    }

    /**
     * Internal callback, after Tumblr's OAuth
     */
    public function oauth_callback()
    {

        $access_token = $this->tum_oauth->getAccessToken($_REQUEST['oauth_verifier']);

        $response = $this->tum_oauth->get('http://api.tumblr.com/v2/user/info');


        sleep(1);

        if(!empty($response->meta) && $response->meta->status == 200){

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

    }

}