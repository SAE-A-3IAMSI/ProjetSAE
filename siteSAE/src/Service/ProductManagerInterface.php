<?php

namespace App\Service;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ProductManagerInterface
{
    public function saveProductPicture(Product $product, ?UploadedFile $file): void;
}