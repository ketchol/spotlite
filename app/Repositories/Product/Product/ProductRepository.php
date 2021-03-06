<?php
namespace App\Repositories\Product\Product;

use App\Contracts\Repository\Product\Product\ProductContract;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 9/5/2016
 * Time: 2:26 PM
 */
class ProductRepository implements ProductContract
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getProducts()
    {
        $products = Product::all();
        return $products;
    }

    public function getProduct($id, $fail = true)
    {
        if ($fail === true) {
            $product = Product::findOrFail($id);
        } else {
            $product = Product::find($id);
        }
        return $product;
    }

    public function getProductsByCategory(Category $category)
    {
        if ($this->request->has('keyword') && !empty($this->request->get('keyword')) && strpos(strtolower($category->category_name), strtolower($this->request->get('keyword'))) === FALSE) {
            $productsBuilder = $category->filteredProducts()->orderBy('product_order')->orderBy('product_id');
        } else {
            $productsBuilder = $category->products()->orderBy('product_order')->orderBy('product_id');
        }
        if ($this->request->has('start')) {
            $productsBuilder->skip($this->request->get('start'));
        }
        if ($this->request->has('length')) {
            $productsBuilder->take($this->request->get('length'));
        }
        return $productsBuilder->get();
    }

    public function createProduct($options)
    {
        $options['user_id'] = auth()->user()->getKey();
        $product = Product::create($options);
        if (isset($options['meta'])) {
            $meta = $product->meta;
            $meta->brand = isset($options['meta']['brand']) && !empty($options['meta']['brand']) ? $options['meta']['brand'] : null;
            $meta->sku = isset($options['meta']['sku']) && !empty($options['meta']['sku']) ? $options['meta']['sku'] : null;
            $meta->colour = isset($options['meta']['colour']) && !empty($options['meta']['colour']) ? $options['meta']['colour'] : null;
            $meta->size = isset($options['meta']['size']) && !empty($options['meta']['size']) ? $options['meta']['size'] : null;
            $meta->supplier = isset($options['meta']['supplier']) && !empty($options['meta']['supplier']) ? $options['meta']['supplier'] : null;
            $meta->cost_price = isset($options['meta']['cost_price']) && !empty($options['meta']['cost_price']) ? $options['meta']['cost_price'] : null;
            $meta->save();
        }
        return $product;
    }

    public function updateProduct($id, $options)
    {
        $product = $this->getProduct($id);
        $product->update($options);
        if (isset($options['meta'])) {
            $meta = $product->meta;
            $meta->brand = isset($options['meta']['brand']) && !empty($options['meta']['brand']) ? $options['meta']['brand'] : null;
            $meta->sku = isset($options['meta']['sku']) && !empty($options['meta']['sku']) ? $options['meta']['sku'] : null;
            $meta->colour = isset($options['meta']['colour']) && !empty($options['meta']['colour']) ? $options['meta']['colour'] : null;
            $meta->size = isset($options['meta']['size']) && !empty($options['meta']['size']) ? $options['meta']['size'] : null;
            $meta->supplier = isset($options['meta']['supplier']) && !empty($options['meta']['supplier']) ? $options['meta']['supplier'] : null;
            $meta->cost_price = isset($options['meta']['cost_price']) && !empty($options['meta']['cost_price']) ? $options['meta']['cost_price'] : null;
            $meta->save();
        }
        return $product;
    }

    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return true;
    }

    public function getProductsCount()
    {
        return auth()->user()->products->count();
    }

    public function createSampleProduct(Category $category)
    {
        return Product::create(array(
            "product_name" => "My First Product",
            "category_id" => $category->getKey(),
            "user_id" => $category->user_id,
        ));
    }
}