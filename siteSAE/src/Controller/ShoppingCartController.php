<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\FlashMessageHelperInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShoppingCartController extends AbstractController
{
    #[Route('/product/{idProduct}/addToShoppingCart', name: 'addToShoppingCart')]
    public function addToShoppingCart(int $idProduct, RequestStack $session, ProductRepository $repository, FlashMessageHelperInterface $flashMessageHelper): Response
    {
        $shoppingCart = $session->getSession()->get('shoppingCart', []);
        if($repository->find($idProduct) != null){
            if(!empty($shoppingCart[$idProduct])){
                $shoppingCart[$idProduct]++;
            }
            else{
                $shoppingCart[$idProduct] = 1;
            }
            $session->getSession()->set('shoppingCart', $shoppingCart);
        }
        else {
            $this->addFlash("error","Le produit que vous avez essayé d'ajouter à votre panier n'existe pas");
        }

        return $this->redirectToRoute('shop');
    }

    #[Route('/product/{idProduct}/removeToShoppingCartItem', name: 'removeShoppingCartItem')]
    public function removeShoppingCartItem(int $idProduct, RequestStack $session): Response
    {
        $shoppingCart = $session->getSession()->get('shoppingCart', []);
        if(!empty($shoppingCart[$idProduct])){
            unset($shoppingCart[$idProduct]);
        }
        $session->getSession()->set('shoppingCart', $shoppingCart);
        return $this->redirectToRoute('shoppingCart'); // Rediriger vers la page d'accueil (ou une autre page)
    }

    #[Route('/shoppingCart', name: 'shoppingCart', options: ["expose" => false])]
    public function showShoppingCart(RequestStack $session, ProductRepository $repository): Response
    {
        $shoppingCart = $session->getSession()->get('shoppingCart', []);
        $panierwithData = [];
        foreach($shoppingCart as $id => $quantity){
            $panierwithData[] = [
                'product' => $repository->find($id),
                'quantity' => $quantity
            ];
        }
        return $this->render('shop/shoppingCart.html.twig', [
            'items' => $panierwithData,
        ]);
    }
}
