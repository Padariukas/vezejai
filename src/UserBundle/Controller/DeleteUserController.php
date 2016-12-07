<?php

namespace UserBundle\Controller;

use AppBundle\Entity\istrinti_vartotojai;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Validator\Constraints\DateTime;


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

    /**
     *
     * @Route("/delete_user/{id}", name="delete_user")
     */


    public function deleteUserAction($id)
    {

        $deletedUser = new istrinti_vartotojai();

        $em = $this->getDoctrine()->getEntityManager();
        $em_d = $this->getDoctrine()->getEntityManager();
        //$em_d->getRepository('AppBundle:istrinti_vartotojai');
        $user = $em->getRepository('AppBundle:User')->find($id);

        $deletedUser->setVardas($user->getVardas());
        $deletedUser->setPavarde($user->getPavarde());
        $deletedUser->setElPastas($user->getEmail());
        $deletedUser->setData(new \DateTime('now'));
        $deletedUser->setPriezastis('Sistemos taisyklių pažeidimas');


        $em->remove($user);
        $em->flush();
        $em_d->persist($deletedUser);
        $em_d->flush();

        $this->addFlash(
            'notice',
            'Vartotojas pašalintas!');

        return $this->redirect($this->generateUrl('get_all_user'));

    }

}
