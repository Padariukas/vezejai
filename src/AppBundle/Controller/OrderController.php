<?php

namespace AppBundle\Controller;

use AppBundle\Entity\preke;
use AppBundle\Entity\uzsakymas;
use AppBundle\Form\prekeType;
use AppBundle\Form\uzsakymasType;
use AppBundle\Form\uzsakymo_busena_EmptyType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class OrderController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('', array('name' => $name));
    }


    /**
     *
     * @Route("/get_user_order", name="get_user_order")
     */
    //atvaizduoti visus uzsakymus
    public function getUserOrdersAction()
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getRepository(uzsakymas::class);
        $uzsakymai = $em->findBy(array('vartotojoId' => $user->getId()));

        return $this->render('@App/vartotojo uzsakymai.html.twig', array('uzsakymai' => $uzsakymai));
    }

    /**
     * @Route("/delete_order/{id}", name="delete_order")
     */


    //atsaukti uzsakyma
    public function cancelOrderAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $uzsakymas = $em->getRepository(uzsakymas::class)->find($id);
        $em->remove($uzsakymas);
        $em->flush();
        $this->addFlash(
            'error',
            'Užsakymas atšauktas!');
        return $this->redirectToRoute('get_user_order');
    }

    /**
     * @Route("/edit_item/{id}", name="edit_item")
     */

    // redaguoti prekes informacija
    public function editItemAction(Request $request, $id)
    {

        $preke = $this->getDoctrine()
            ->getRepository('AppBundle:preke')
            ->find($id);
        $form = $this->createForm(prekeType::class, $preke);

        $form->add('submit', SubmitType::class, array(
            'label' => 'Atnaujinti',
            'attr' => array('class' => 'btn btn-default pull-right')));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($preke);
            $em->flush();
            $this->addFlash(
                'notice',
                'Prekės informacija atnaujinta!');
            return $this->redirectToRoute('get_user_order');
        }

        return $this->render('AppBundle:default:redaguoti preke.html.twig', array('form' => $form->createView()));
    }


    /**
     * @Route("/change_order_status/{id}", name="change_order_status")
     */

    // keisti statusa
    public function changeOrderStatusAction(Request $request, $id)
    {

        $emm = $this->getDoctrine()->getRepository('AppBundle:uzsakymas')->find($id);
        $email = $emm->getUser()->getEmail();

        $choices = array();
        $status = $this->getDoctrine()
            ->getRepository('AppBundle:uzsakymas')
            ->find($id);
        $statusNumber = $status->getBusena()->getBusena();

        switch ($statusNumber) {
            case 1:
                $choices = array(
                    'Laukiama apmokėjimo' => 2
                );
                break;
            case 2:
                $choices = array(
                    'Vykdomas pirkimas' => 3
                );
                break;
            case 3:
                $choices = array(
                    'Prekė gauta į užsienio sandėlį' => 4
                );
                $instruktion = "Jūsų užsakyta prekė pristatyta į mūsų sandėlį užsienyje. Apie tolimesnę eigą informuosime el. paštu";
                $order_status = "Prekė gauta į užsienio sandėlį";
                break;
            case 4:
                $choices = array(
                    'Prekė transportuojama į Lietuvą' => 5
                );
                $instruktion = "Jūsų užsakyta prekė transportuojama į Lietuvą. Apie tolimesnę eigą informuosime el. paštu";
                $order_status = "Prekė transportuojama į Lietuvą";
                break;
            case 5:
                $choices = array(
                    'Prekė paruošta atsiimti' => 6
                );
                $instruktion = "Jūsų užsakyta prekė paruošta atsiimti";
                $order_status = "Prekė paruošta atsiimti";
                break;
            case 6:
                $choices = array(
                    'Užsakymas įvykdytas' => 7
                );
                $instruktion = "Jūsų užsakymas sėmingai užbaigtas. Dėkojame, kad naudojaės mūsų paslaugomis.";
                $order_status = "Užsakymas įvykdytas";
                break;

        }
        $busena = $this->getDoctrine()
            ->getRepository('AppBundle:uzsakymo_busena')
            ->find($id);
        $busena->setData(new \DateTime('now'));
        $form = $this->createForm(uzsakymo_busena_EmptyType::class, $busena);
        $form->add('busena', ChoiceType::class, array(
                'choices' => $choices
            )
        );

        $form->add('submit', SubmitType::class, array(
            'label' => 'Atnaujinti',
            'attr' => array('class' => 'btn btn-default pull-right')));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($busena);
            $em->flush();
            $this->addFlash(
                'notice',
                'Statusas atnaujintas!');


//laisko siuntimas
            $message = \Swift_Message::newInstance()
                ->setSubject('Užsakymo statusas atnaujintas!')
                ->setFrom('vezejai@vezejai.lt')
                ->setTo($email)
                ->setBody(
                    $this->renderView('@App/email/orderStatusEmail.html.twig', array(
                        'id' => $id,
                        'instruktion' => $instruktion,
                        'order_status' => $order_status)),
                    'text/html'
                );
            $this->get('mailer')->send($message);
            return $this->redirectToRoute('get_all_active_order');
        }

        return $this->render('AppBundle::changeOrderStatus.html.twig', array('form' => $form->createView()));

    }

    /**
     * @Route("/get_all_active_order", name="get_all_active_order")
     */

    public function getAllActiveOrder()
    {

        $em = $this->getDoctrine()->getManager();
        $uzsakymas = $em->getRepository('AppBundle:uzsakymas');
        $query = $uzsakymas->createQueryBuilder('uzsakymas');
        $query->select('uzsakymas')
            ->leftJoin('uzsakymas.busena', 'b')
            ->where('b.busena <7');
        $all = $query->getQuery()->getResult();

        return $this->render('AppBundle::uzsakymu valdymas.html.twig', array('all' => $all));
    }


    /**
     * @Route("/payed/{id}", name="payed")
     *
     */
    public function orderPayedAction($id)
    {
        $emm = $this->getDoctrine()->getRepository('AppBundle:uzsakymas')->find($id);
        $email = $emm->getUser()->getEmail();

        $instruktion = "Jūsų apmokėjimas gautas! Vykdomas prekės pirkimas";
        $order_status = "Vykdomas pirkimas";

        $em = $this->getDoctrine()->getManager();
        $status = $em->getRepository('AppBundle:uzsakymas')->find($id);

        $statusNumber = $status->getBusena()->getBusena();
        $newStatus = $statusNumber + 1;
        $status->getBusena()->setBusena($newStatus);
        $status->getBusena()->setData(new \DateTime('now'));
        $status->getSaskaita()->setApmoketa(true);
        $status->getSaskaita()->setApmokejimoData(new \DateTime('now'));

        //laisko siuntimas
        $message = \Swift_Message::newInstance()
            ->setSubject('Užsakymo statusas atnaujintas!')
            ->setFrom('vezejai@vezejai.lt')
            ->setTo($email)
            ->setBody(
                $this->renderView('@App/email/orderStatusEmail.html.twig', array(
                    'id' => $id,
                    'instruktion' => $instruktion,
                    'order_status' => $order_status)),
                'text/html'
            );
        $this->get('mailer')->send($message);

        $em->flush();

        $this->addFlash(
            'notice',
            'Mokėjimas sėkmingai užregistruotas!');

        return $this->redirectToRoute('get_all_active_order');

    }
}
