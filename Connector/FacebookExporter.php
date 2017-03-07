<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\SocialBundle\Connector;

use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Common\Channel\ChannelInterface;
use Integrated\Common\Channel\Exporter\ExporterInterface;
use AppBundle\Facebook\Oauth;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class FacebookExporter implements ExporterInterface
{
    private $options;

    private $facebook;

    public function __construct($options, Oauth $facebook)
    {
        $this->options = $options;
        $this->facebook = $facebook;
    }

    /**
     * {@inheritdoc}
     */
    public function export($content, $state, ChannelInterface $channel)
    {
        if($content instanceof Article)
        {
            $message = $content->getTitle();
            $link = "http://". $channel->getPrimaryDomain() ."/content/article/" . $content->getSlug() . ".html";

            return $this->facebook->post($this->options["user_id"], $this->options["access_token"], $link, $message);
        }
    }
}
