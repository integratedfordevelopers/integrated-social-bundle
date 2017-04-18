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

use Integrated\Common\Channel\Connector\AdapterInterface;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;
use Integrated\Common\Channel\Connector\ConfigurableInterface;

use Integrated\Common\Channel\Exporter\ExportableInterface;
use Integrated\Bundle\SocialBundle\Social\Twitter\Oauth;

class TwitterAdapter implements AdapterInterface, ConfigurableInterface, ExportableInterface
{
    /**
     * @var TwitterManifest
     */
    private $manifest = null;

    /**
     * @var TwitterConfiguration
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

    public function __construct(Oauth $oauth)
    {
        $this->oauth = $oauth;
    }

    /**
     * {@inheritdoc}
     */
    public function getManifest()
    {
        if (null === $this->manifest) {
            $this->manifest = new TwitterManifest();
        }

        return $this->manifest;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        if (null === $this->configuration) {
            $this->configuration = new TwitterConfiguration();
        }

        return $this->configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getExporter(OptionsInterface $options)
    {
        if (null === $this->exporter) {
            $this->exporter = new TwitterExporter($options, $this->oauth);
        }

        return $this->exporter;
    }
}
