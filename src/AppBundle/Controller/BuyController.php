<?php

namespace AppBundle\Controller;

use AppBundle\Entity\saskaita;
use AppBundle\Form\saskaitaType;
use Proxies\__CG__\AppBundle\Entity\uzsakymo_busena;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\preke;
use AppBundle\Entity\uzsakymas;
use AppBundle\Form\prekeType;
use AppBundle\Form\uzsakymasType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\AbstractType;

class BuyController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('', array('name' => $name));
    }

    // This action builds new order form and saves data to database
    /**
     *
     * @Route("/new_order", name="new_order")
     */
    public function newOrderAction(Request $request)
    {
        //var_dump($this->container->get('request_stack')->getCurrentRequest()->getClientIp());
        $time = new \DateTime('now');
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $email = $user->getEmail();

        $uzsakymas = new uzsakymas();
        $uzsakymas->setUser($user);
        $uzsakymas->setData(new \DateTime('now'));

        $form = $this->createForm(uzsakymasType::class, $uzsakymas);
        $form->add('submit', SubmitType::class, array(
            'label' => 'Užsakyti',
            'attr' => array('class' => 'btn btn-default pull-right')));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($uzsakymas);
            $em->flush();

            //laisko siuntimas
            $message = \Swift_Message::newInstance()
                ->setSubject('Naujas užsakymas sukurtas!')
                ->setFrom('vezejai@vezejai.lt')
                ->setTo($email)
                ->setBody(
                    $this->renderView('@App/email/newOrderEmail.html.twig', array('uzsakymas' => $uzsakymas->getId())),
                    'text/html'
                );
            $this->get('mailer')->send($message);
            $this->addFlash(
                'notice',
                'Naujas užsakymas sėkmingas sukurtas! Apie visus užsakymo pasikeitimus informuosime el. paštu!');
            return $this->redirectToRoute('get_user_order');
        }

        return $this->render('AppBundle::uzsakymo langas.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/create_payment/{id}/{orderId}", name="create_payment")
     */
    //apmokejimo formavimas
    public function paymentAction(Request $request, $id, $orderId)
    {

        $emm = $this->getDoctrine()->getRepository('AppBundle:uzsakymas')->find($orderId);
        $email = $emm->getUser()->getEmail();
        //$user = $this->container->get('security.token_storage')->getToken()->getUser();
        //$email = $user->getEmail();

        $instruktion = " Jūsų užsakymas patvirtintas ir Jums suformuota saskaita. Prašome apmokėti sąskaitą.";
        $order_status = "Užsakymas patvirtintas! Sąskaita suformuota.";


        //uzsakymo repozitorija
        $em_u = $this->getDoctrine()->getManager();
        $status = $em_u->getRepository('AppBundle:uzsakymas')->find($orderId);
        // uzsetina statusa + 1
        $statusNumber = $status->getBusena()->getBusena();
        $newStatus = $statusNumber + 1;
        $status->getBusena()->setBusena($newStatus);
        $status->getBusena()->setData(new \DateTime('now'));

        // saskaitos repozitorija
        $saskaita = $this->getDoctrine()
            ->getRepository('AppBundle:saskaita')
            ->find($id);

        $time = new \DateTime('now');
        $saskaita->setData($time);
        $saskaita->setApmoketa(false);

        $form = $this->createForm(saskaitaType::class, $saskaita);

        $form->add('submit', SubmitType::class, array(
            'label' => 'Formuoti sąskaitą',
            'attr' => array('class' => 'btn btn-default pull-right')));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($saskaita);
            $em_u->flush();
            $em->flush();
            $this->addFlash(
                'notice',
                'Sąskaita sėkmingai sukurta! Statusas atnaujintas!');

            //laisko siuntimas
            $message = \Swift_Message::newInstance()
                ->setSubject('Užsakymo statusas atnaujintas!')
                ->setFrom('vezejai@vezejai.lt')
                ->setTo($email)
                ->setBody(
                    $this->renderView('@App/email/orderStatusEmail.html.twig', array(
                        'id' => $id,
                        'instruktion' => $instruktion . "Sąskaitos suma: " . $saskaita->getSuma() . " eurų",
                        'order_status' => $order_status)),
                    'text/html'
                );
            $this->get('mailer')->send($message);

            return $this->redirectToRoute('get_user_order');
        }

        return $this->render('AppBundle::createPayment.html.twig', array('form' => $form->createView()));

    }

    /**
     * @Route("/new_orders_list", name="new_orders_list")
     */

    //nauju uzsakymu atvaizdavimas
    public function getNewOrdersAction()
    {

        $em = $this->getDoctrine()->getManager();
        $filmRepository = $em->getRepository('AppBundle:uzsakymas');
        $query = $filmRepository->createQueryBuilder('uzsakymas');
        $query->select('uzsakymas')
            ->leftJoin('uzsakymas.busena', 'b')
            ->where('b.busena = 1 or b.busena = 2');
        $newOrders = $query->getQuery()->getResult();


        return $this->render('@App/newOrder.html.twig', array('newOrders' => $newOrders));
    }
}
