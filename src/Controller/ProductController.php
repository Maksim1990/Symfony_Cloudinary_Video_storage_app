<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    /**
     * @Route("/product", name="product_page")
     */
    public function index()
    {
        $arr=[1,2,3,4,5];
        dump("TET 1");
        var_dump('test');
        return $this->render('product/index.html.twig', [
            'product'=>'Test product',
            'city'=>'Minsk',
        ]);
    }
}
