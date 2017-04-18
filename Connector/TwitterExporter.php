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
use Integrated\Common\Channel\Connector\Config\OptionsInterface;
use Integrated\Common\Channel\Exporter\ExporterInterface;
use Integrated\Bundle\SocialBundle\Social\Twitter\Oauth;

class TwitterExporter implements ExporterInterface
{
    /**
     * @var OptionsInterface
     */
    private $options;

    /**
     * @var Oauth
     */
    private $twitter;

    public function __construct(OptionsInterface $option, Oauth $twitter)
    {
        $this->options = $option;
        $this->twitter = $twitter;
    }

    /**
     * {@inheritdoc}
     */
    public function export($content, $state, ChannelInterface $channel)
    {
        if ($content instanceof Article)
        {
            //TODO remove hardcoded URL when INTEGRATED-572 is fixed
            $tweet = $content->getTitle() . " http://". $channel->getPrimaryDomain() ."/content/article/" . $content->getSlug();

            $this->twitter->tweet($this->options["token"], $this->options["token_secret"], $tweet);
        }
    }
}
