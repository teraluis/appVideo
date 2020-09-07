<?php

namespace VideoBundle\Dao;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use VideoBundle\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;

class VideoDAO {
    public function getVideosFrom($userId) {
        $dql = "SELECT v VideoBundle:Videos WHERE v.id = $userId ORDER BY v.id DESC";
        $em = $this->getDoctrine()->getEntityManager();
        $query = $em->createQuery($dql);
        return $query;
    }
}
