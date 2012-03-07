<?php

/*
 * This file is part of the CCDN ForumBundle
 *
 * (c) CCDN (c) CodeConsortium <http://www.codeconsortium.com/> 
 * 
 * Available on github <http://www.github.com/codeconsortium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CCDNForum\ForumBundle\Form\Handler;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

use CCDNComponent\CommonBundle\Entity\Manager\EntityManagerInterface;

//use CCDNForum\ForumBundle\Entity\Manager\TopicManager;
//use CCDNForum\ForumBundle\Form\Type\TopicType;
//use CCDNForum\ForumBundle\Form\Type\PostType;
//use CCDNForum\ForumBundle\Entity\Post;

/**
 * 
 * @author Reece Fowell <reece@codeconsortium.com> 
 * @version 1.0
 */
class TopicInsertFormHandler
{
	
	
	/**
	 *
	 * @access protected
	 */
	protected $factory;
	
	
	/**
	 *
	 * @access protected
	 */
	protected $container;
	
	
	/**
	 *
	 * @access protected
	 */
	protected $request;
	
	
	/**
	 *
	 * @access protected
	 */
	protected $manager;
	
	
	/**
	 *
	 * @access protected
	 */
	protected $options;
	
	
	/**
	 *
	 * @access protected
	 */
	protected $form;


	/**
	 *
	 * @access public
	 * @param FormFactory $factory, ContainerInterface $container, EntityManagerInterface $manager
	 */
	public function __construct(FormFactory $factory, ContainerInterface $container, EntityManagerInterface $manager)
	{
		$this->options = array();
		$this->factory = $factory;
		$this->container = $container;
		$this->manager = $manager;

		$this->request = $container->get('request');
	}
	
	
	/**
	 *
	 * @access public
	 * @param Array() $options
	 * @return $this
	 */
	public function setOptions($options = null )
	{
		$this->options = $options;
		
		return $this;
	}
	
	
	/**
	 *
	 * @access public
	 * @return bool
	 */
	public function process()
	{		
		$this->getForm();
		
		if ($this->request->getMethod() == 'POST')
		{
			
			$this->form->bindRequest($this->request);
		
			$formData = $this->form->getData();

			$formData->setCreatedDate(new \DateTime());
			$formData->setCreatedBy($this->options['user']);

			$formData->getTopic()->setViewCount(0);
			$formData->getTopic()->setReplyCount(0);

			$formData->getTopic()->setBoard($this->options['board']);				
				
//			$attachment = $formData->getAttachment();
				
			if ($this->form->isValid())
			{	
				$this->onSuccess($this->form->getData());
				
				return true;				
			}
		}

		return false;
	}
	
	
	/**
	 *
	 * @access public
	 * @return Form
	 */
	public function getForm()
	{
		if ( ! $this->form)
		{
			$postType = $this->container->get('ccdn_forum_forum.post.form.type');
			$postType->setOptions($this->options);
			$topicType = $this->container->get('ccdn_forum_forum.topic.form.type');
			
			$this->form = $this->factory->create($postType);
			$this->form->add($this->factory->create($topicType));
		}
		
		return $this->form;
	}
	
	
	/**
	 *
	 * @access protected
	 * @param $entity
	 * @return TopicManager
	 */
	protected function onSuccess($entity)
    {
		return $this->manager->insert($entity)->flushNow();
    }

	public function getCounters()
	{
		return $this->manager->getCounters();
	}

}