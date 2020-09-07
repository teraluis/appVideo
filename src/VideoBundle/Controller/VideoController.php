<?php

namespace VideoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use VideoBundle\Entity\Videos;
use Doctrine\ORM\EntityManagerInterface;

class VideoController extends Controller {

    public function newAction(Request $request) {
        $helpers = $this->get("app.helpers");
        $hash = $request->headers->get('authorization');
        $check = $helpers->authCheck($hash, false);
        $result = array();
        if ($check) {
            $identity = $helpers->authCheck($hash, true);
            $json = json_decode(
                    $request->getContent(), true
            );
            $createdAt = new \DateTime("now");
            $updatedAt = new \DateTime("now");
            $imagen = null;
            $videoPath = null;
            $user_id = ($identity->id != null) ? $identity->id : null;
            $title = (isset($json['title'])) ? $json['title'] : null;
            $description = (isset($json['description'])) ? $json['description'] : null;
            $status = (isset($json['status'])) ? $json['status'] : null;
            
            $em = $this->getDoctrine()->getEntityManager();
            $user = $em->getRepository('VideoBundle:Users')->find($identity->id);
            
            $video = new Videos();
            $video->setImage($imagen);
            $video->setUser($user);
            $video->setTitle($title);
            $video->setDescription($description);
            $video->setStatus($status);
            $video->setCreatedAt($createdAt);
            $video->setUpdatedAt($updatedAt);
            $result['status'] = 'ok';
            $result['msg'] = 'video add';
            $result['code'] = 200;
            return $helpers->json($result);
        } else {
            $result['status'] = 'error';
            $result['msg'] = 'expired';
            $result['code'] = 400;
            return $helpers->json($result);
        }
    }

}
