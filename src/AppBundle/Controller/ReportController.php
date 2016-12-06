<?php

namespace AppBundle\Controller;

use AppBundle\Form\dataFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class ReportController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('', array('name' => $name));
    }

    /**
     * @Route("/get_money", name="get_money")
     */

    public function allPayedOrderAction(Request $request)
    {

        $form = $this->createForm(dataFormType::class);
        $form->add('submit', SubmitType::class, array(
            'label' => 'Gauti duomenis',
            'attr' => array('class' => 'btn btn-default pull-right')));

        $form->handleRequest($request);

        $suma = '';
        $nuo = '';

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->get('laikotarpis')->getData();
            $nuo = $data;
            $em = $this->getDoctrine()->getManager();
            $saskaitaRepository = $em->getRepository('AppBundle:uzsakymas');
            $query = $saskaitaRepository->createQueryBuilder('uzsakymas');
            $query->select('uzsakymas')
                ->leftJoin('uzsakymas.saskaita', 's')
                ->where('s.apmokejimoData >=:data')
                ->andWhere('s.apmoketa = :apmoketa')
                ->setParameter('data', $data)
                ->setParameter('apmoketa', true);

            $suma = $query->getQuery()->getResult();


        }

        return $this->render('@App/report/report.html.twig', array('suma' => $suma, 'nuo' => $nuo, 'form' => $form->createView()));
    }


    /**
     * @Route("/get_all_order_in_period", name="get_all_order_in_period")
     */

    public function getAllOrderAction(Request $request)
    {

        $form = $this->createForm(dataFormType::class);
        $form->add('submit', SubmitType::class, array(
            'label' => 'Gauti duomenis',
            'attr' => array('class' => 'btn btn-default pull-right')));

        $form->handleRequest($request);

        $suma = '';
        $nuo = '';

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->get('laikotarpis')->getData();
            $nuo = $data;
            $em = $this->getDoctrine()->getManager();
            $saskaitaRepository = $em->getRepository('AppBundle:uzsakymas');
            $query = $saskaitaRepository->createQueryBuilder('uzsakymas');
            $query->select('uzsakymas')
                ->leftJoin('uzsakymas.saskaita', 's')
                ->where('s.apmokejimoData >=:data')
                ->andWhere('s.apmoketa = :apmoketa')
                ->setParameter('data', $data)
                ->setParameter('apmoketa', true);

            $suma = $query->getQuery()->getResult();
        }
        return $this->render('@App/report/report2.html.twig', array('suma' => $suma, 'nuo' => $nuo, 'form' => $form->createView()));
    }

}
