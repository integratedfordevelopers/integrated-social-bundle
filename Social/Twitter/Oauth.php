<?php
namespace Integrated\Bundle\SocialBundle\Social\Twitter;

use Abraham\TwitterOAuth\TwitterOAuth;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Oauth
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function login($connector, $admin_url)
    {
        $twitteroauth = new TwitterOAuth($this->container->getParameter("consumer_key"), $this->container->getParameter("consumer_key_secret"));

        // request token of application
        $request_token = $twitteroauth->oauth('oauth/request_token', ['oauth_callback' => "http://" . $_SERVER["SERVER_NAME"] . ":8080/" . $admin_url . '/connector/config/'.$connector]);

        // throw exception if something gone wrong
        if($twitteroauth->getLastHttpCode() != 200) {
            throw new \Exception('There was a problem performing this request');
        }

        // save token of application to session
        $_SESSION['oauth_token'] = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

        // generate the URL to make request to authorize our application
        $url = $twitteroauth->url('oauth/authenticate', ['oauth_token' => $request_token['oauth_token'], 'force_login' => 'true']);

        return $url;
    }

    public function callback()
    {
        $oauth_verifier = filter_input(INPUT_GET, 'oauth_verifier');

        if (empty($oauth_verifier) || empty($_SESSION['oauth_token']) || empty($_SESSION['oauth_token_secret']))
        {
            // something's missing, go and login again
            return false;
        }

        // request user token
        $connection = new TwitterOAuth($this->container->getParameter("consumer_key"), $this->container->getParameter("consumer_key_secret"), $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

        // request user token
        $token = $connection->oauth('oauth/access_token', ['oauth_verifier' => $oauth_verifier]);

        return $token;
    }

    public function tweet($token, $token_secret, $content)
    {
        $twitter = new TwitterOAuth($this->container->getParameter("consumer_key"), $this->container->getParameter("consumer_key_secret"), $token, $token_secret);

        $status = $twitter->post("statuses/update", ["status" => $content]);

        return $status;
    }
}