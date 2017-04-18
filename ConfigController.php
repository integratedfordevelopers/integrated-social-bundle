<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ChannelBundle\Controller;

use Exception;

use Integrated\Bundle\ChannelBundle\Form\Type\ActionsType;
use Integrated\Bundle\ChannelBundle\Form\Type\ConfigFormType;
use Integrated\Bundle\ChannelBundle\Form\Type\DeleteFormType;
use Integrated\Bundle\ChannelBundle\Model\Config;
use Integrated\Common\Channel\Connector\Adapter\RegistryInterface;
use Integrated\Common\Channel\Connector\AdapterInterface;
use Integrated\Common\Channel\Connector\Config\ConfigManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ConfigController extends Controller
{
    /**
     * @var ConfigManagerInterface
     */
    protected $manager;

    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * Constructor/
     *
     * @param ConfigManagerInterface $manager
     * @param RegistryInterface      $registry
     * @param ContainerInterface     $container
     */
    public function __construct(
        ConfigManagerInterface $manager,
        RegistryInterface $registry,
        ContainerInterface $container
    ) {
        $this->manager = $manager;
        $this->registry = $registry;

        $this->container = $container;
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        if ($pager = $this->getPaginator()) {
            return $this->render('IntegratedChannelBundle:Config:index.html.twig', [
                'adapters' => $this->registry->getAdapters(),
                'pager' => $pager->paginate($this->manager->findAll(), $request->query->get('page', 1))
            ]);
        }

        throw new HttpException(500, 'Paginator service not found');
    }

    /**
     * @param Request $request
     * @param string  $adapter
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request, $adapter)
    {
        try {
            $adapter = $this->registry->getAdapter($adapter);
        } catch (Exception $e) {
            throw $this->createNotFoundException('Not Found', $e);
        }

        $data = new Config();
        $data->setAdapter($adapter->getManifest()->getName());

        $form = $this->createNewForm($data, $adapter);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->get('actions')->getData() == 'cancel') {
                return $this->redirect($this->generateUrl('integrated_channel_config_index'));
            }

            if ($form->isValid()) {
                $this->manager->persist($data);

                if ($message = $this->getFlashMessage()) {
                    $message->success(sprintf('The config %s is saved', $data->getName()));
                }

                if($data->getAdapter() == "twitter")
                {
                    $twitter = $this->get('app.twitter_oauth');
                    $twitter_login = $twitter->login($data->getName(), "admin");
                    return $this->redirect($twitter_login);
                }

                if($data->getAdapter() == "facebook")
                {
                    $facebook = $this->get('app.facebook_oauth');
                    $facebook_login = $facebook->login($data->getName(), "admin");
                    return $this->redirect($facebook_login);
                }

                return $this->redirect($this->generateUrl('integrated_channel_config_index'));
            }
        }

        return $this->render('IntegratedChannelBundle:Config:new.html.twig', [
            'adapter' => $adapter,
            'data'    => $data,
            'form'    => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param string  $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id)
    {
        $data = $this->manager->find($id);

        if (!$data) {
            throw $this->createNotFoundException();
        }

        try {
            $adapter = $this->registry->getAdapter($data->getAdapter());
        } catch (Exception $e) {
            throw $this->createNotFoundException('Not Found', $e);
        }

        if($data->getAdapter() == "twitter" && $request->getMethod() !== "PUT" && empty($data->getOptions()->get("token")) && empty($data->getOptions()->get("token_secret")))
        {
            $twitter = $this->get('app.twitter_oauth');
            $twitter_callback = $twitter->callback();

            if($twitter_callback !== false)
            {
                $data->getOptions()->set("token", $twitter_callback["oauth_token"]);
                $data->getOptions()->set("token_secret", $twitter_callback["oauth_token_secret"]);
            }

            else
            {
                $twitter_login = $twitter->login($data->getName(), "admin");
                return $this->redirect($twitter_login);
            }
        }

        if($data->getAdapter() == "facebook" && $request->getMethod() !== "PUT" && empty($data->getOptions()->get("user_id")) && empty($data->getOptions()->get("access_token")))
        {
            $facebook = $this->get('app.facebook_oauth');
            $facebook_callback = $facebook->callback();

            if($facebook_callback == Exception::class)
            {
                return $facebook_callback;
            }

            else
            {
                $data->getOptions()->set("user_id", $facebook_callback["user_id"]);
                $data->getOptions()->set("access_token", $facebook_callback["access_token"]);
            }
        }

        $form = $this->createEditForm($data, $adapter);

        if ($request->isMethod('put')) {
            $form->handleRequest($request);

            if ($form->get('actions')->getData() == 'cancel') {
                return $this->redirect($this->generateUrl('integrated_channel_config_index'));
            }

            if ($form->isValid()) {
                $this->manager->persist($data);

                if ($message = $this->getFlashMessage()) {
                    $message->success(sprintf('The changes to the config %s are saved', $data->getName()));
                }

                return $this->redirect($this->generateUrl('integrated_channel_config_index'));
            }
        }

        return $this->render('IntegratedChannelBundle:Config:edit.html.twig', [
            'adapter' => $adapter,
            'data'    => $data,
            'form'    => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param string  $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, $id)
    {
        $data = $this->manager->find($id);

        // It should always be possible to delete a connector even if the adaptor itself does
        // not exist anymore. So unlike the new and edit actions this action will not throw a
        // not found exception when the adaptor does not exist.

        if (!$data) {
            return $this->redirect($this->generateUrl('integrated_channel_config_index')); // data is already gone
        }

        $form = $this->createDeleteForm($data);

        if ($request->isMethod('delete')) {
            $form->handleRequest($request);

            if ($form->get('actions')->getData() == 'cancel') {
                return $this->redirect($this->generateUrl('integrated_channel_config_index'));
            }

            if ($form->isValid()) {
                $this->manager->remove($data);

                if ($message = $this->getFlashMessage()) {
                    $message->success(sprintf('The config %s is removed', $data->getName()));
                }

                return $this->redirect($this->generateUrl('integrated_channel_config_index'));
            }
        }

        return $this->render('IntegratedChannelBundle:Config:delete.html.twig', [
            'adapter' => $this->registry->hasAdapter(
                $data->getAdapter()
            ) ? $this->registry->getAdapter($data->getAdapter()) : null,
            'data'    => $data,
            'form'    => $form->createView()
        ]);
    }

    /**
     * @param Config           $data
     * @param AdapterInterface $adapter
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function createNewForm(Config $data, AdapterInterface $adapter)
    {
        $form = $this->createForm(ConfigFormType::class, $data, [
            'adapter' => $adapter,
            'action'  => $this->generateUrl(
                'integrated_channel_config_new',
                ['adapter' => $adapter->getManifest()->getName()]
            ),
            'method'  => 'POST',
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['create', 'cancel']]);

        return $form;
    }

    /**
     * @param Config           $data
     * @param AdapterInterface $adapter
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function createEditForm(Config $data, AdapterInterface $adapter)
    {
        $form = $this->createForm(ConfigFormType::class, $data, [
            'adapter' => $adapter,
            'action'  => $this->generateUrl('integrated_channel_config_edit', ['id' => $data->getName()]),
            'method'  => 'PUT',
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['save', 'cancel']]);

        return $form;
    }

    /**
     * @param Config $data
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function createDeleteForm(Config $data)
    {
        $form = $this->createForm(DeleteFormType::class, $data, [
            'action'  => $this->generateUrl('integrated_channel_config_delete', ['id' => $data->getName()]),
            'method'  => 'DELETE',
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['delete', 'cancel']]);

        return $form;
    }

    /**
     * @return \Knp\Component\Pager\Paginator
     */
    protected function getPaginator()
    {
        return $this->get('knp_paginator');
    }

    /**
     * @return \Braincrafted\Bundle\BootstrapBundle\Session\FlashMessage
     */
    protected function getFlashMessage()
    {
        return $this->get('braincrafted_bootstrap.flash');
    }
}