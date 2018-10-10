<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    /**
     * @Route("/product", name="homepage")
     */
    public function index()
    {
        //$user = $this->get('security.token_storage')->getToken()->getUser();
//        $user=$this->getDoctrine()
//            ->getRepository(User::class)
//            ->findOneBy([
//                'username'=>'Maksim'
//            ]);
//        dd($user);
        return $this->render('product/index.html.twig', [
            'product'=>'Test product',
            'city'=>'Minsk',
        ]);
    }
}
