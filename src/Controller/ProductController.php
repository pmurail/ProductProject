<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ProductController extends AbstractController
{
    #[Route('/product/create', name: 'create_product')]
    public function create_product(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $product = new Product();
        $product->setName('Keyboard');
        $product->setPrice(1999);
        $product->setDescription('Ergonomic and stylish!');

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($product);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Saved new product with id '.$product->getId());
    }

    #[Route('/product', name: 'app_product')]
    public function index(): Response
    {
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
        ]);
    }

    #[Route('/product/{id}', name: 'product_show')]
    public function show(ManagerRegistry $doctrine, int $id): Response
    {
        $product = $doctrine->getRepository(Product::class)
            ->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        return new Response('Check out this great product: '.$product->getName());
        // ici on peut aussi utiliser Twig !
        // Avec des choses comme {{ product.name }}
        // Et finir par un return $this->render('product/show.html.twig', ['product' => $product]);
    }

    #[Route('/product/{id}/inflate', name: 'product_inflate')]
    public function inflateAction(ManagerRegistry $doctrine, int $id)
    {
        $product = $doctrine->getRepository(Product::class)->find($id);
        $product->setPrice($product->getPrice() * 1.01);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($product);
        $entityManager->flush();

        // methode remove 
        // $entityManager->remove($product);
        // $entityManager->flush();

        return $this->redirectToRoute('product_show', ['id'=>$product->getId()]);
  
    }
    #[Route('/products/morethan/{price}', name:'product_morethan')]
    public function showPriceMoreThanAction(ManagerRegistry $doctrine, $price): Response
  {
      $repository = $doctrine->getRepository(Product::class);
      $products = $repository->findAllGreaterThanPrice($price);
      $productsList = "";
      foreach ($products as $product) {
        $productsList .= $product->getName(). " at ". $product->getPrice(). "\n";
        }

      return new Response('Products whose price is more than '. $price .': '. $productsList);
  }
  
  #[Route('/product/createwithcategory', name :"create_product_with_cat")]
  public function createWithCategory(ManagerRegistry $doctrine)
    {
      $category = new Category();
      $category->setName('Computer Peripherals');

      $product = new Product();
      $product->setName('Keyboard');
      $product->setPrice(1999);
      $product->setDescription('Ergonomic and stylish!');

      // relates this product to the category
      $product->setCategory($category);

      $entityManager = $doctrine->getManager();
      $entityManager->persist($category);
      $entityManager->persist($product);
      $entityManager->flush();

      return new Response(
          'Saved new product with id: '.$product->getId()
        .' and new category with id: '.$category->getId()
      );
    }
}
