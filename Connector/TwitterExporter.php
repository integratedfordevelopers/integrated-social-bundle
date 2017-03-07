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
use AppBundle\Twitter\Oauth;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class TwitterExporter implements ExporterInterface
{
    private $options;

    private $twitter;

    public function __construct($option, Oauth $twitter)
    {
        $this->options = $option;
        $this->twitter = $twitter;
    }

    /**
     * {@inheritdoc}
     */
    public function export($content, $state, ChannelInterface $channel)
    {
        if($content instanceof Article)
        {
            $tweet = $content->getTitle() . " http://". $channel->getPrimaryDomain() ."/content/article/" . $content->getSlug() . ".html";

            return $this->twitter->tweet($this->options["token"], $this->options["token_secret"], $tweet);
        }
    }
}
