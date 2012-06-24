<?php

namespace CCDNForum\ForumBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * SubscriptionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SubscriptionRepository extends EntityRepository
{
	
	
	
	/**
	 *
	 * @access public
	 */
	public function getTableIntegrityStatus()
	{
		$queryOrphanedSubscriptionCount = $this->getEntityManager()
			->createQuery('
				SELECT COUNT(DISTINCT s.id) AS orphanedSubscriptionCount
				FROM CCDNForumForumBundle:Subscription s
				WHERE s.topic IS NULL
			');
		$queryNullSubscribedCount = $this->getEntityManager()
			->createQuery('
				SELECT COUNT(DISTINCT s.id) AS nullSubscribedCount
				FROM CCDNForumForumBundle:Subscription s
				WHERE s.subscribed IS NULL 
			');
		$queryNullReadCount = $this->getEntityManager()
			->createQuery('
				SELECT COUNT(DISTINCT s.id) AS nullReadCount
				FROM CCDNForumForumBundle:Subscription s
				WHERE s.read_it IS NULL
			');

		try {
	        $result['orphanedSubscriptionCount'] = $queryOrphanedSubscriptionCount->getSingleScalarResult();
	    } catch (\Doctrine\ORM\NoResultException $e) {
	        $result['orphanedSubscriptionCount'] = '?';
	    }

		try {
	        $result['nullSubscribedCount'] = $queryNullSubscribedCount->getSingleScalarResult();
	    } catch (\Doctrine\ORM\NoResultException $e) {
	        $result['nullSubscribedCount'] = '?';
	    }

		try {
	        $result['nullReadCount'] = $queryNullReadCount->getSingleScalarResult();
	    } catch (\Doctrine\ORM\NoResultException $e) {
	        $result['nullReadCount'] = '?';
	    }
	
		return $result;
	}
	
	

	/**
	 *
	 * @access public
	 * @param int $status_code
	 */	
	public function findForUserById($userId)
	{

		$query = $this->getEntityManager()
			->createQuery('
				SELECT s, t, fp, lp, b, c FROM CCDNForumForumBundle:Subscription s 
				LEFT JOIN s.topic t
				LEFT JOIN t.last_post lp
				LEFT JOIN lp.created_by lpu
				LEFT JOIN t.first_post fp
				LEFT JOIN fp.created_by fpu
				LEFT JOIN t.board b
				LEFT JOIN b.category c
				WHERE s.owned_by = :userId AND s.subscribed = true 
				GROUP BY t.id
				ORDER BY t.id ASC')
			->setParameter('userId', $userId);
		
		try {
			return new Pagerfanta(new DoctrineORMAdapter($query));
	    } catch (\Doctrine\ORM\NoResultException $e) {
	        return null;
	    }
	}
	


	/**
	 *
	 * @access public
	 * @param int $topicId, int $userId
	 */
	public function findTopicSubscriptionByTopicAndUserId($topicId, $userId)
	{
		$query = $this->getEntityManager()
			->createQuery('
				SELECT s, t FROM CCDNForumForumBundle:Subscription s
				LEFT JOIN s.topic t
				WHERE s.topic = :topicId AND s.owned_by = :userId')
			->setParameters(array('topicId' => $topicId, 'userId' => $userId));
					
		try {
	        return $query->getSingleResult();
	    } catch (\Doctrine\ORM\NoResultException $e) {
	        return null;
	    }
	}
	
	
	
	/**
	 *
	 *
	 */
	public function getSubscriberCountForTopicById($topicId)
	{
		$query = $this->getEntityManager()
			->createQuery('	
				SELECT COUNT(s.id)
				FROM CCDNForumForumBundle:Subscription s
				WHERE s.topic = :id AND s.subscribed = TRUE')
			->setParameter('id', $topicId);
			
		try {
	        return $query->getSingleScalarResult();
	    } catch (\Doctrine\ORM\NoResultException $e) {
	        return null;
	    }
	
	}
	
}