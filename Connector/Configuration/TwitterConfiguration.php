<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\SocialBundle\Connector\Configuration;

use Integrated\Bundle\SocialBundle\Connector\Exporter\TwitterExporter;
use Integrated\Common\Channel\Connector\AdapterInterface;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;
use Integrated\Common\Channel\Connector\ConfigurableInterface;
use Integrated\Common\Channel\Connector\ConfigurationInterface;
use Integrated\Common\Channel\Connector\Adapter\ManifestInterface;

use Integrated\Common\Channel\Exporter\ExportableInterface;
use Integrated\Bundle\SocialBundle\Social\Twitter\Oauth;
use Integrated\Bundle\SocialBundle\Form\Type\TwitterType;

class TwitterConfiguration implements AdapterInterface, ConfigurableInterface, ExportableInterface, ConfigurationInterface, ManifestInterface
{
    /**
     * @var ManifestInterface
     */
    private $manifest = null;

    /**
     * @var ConfigurationInterface
     */
    private $configuration = null;

    /**
     * @var TwitterExporter
     */
    private $exporter = null;

    /**
     * @var Oauth
     */
    private $oauth;

    /**
     * TwitterConfiguration constructor.
     * @param Oauth $oauth
     */
    public function __construct(Oauth $oauth)
    {
        $this->oauth = $oauth;
    }

    /**
     * @return TwitterConfiguration|ManifestInterface
     */
    public function getManifest()
    {
        if (null === $this->manifest) {
            $this->manifest = $this;
        }

        return $this->manifest;
    }

    /**
     * @return TwitterConfiguration|ConfigurationInterface
     */
    public function getConfiguration()
    {
        if (null === $this->configuration) {
            $this->configuration = $this;
        }

        return $this->configuration;
    }

    /**
     * @param OptionsInterface $options
     * @return TwitterExporter
     */
    public function getExporter(OptionsInterface $options)
    {
        if (null === $this->exporter) {
            $this->exporter = new TwitterExporter($options, $this->oauth);
        }

        return $this->exporter;
    }

    /**
     * @return mixed
     */
    public function getForm()
    {
        return TwitterType::class;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'twitter';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Twitter';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Twitter OAuth adapter';
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0';
    }
}
