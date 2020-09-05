<?php

namespace VideoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->render('VideoBundle:Default:index.html.twig');
    }
    
     /**
     * @Route("/get/users",name="getUsers")
     */
    public function getUsersAction(Request $request)
    {   
        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager(); 
        $user = $em->getRepository('VideoBundle:Users')->findAll();
        return $helpers->json($user);
    }
     /**
     * @Route("/user",name="user")
     */    
    public function userAction(Request $request) {
        $helpers = $this->get("app.helpers");
        $all = $request->query->all();
        $name = $request->query->get('name');
        $em = $this->getDoctrine()->getManager(); 
        $user = $em->getRepository('VideoBundle:Users')->findBy(['name'=>$name]);
        return $helpers->json($user);
    }
    /**
     * @Route("/login",name="login",methods={"POST"})
    */  
    public function loginAction(Request $request) {
        $helpers = $this->get("app.helpers");
        $json = json_decode(
            $request->getContent(),
            true
        );
        $email = isset($json['email'])? $json['email']:null;
        $pwd = isset($json['password'])? $json['password']:null;
        $em = $this->getDoctrine()->getManager(); 
        $user = $em->getRepository('VideoBundle:Users')->findBy(['email'=>$email]);
        if($user !== null){
            return $helpers->json(['response'=>'user doesnt exists']);
        }else {
            if($pwd === $user['password']){
                return $helpers->json(['response'=>'ok']);
            }else {
                return $helpers->json(['response'=>'password error']);
            }
        }
    }    
}
