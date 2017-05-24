<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\SocialBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class TwitterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('token', 'text', ['attr' => ['readonly' => 'true']]);
        $builder->add('token_secret', 'text', ['attr' => ['readonly' => 'true']]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return "integrated_twitter_token";
    }
}
