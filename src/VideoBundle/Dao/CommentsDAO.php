<?php

namespace VideoBundle\Dao;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use VideoBundle\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;

class CommentsDAO {

    public function getCommentsFrom($userId) {
        $dql = "SELECT v VideoBundle:Comments WHERE v.id = $userId ORDER BY v.id DESC";
        $em = $this->getDoctrine()->getEntityManager();
        $query = $em->createQuery($dql);
        return $query;
    }


}
