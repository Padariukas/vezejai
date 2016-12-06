<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class DeleteUserController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('', array('name' => $name));
    }

    /**
     *
     * @Route("/get_all_user", name="get_all_user")
     */
    public function getAllUsers()
    {

        $users = $this->getDoctrine()->getRepository('AppBundle:User')->findAll();

        return $this->render('UserBundle::userList.html.twig', array('users' => $users));

    }


    public function deleteUserAction()
    {

    }

}
