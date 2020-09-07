<?php

namespace VideoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use VideoBundle\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;

class UserController extends Controller {

    /**
     * @Route("/get/users",name="getUsers")
     */
    public function getUsersAction(Request $request) {
        $helpers = $this->get("app.helpers");
        $hash = $request->headers->get('authorization');
        $check = $helpers->authCheck($hash);
        $result = array();
        if ($check) {
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('VideoBundle:Users')->findAll();
            $result['status'] = 'ok';
            if (count($user) > 0) {
                $result['msg'] = count($user) . ' user(s) found';
                $result['code'] = 200;
                $result['users'] = $user;
                return $helpers->json($result);
            } else {
                $result['msg'] = 'no users found';
                $result['code'] = 200;
                return $helpers->json($user);
            }
        } else {
            $result['status'] = 'error';
            $result['msg'] = 'expired';
            $result['code'] = 400;
            return $helpers->json($result);
        }
    }

    /**
     * @Route("/user",name="user")
     */
    public function searchUsersAction(Request $request) {
        $helpers = $this->get("app.helpers");
        $hash = $request->headers->get('authorization');
        $check = $helpers->authCheck($hash);
        if ($check) {
            //$all = $request->query->all(); on recupere tout les champs
            $name = $request->query->get('name');
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('VideoBundle:Users')->findBy(['name' => $name]);
            //var_dump($user); die();
            if (count($user) > 0) {
                $array_users = array();
                for ($i = 0; $i < count($user); $i++) {
                    $array_users[] = array('id' => $user[$i]->getId());
                }
                return $helpers->json(array(
                            'response' => 'not found',
                            'code' => 200,
                            'users' => $array_users
                ));
            } else {
                return $helpers->json(array('response' => 'not found', 'code' => 400));
            }
        } else {
            return $helpers->json(array('status' => 'logout'));
        }
    }

    /**
     * @Route("/user/{id}",name="userid")
     */
    public function userById($id, Request $request) {
        $hash = $request->headers->get('authorization');
        $helpers = $this->get("app.helpers");
        $check = $helpers->authCheck($hash);
        if ($check) {
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('VideoBundle:Users')->find($id);
            if (is_object($user)) {
                return new JsonResponse(array(
                    'status' => 'ok',
                    'code' => 200,
                    'user' => array('id' => $user->getId(), 'role' => $user->getRole(), 'email' => $user->getEmail())
                ));
            } else {
                return new JsonResponse(array(
                    'status' => 'user not found',
                    'code' => 200
                ));
            }
        } else {
            return new JsonResponse(array(
                'status' => 'logout',
                'code' => 200
            ));
        }
    }

    /**
     * @Route("/createuser",name="createuser",methods={"POST"})
     */
    public function createUserAction(Request $request) {
        $helpers = $this->get("app.helpers");
        $jwtAuth = $this->get("app.jwt_auth");
        $json = json_decode(
                $request->getContent(), true
        );
        $hash = $request->headers->get('authorization');
        $check = $helpers->authCheck($hash, false);
        $result = array();
        if ($check) {
            $email = isset($json['email']) ? $json['email'] : null;
            $pwd = isset($json['password']) ? $json['password'] : null;
            $name = isset($json['name']) ? $json['name'] : null;
            $surname = isset($json['surname']) ? $json['surname'] : null;
            $image = isset($json['image']) ? $json['image'] : null;
            $role = isset($json['role']) ? $json['role'] : null;
            $createdAt = new \DateTime("now");
            $pwdHash = hash('sha256', $pwd);
            $user = new Users();
            $user->setName($name);
            $user->setSurname($surname);
            $user->setEmail($email);
            $user->setPassword($pwdHash);
            $user->setImage($image);
            $user->setRole($role);
            $user->setCreatedAt($createdAt);
            $entityManager = $this->getDoctrine()->getManager();
            
            $emailConstraint = new Assert\Email();
            $emailConstraint->message = "This mail is not valid";
            $validateEmail = $this->get("validator")->validate($email, $emailConstraint);
            
            $stringConstraint = new Assert\Regex(['pattern' => '/^\w+/']);            
            $stringConstraint->message = "This name is not valid";
            
            $validateName = $this->get("validator")->validate($name, $stringConstraint);
            $validateSurname = $this->get("validator")->validate($surname, $stringConstraint);
            try {
                $em = $this->getDoctrine()->getManager();
                $isset_user = $em->getRepository("VideoBundle:Users")->findBy(
                        array(
                            "email" => $email
                        )
                );
                if (count($isset_user) == 0) {
                    $dataToValidate = [$validateEmail, $validateName, $validateSurname, $pwd];                    
                    if (count($validateEmail) == 0 && strlen($pwd)>3 && count($validateName) == 0 && count($validateSurname)) {
                        // tell Doctrine you want to (eventually) save the Product (no queries yet)
                        $entityManager->persist($user);
                        // actually executes the queries (i.e. the INSERT query)
                        $entityManager->flush();
                        $result['response'] = 'ok';
                        $result['msg'] = 'user add';
                        $result['code'] = 200;
                        $result['user'] = array('id' => $user->getId(), 'email' => $user->getEmail());
                        return new JsonResponse($result);
                    } else {
                        $result['response'] = 'error';
                        $result['msg'] = 'invalid data';
                        $result['code'] = 400;
                        return new JsonResponse($result);
                    }
                } else {
                    $result['response'] = 'error';
                    $result['msg'] = 'user alredy exists';
                    $result['code'] = 400;
                    return $helpers->json($result);
                }
            } catch (\Exception $ex) {
                return new JsonResponse(array('response' => 'error' . $ex->getMessage()));
            }
        } else {
            $result['status'] = 'error';
            $result['msg'] = 'expired';
            $result['code'] = 400;
            return $helpers->json($result);
        }
    }

