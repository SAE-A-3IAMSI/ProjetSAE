<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShopController extends AbstractController
{
    #[Route('/shop', name: 'shop')]
    public function shop(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();
        return $this->render('shop/shop.html.twig', ["products" => $products]);
    }

}
