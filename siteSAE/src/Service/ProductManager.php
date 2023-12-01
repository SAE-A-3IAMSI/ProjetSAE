<?php

namespace App\Service;

use App\Entity\Product;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductManager implements ProductManagerInterface
{
    public function __construct(
        #[Autowire('%directory_product_picture%')] private string $directory
    ){}

    public function saveProductPicture(Product $product, ?UploadedFile $file) : void {
        if($file != null) {
            $fileName = uniqid() . '.' . $file->guessExtension();
            $file->move($this->directory, $fileName);
            $product->setImageProduct($fileName);
        }
    }
}