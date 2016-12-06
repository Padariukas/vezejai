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

    public function allPayedOrder(Request $request)
    {

        $form = $this->createForm(dataFormType::class);
        $form->add('submit', SubmitType::class, array(
            'label' => 'Gauti duomenis',
            'attr' => array('class' => 'btn btn-default pull-right')));

        $form->handleRequest($request);

        $suma = '';

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->get('laikotarpis')->getData();
            $em = $this->getDoctrine()->getManager();
            $saskaitaRepository = $em->getRepository('AppBundle:saskaita');
            $query = $saskaitaRepository->createQueryBuilder('saskaita');
            $query->select('SUM(saskaita.suma)')
                ->where('saskaita.apmokejimoData >=:data')
                ->andWhere('saskaita.apmoketa = :apmoketa')
                ->setParameter('data', $data)
                ->setParameter('apmoketa', true);

            $suma = $query->getQuery()->getSingleScalarResult();
        }

        return $this->render('@App/report/report.html.twig', array('suma' => $suma, 'form' => $form->createView()));
    }


    /**
     * @Route("/get_all_unpayed", name="get_all_unpayed")
     */

    public function getAllUnpayedOrder()
    {

        $em = $this->getDoctrine()->getManager();
        $uzsakymasRepository = $em->getRepository('AppBundle:uzsakymas');
        $query = $uzsakymasRepository->createQueryBuilder('uzsakymas');
        $query->select('uzsakymas', 'uzsakymas.saskaita')
            ->where('uzsakymas.saskaita.apmoketa >=:apmoketa')
            ->setParameter('apmoketa', false);

        $allUnpayed = $query->getQuery()->getSingleScalarResult();
        var_dump($allUnpayed);

        return $this->render('@App/report/report.html.twig', array());
    }

}
