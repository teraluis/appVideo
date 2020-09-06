<?php

namespace VideoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use VideoBundle\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
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
        $all = $request->query->all();//on recupere tout
        $name = $request->query->get('name');
        $em = $this->getDoctrine()->getManager(); 
        $user = $em->getRepository('VideoBundle:Users')->findBy(['name'=>$name]);
        return $helpers->json($user);
    }
    
     /**
     * @Route("/user/{id}",name="userid")
     */      
    public function userById($id){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('VideoBundle:Users')->find($id);
        return new JsonResponse(array('user' => $user->getName()));
    }
    
    /**
     * @Route("/login",name="login",methods={"POST"})
    */  
    public function loginAction(Request $request) {
        $helpers = $this->get("app.helpers");
        $jwtAuth = $this->get("app.jwt_auth");
        $json = json_decode(
            $request->getContent(),
            true
        );
        $email = isset($json['email'])? $json['email']:null;
        $pwd = isset($json['password'])? $json['password']:null;
        $hash = isset($json['hash'])? $json['hash']:null;
        $em = $this->getDoctrine()->getManager(); 
        $user = $em->getRepository('VideoBundle:Users')->findBy(['email'=>$email]);
        $emailConstraint = new Assert\Email();
        $emailConstraint->message = "This mail is not valid";
        $validateEmail = $this->get("validator")->validate($email, $emailConstraint);
        if(count($validateEmail) == 0 && $pwd != null ) {
            $signup = $jwtAuth->signup($email,$pwd,$hash);
            return new JsonResponse($signup);
        }else {
            return $helpers->json(['response' => 'invalid data']);
        }
    }
   
    /**
     * @Route("/createuser",name="createuser",methods={"POST"})
    */
    public function createUserAction(Request $request){
        $helpers = $this->get("app.helpers");
        $jwtAuth = $this->get("app.jwt_auth");
        $json = json_decode(
            $request->getContent(),
            true
        );
        $email = isset($json['email'])? $json['email']:null;
        $pwd = isset($json['password'])? $json['password']:null;
        $name = isset($json['name'])? $json['name']:null;
        $surname = isset($json['surname'])? $json['surname']:null;
        $image = isset($json['image'])? $json['image']:null;
        $role = isset($json['role'])? $json['role']:null;
        $createdAt = new \DateTime("now");   
        $user = new Users();
        $user->setName($name);
        $user->setSurname($surname);
        $user->setEmail($email);
        $user->setPassword($pwd);
        $user->setImage($image);
        $user->setRole($role);
        $user->setCreatedAt($createdAt);        
        $entityManager = $this->getDoctrine()->getManager();
        $emailConstraint = new Assert\Email();
        $nameConstraint = new Assert\Regex(['pattern' => '/^\w+/']);
        $surnameConstraint = new Assert\Regex(['pattern' => '/^\w+/']);
        $emailConstraint->message = "This mail is not valid";
        $surnameConstraint->message = "This surname is not valid";
        $nameConstraint->message = "This name is not valid";
        $validateEmail = $this->get("validator")->validate($email, $emailConstraint);
        $validateName = $this->get("validator")->validate($name,$nameConstraint);
        try {
            if(count($validateEmail) == 0 && $pwd != null && strlen($pwd)>3 && count($validateName) == 0) {
                // tell Doctrine you want to (eventually) save the Product (no queries yet)
                $entityManager->persist($user);
                // actually executes the queries (i.e. the INSERT query)
                $entityManager->flush();
                return new JsonResponse(array('response'=>'ok','userId' => $user->getId()));
            }else {
                return new JsonResponse(array('response'=>'wrong email or password'));
            }
                    
        } catch (\Exception $ex) {
            return new JsonResponse(array('response' => 'error'.$ex->getMessage()));
        }
        
        
    }    
}
