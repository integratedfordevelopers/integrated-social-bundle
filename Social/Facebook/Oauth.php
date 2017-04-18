<?php
namespace Integrated\Bundle\SocialBundle\Social\Facebook;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
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
        $fb = new Facebook([
            'app_id' => $this->container->getParameter("app_id"),
            'app_secret' => $this->container->getParameter("app_id_secret"),
            'default_graph_version' => 'v2.2'
        ]);

        $helper = $fb->getRedirectLoginHelper();

        $permissions = ['publish_actions']; // Optional permissions
        $loginUrl = $helper->getLoginUrl("http://" . $_SERVER["SERVER_NAME"] . ":8080/" . $admin_url . '/connector/config/'.$connector, $permissions);

        return $loginUrl;
    }

    public function callback()
    {
        $fb = new Facebook([
            'app_id' => $this->container->getParameter("app_id"),
            'app_secret' => $this->container->getParameter("app_id_secret"),
            'default_graph_version' => 'v2.2',
        ]);

        $helper = $fb->getRedirectLoginHelper();

        try {
            $accessToken = $helper->getAccessToken();
        } catch(FacebookResponseException $e) {
            // When Graph returns an error
            throw new \Exception('Graph returned an error: ' . $e->getMessage());
        } catch(FacebookSDKException $e) {
            // When validation fails or other local issues
            throw new \Exception('Facebook SDK returned an error: ' . $e->getMessage());
        }

        if (! isset($accessToken)) {
            if ($helper->getError()) {
                header('HTTP/1.0 401 Unauthorized');
                $error = "Error: " . $helper->getError() . "\n";
                $error .= "Error Code: " . $helper->getErrorCode() . "\n";
                $error .= "Error Reason: " . $helper->getErrorReason() . "\n";
                $error .= "Error Description: " . $helper->getErrorDescription() . "\n";
            } else {
                header('HTTP/1.0 400 Bad Request');
                $error = 'Bad request';
            }
            throw new \Exception($error);
        }

        // The OAuth 2.0 client handler helps us manage access tokens
        $oAuth2Client = $fb->getOAuth2Client();

        // Get the access token metadata from /debug_token
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);

        // Validation (these will throw FacebookSDKException's when they fail)
        $tokenMetadata->validateExpiration();

        if (! $accessToken->isLongLived()) {
            // Exchanges a short-lived access token for a long-lived one
            try {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (FacebookSDKException $e) {
                throw new \Exception("<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n");
            }
        }

        try {
            // Returns a `Facebook\FacebookResponse` object
            $response = $fb->get('/me', $accessToken);
        } catch(FacebookResponseException $e) {
            throw new \Exception('Graph returned an error: ' . $e->getMessage());
        } catch(FacebookSDKException $e) {
            throw new \Exception('Facebook SDK returned an error: ' . $e->getMessage());
        }

        $userdata = [];

        $userdata["user_id"] = $response->getDecodedBody()["id"];
        $userdata["access_token"] = $accessToken->getValue();

        return $userdata;

        // User is logged in with a long-lived access token.
        // You can redirect them to a members-only page.
    }

    public function post($userid, $access_token, $link, $message)
    {
        $fb = new Facebook([
            'app_id' => $this->container->getParameter("app_id"),
            'app_secret' => $this->container->getParameter("app_id_secret"),
            'default_graph_version' => 'v2.2',
        ]);

        $postData = [
            'link' => $link,
            'message' => $message
        ];

        try {
            // Returns a `Facebook\FacebookResponse` object
            $response = $fb->post('/'. $userid .'/feed', $postData, $access_token);
        } catch(FacebookResponseException $e) {
            throw new \Exception('Graph returned an error: ' . $e->getMessage());
        } catch(FacebookSDKException $e) {
            throw new \Exception('Facebook SDK returned an error: ' . $e->getMessage());
        }

        $graphNode = $response->getGraphNode();

        return $graphNode;
    }
}