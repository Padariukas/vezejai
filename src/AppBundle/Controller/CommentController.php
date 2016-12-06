<?php

namespace AppBundle\Controller;

use AppBundle\Entity\atsiliepimas;
use AppBundle\Form\atsiliepimasType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class CommentController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('', array('name' => $name));
    }


    /**
     *
     * @Route("/new_comment", name="new_comments")
     */

    public function newCommentAction(Request $request)
    {
        $ip = $this->container->get('request_stack')->getCurrentRequest()->getClientIp();
        $username = $this->getUser();

        $atsiliepimas = new atsiliepimas();
        $atsiliepimas->setIp($ip);
        $atsiliepimas->setVardas($username);
        $atsiliepimas->setData(new \DateTime('now'));

        $form = $this->createForm(atsiliepimasType::class, $atsiliepimas);
        $form->add('submit', SubmitType::class, array(
            'label' => 'Palikti komentarą',
            'attr' => array('class' => 'btn btn-default pull-right')));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($atsiliepimas);
            $em->flush();

            $this->addFlash(
                'notice',
                'Jūsų komentaras išsaugotas!');

            return $this->redirectToRoute('get_user_order');

        }

        return $this->render('AppBundle:comment:newComment.html.twig', array('form' => $form->createView()));

    }

    public function getCommentsAction()
    {

    }
}
