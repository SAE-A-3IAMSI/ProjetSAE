<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\FlashMessageHelper;
use App\Service\ProductManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProductController extends AbstractController
{
    #[Route('/product/{filter}/filter', name: 'filterProduct')]
    public function filterProduct(string $filter, ProductRepository $productRepository): Response
    {
        $products = $productRepository->findBy(["type" => $filter]);
        return $this->render('shop/shop.html.twig', ['products' => $products, 'filter'=>$filter]);
    }

    #[Route('/product/{idProduct}', name: 'readProduct')]
    public function readProduct(int $idProduct, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($idProduct);
        return $this->render('product/productPage.html.twig', ["product" => $product]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/product', name: 'product')]
    public function product(ProductManagerInterface $productManager, ProductRepository $productRepository, Request $request, EntityManagerInterface $entityManager, FlashMessageHelper $flashHelper): Response {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product, [
            'method' => 'POST',
            'action' => $this->generateURL('product')
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $picture = $form["imageProduct"]->getData();
            $productManager->saveProductPicture($product, $picture);
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash("success", "Produit créé!");
            return $this->redirectToRoute('product');
        }
        $flashHelper->addFormErrorsAsFlash($form);
        $productList = $productRepository->findAll();
        return $this->render('product/productList.html.twig', ["products" => $productList, "form" => $form]);
    }

    #[IsGranted('PRODUCT_DELETE', 'product')]
    #[Route('/product/{id}/delete', name: 'deleteProduct', options: ["expose" => true], methods: ["DELETE"])]
    public function deleteProduct(#[MapEntity] Product $product, EntityManagerInterface $entityManager) : Response {
        $entityManager->remove($product);
        $entityManager->flush();
        return new JsonResponse(null, 204);
    }

    #[Route('/search', name: 'searchProduct', methods: ["POST"])]
    public function searchProduct(Request $request, ProductRepository $productRepository)
    {
        $searchTerm = $request->request->get('searchTerm');
        $products = $productRepository->findProduct($searchTerm);
        return $this->render('shop/shop.html.twig', ["products" => $products]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/product/{idProduct}/modifyPage', name: 'modifyProductPage')]
    public function modifyProductPage(Request $request, int $idProduct, ProductRepository $productRepository, ProductManagerInterface $productManager, EntityManagerInterface $entityManager, FlashMessageHelper $flashMessageHelper){

        $product = $productRepository->find($idProduct);
        $form = $this->createForm(ProductType::class, $product, [
            'method' => 'POST',
            'action' => $this->generateURL('modifyProductPage', ['idProduct'=>$idProduct]),
            'attr' => [
                'enctype' => 'multipart/form-data',
            ]
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $picture = $form["imageProduct"]->getData();
            $productManager->saveProductPicture($product, $picture);
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash("success", "Produit modifié!");
            return $this->redirectToRoute('readProduct', ['idProduct'=>$idProduct]);
        }
        $flashMessageHelper->addFormErrorsAsFlash($form);
        return $this->render('product/productPageModify.html.twig', ["product"=>$product,"form" => $form]);
    }


}