    /**
     * @Route("/updateuser",name="updateuser",methods={"PATCH"})
     */
    public function updateUserAction(Request $request) {
        $helpers = $this->get("app.helpers");
        $jwtAuth = $this->get("app.jwt_auth");
        $json = json_decode(
                $request->getContent(), true
        );
        $hash = $request->headers->get('authorization');
        $check = $helpers->authCheck($hash);
        $result = array();
        $email = isset($json['email']) ? $json['email'] : null;
        $pwd = isset($json['password']) ? $json['password'] : null;
        $name = isset($json['name']) ? $json['name'] : null;
        $surname = isset($json['surname']) ? $json['surname'] : null;
        $image = isset($json['image']) ? $json['image'] : null;
        $role = isset($json['role']) ? $json['role'] : null;
        $createdAt = new \DateTime("now");
        $pwdHash = hash('sha256', $pwd);
        $emailConstraint = new Assert\Email();        
        $emailConstraint->message = "This mail is not valid";
        $validateEmail = $this->get("validator")->validate($email, $emailConstraint);
        
        $stringConstraint = new Assert\Regex(['pattern' => '/^\w+/']);
        $stringConstraint->message = "This name is not valid";
        
        $validateName = $this->get("validator")->validate($name, $stringConstraint);
        $validateSurname = $this->get("validator")->validate($surname, $stringConstraint);

        if ($check) {
            $identity = $helpers->authCheck($hash, true);
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('VideoBundle:Users')->find($identity->id);
            if (count($validateEmail) == 0 && strlen($pwd)>3 && count($validateName) == 0 && count($validateSurname)) {
                $user->setName($name);
                $user->setSurname($surname);
                $user->setEmail($email);
                $user->setPassword($pwdHash);
                $user->setImage($image);
                $user->setRole($role);
                $user->setCreatedAt($createdAt);
                $em->persist($user);
                $em->flush();
                $result['response'] = 'ok';
                $result['msg'] = 'user updated';
                $result['code'] = 200;
            } else {
                $result['response'] = 'error';
                $result['msg'] = 'invalid data';
                $result['code'] = 400;
            }
            return $helpers->json($result);
        } else {
            $result['response'] = 'error';
            $result['msg'] = 'user logout';
            $result['code'] = 400;
            return $helpers->json($result);
        }
    }

    /**
     * @Route("/user/upload/image",name="uploadimage",methods={"POST"})
     */
    public function uploadImageAction(Request $request) {
        $helpers = $this->get("app.helpers");
        $jwtAuth = $this->get("app.jwt_auth");
        $hash = $request->headers->get('authorization');
        $authCheck = $helpers->authCheck($hash);
        $result = array();
        if ($authCheck) {
            $identity = $helpers->authCheck($hash, true);
            $em = $this->getDoctrine()->getEntityManager();
            $user = $em->getRepository("VideoBundle:Users")->find($identity->id);
            //upload file
            $file = $request->files->get("image");
            if (!empty($file) && $file != null) {
                $ext = $file->guessExtension();
                $fileName = null;
                $fileName = time() . "." . $ext;
                $file->move("upload/users");
                $user->setImage($file_name);
                $em->persist($user);
                $em->flush();
                $result['status'] = "ok";
                $result['code'] = 200;
                $result['msg'] = "image upload";
            } else {
                $result['status'] = "error";
                $result['code'] = 400;
                $result['msg'] = "file is empty";
            }
        } else {
                $result['status'] = "error";
                $result['code'] = 400;
                $result['msg'] = "logout";
        }
        return $helpers->json($result);
    }



}
