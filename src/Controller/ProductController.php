<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends AbstractController
{
    /**
     * @Route("{_locale}/product", name="homepage")
     */
    public function index(Request $request)
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
