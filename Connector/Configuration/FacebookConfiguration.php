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

use Integrated\Bundle\SocialBundle\Connector\Exporter\FacebookExporter;
use Integrated\Common\Channel\Connector\AdapterInterface;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;
use Integrated\Common\Channel\Connector\ConfigurableInterface;
use Integrated\Common\Channel\Connector\ConfigurationInterface;
use Integrated\Common\Channel\Connector\Adapter\ManifestInterface;

use Integrated\Common\Channel\Exporter\ExportableInterface;
use Integrated\Bundle\SocialBundle\Social\Facebook\Oauth;
use Integrated\Bundle\SocialBundle\Form\Type\FacebookType;

class FacebookConfiguration implements AdapterInterface, ConfigurableInterface, ExportableInterface, ConfigurationInterface, ManifestInterface
{
    /**
     * @var ManifestInterface
     */
    private $manifest = null;

    /**
     * @var ConfigurableInterface
     */
    private $configuration = null;

    /**
     * @var FacebookExporter
     */
    private $exporter = null;

    /**
     * @var Oauth
     */
    private $oauth;

    /**
     * FacebookConfiguration constructor.
     * @param Oauth $oauth
     */
    public function __construct(Oauth $oauth)
    {
        $this->oauth = $oauth;
    }

    /**
     * @return FacebookConfiguration|ManifestInterface
     */
    public function getManifest()
    {
        if (null === $this->manifest) {
            $this->manifest = $this;
        }

        return $this->manifest;
    }

    /**
     * @return FacebookConfiguration|ConfigurableInterface
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
     * @return FacebookExporter
     */
    public function getExporter(OptionsInterface $options)
    {
        if (null === $this->exporter) {
            $this->exporter = new FacebookExporter($options, $this->oauth);
        }

        return $this->exporter;
    }

    /**
     * @return mixed
     */
    public function getForm()
    {
        return FacebookType::class;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'facebook';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Facebook';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Facebook Oauth adapter';
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0';
    }
}
