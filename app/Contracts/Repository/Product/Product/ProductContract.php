<?php
namespace App\Contracts\Repository\Product\Product;

use App\Models\Category;

/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 9/5/2016
 * Time: 2:24 PM
 */
interface ProductContract
{
    public function getProducts();

    public function getProduct($id, $fail = true);

    public function getProductsByCategory(Category $category);

    public function createProduct($options);

    public function updateProduct($id, $options);

    public function deleteProduct($id);

    public function getProductsCount();

    public function createSampleProduct(Category $category);
}