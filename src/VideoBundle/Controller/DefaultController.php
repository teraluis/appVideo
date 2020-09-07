<?php

namespace VideoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use VideoBundle\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;

class DefaultController extends Controller {

    /**
     * @Route("/")
     */
    public function indexAction() {
        return $this->render('VideoBundle:Default:index.html.twig');
    }

    /**
     * @Route("/login",name="login",methods={"POST"})
     */
    public function loginAction(Request $request) {
        $helpers = $this->get("app.helpers");
        $jwtAuth = $this->get("app.jwt_auth");
        $json = json_decode(
                $request->getContent(), true
        );
        $email = isset($json['email']) ? $json['email'] : null;
        $pwd = isset($json['password']) ? $json['password'] : null;
        $hash = isset($json['hash']) ? $json['hash'] : null;
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('VideoBundle:Users')->findBy(['email' => $email]);
        $emailConstraint = new Assert\Email();
        $emailConstraint->message = "This mail is not valid";
        $validateEmail = $this->get("validator")->validate($email, $emailConstraint);
        if (count($validateEmail) == 0 && $pwd != null) {
            $signup = $jwtAuth->signup($email, hash('sha256',$pwd), $hash);
            return new JsonResponse($signup);
        } else {
            return $helpers->json(['response' => 'invalid data']);
        }
    }

}
