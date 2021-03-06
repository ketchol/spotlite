<?php

namespace App\Http\Controllers\Product;

use App\Contracts\Repository\Product\Category\CategoryContract;
use App\Contracts\Repository\Product\Product\ProductContract;
use App\Contracts\Repository\Product\Site\SiteContract;
use App\Events\Products\Import\AfterStoreProducts;
use App\Events\Products\Import\AfterStoreSites;
use App\Events\Products\Import\BeforeStoreProducts;
use App\Events\Products\Import\BeforeStoreSites;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Crawler;
use App\Models\Domain;
use App\Models\DomainPreference;
use App\Models\Product;
use App\Models\ProductMeta;
use App\Models\Site;
use App\Models\SitePreference;
use App\Validators\Product\ImportProduct\StoreValidator;
use Illuminate\Http\Request;

use App\Http\Requests;
use Maatwebsite\Excel\Facades\Excel;

class ImportProductController extends Controller
{
    var $request;
    var $categoryRepo;
    var $productRepo;
    var $siteRepo;

    public function __construct(Request $request, CategoryContract $categoryContract, ProductContract $productContract, SiteContract $siteContract)
    {
        $this->request = $request;
        $this->categoryRepo = $categoryContract;
        $this->productRepo = $productContract;
        $this->siteRepo = $siteContract;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('products.import.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreValidator $storeValidator
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function storeProducts(StoreValidator $storeValidator)
    {
        event(new BeforeStoreProducts());

        set_time_limit(3000);
        $storeValidator->validate($this->request->all());
        $user = auth()->user();
        $file = $this->request->file('file');
        $products = [];
        $errors = [];

        /*TODO data collection and validation*/
        //import products

        $productCounts = $user->products()->count();
        $productNames = $user->products->pluck('product_name')->toArray();

        $subCriteria = $user->subscriptionCriteria();
        $productLimit = 0;
        if (!is_null($subCriteria)) {
            $productLimit = isset($subCriteria->product) ? $subCriteria->product : 0;
        }

        $result = Excel::load($file->getPathname(), function ($reader) use ($productLimit, $productNames, $productCounts, $user, &$products, &$errors) {
            $data = $reader->all();
            foreach ($data as $index => $product) {
                $rowNumber = $index + 2;
                if (!isset($product->product) || is_null($product->product)) {
                    $errors[] = "Product name is missing in 'Import Products' row #{$rowNumber}";
                }
                if (!isset($product->category) || is_null($product->category)) {
                    $errors[] = "Category name is missing in 'Import Products' row #{$rowNumber}";
                }

                if ($productLimit > 0) {
                    if (!in_array($product->product, $productNames)) {
                        $productCounts++;
                    }
                    if ($productCounts > $productLimit) {
                        $errors[] = "You have reached maximum amount of products. Please upgrade your subscription plan to import more products.";
                        break;
                    }
                }

                $productData = $product->all();
                $products [] = $productData;
            }
        }, 'Windows-1252');

        $products = collect($products);

        if (count($errors) > 0) {
            return response(compact(['errors']), 422);
        }
        /*VALIDATION FINISHED*/
        $warnings = [];

        $siteCounter = 0;
        $productCounter = 0;
        $categoryCounter = 0;

        $greatestCategoryOrder = $this->categoryRepo->getCategoriesCount();


        $groupedMetas = $products->groupBy(function ($item, $key) {
            return $item['category'] . "$ $" . $item['product'];
        });


        /*create new categories*/
        $spreadsheetCategories = $products->pluck('category')->unique()->values();
        $categoryNames = auth()->user()->categories->pluck('category_name')->unique()->values();
        $diffedCategories = $spreadsheetCategories->diff($categoryNames);
        $newCategories = [];
        foreach ($diffedCategories as $diffedCategory) {
            $newCategories[] = [
                'category_name' => $diffedCategory,
                'category_order' => $greatestCategoryOrder++,
                'user_id' => $user->getKey(),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        Category::insert($newCategories);

        $categories = $user->categories()->with('products')->get();
        $forProductPurposeCategories = [];
        foreach ($products as $product) {
            if (!array_key_exists($product['category'], $forProductPurposeCategories)) {
                $forProductPurposeCategories[$product['category']] = [];
            }
            if (!in_array($product['product'], $forProductPurposeCategories[$product['category']])) {
                $forProductPurposeCategories[$product['category']][] = $product['product'];
            }
        }


        $categoryCounter = count($newCategories);

        $newProducts = [];
        foreach ($forProductPurposeCategories as $category_name => $forProductPurposeCategory) {
            $category = $categories->filter(function ($category) use ($category_name) {
                return $category->category_name == $category_name;
            })->first();
            $forProductPurposeProducts = collect($forProductPurposeCategory);
            $forProductPurposeProducts = $forProductPurposeProducts->unique()->values();
            $productNames = $category->products->pluck('product_name')->unique()->values();
            $diffedProducts = $forProductPurposeProducts->diff($productNames);
            foreach ($diffedProducts as $diffedProduct) {
                $newProducts[] = [
                    'product_name' => $diffedProduct,
                    'category_id' => $category->getKey(),
                    'product_order' => 99999,
                    'user_id' => $user->getKey()
                ];
            }
        }
        $productCounter = count($newProducts);
        Product::insert($newProducts);
        $categories = $user->categories()->with('products')->get();
        foreach ($groupedMetas as $categoryAndProduct => $groupedMeta) {
            $groupedMeta = array_first(array_first($groupedMeta));
            list($tempCategory, $tempProduct) = explode('$ $', $categoryAndProduct);
            $category = $categories->filter(function ($category) use ($tempCategory, $tempProduct) {
                return $category->category_name == $tempCategory;
            })->first();
            $product = $category->products->filter(function ($product) use ($tempProduct) {
                return $product->product_name == $tempProduct;
            })->first();
            $meta = $product->meta;
            if (is_null($meta)) {
                $meta = new ProductMeta([
                    'sku' => isset($groupedMeta['sku']) ? $groupedMeta['sku'] : null,
                    'supplier' => isset($groupedMeta['supplier']) ? $groupedMeta['supplier'] : null,
                    'brand' => isset($groupedMeta['brand']) ? $groupedMeta['brand'] : null,
                    'cost_price' => isset($groupedMeta['cost_price']) ? $groupedMeta['cost_price'] : null,
                ]);
            } else {
                $meta->sku = isset($groupedMeta['sku']) ? $groupedMeta['sku'] : null;
                $meta->supplier = isset($groupedMeta['supplier']) ? $groupedMeta['supplier'] : null;
                $meta->brand = isset($groupedMeta['brand']) ? $groupedMeta['brand'] : null;
                $meta->cost_price = isset($groupedMeta['cost_price']) ? $groupedMeta['cost_price'] : null;
            }
            $meta->save();
        }

//        $product_ids = auth()->user()->products->pluck('product_id');
//
//        $newMetas = [];
//        foreach ($product_ids as $product_id) {
//            $newMetas[] = [
//                'product_id' => $product_id
//            ];
//        }
//        ProductMeta::insert($newMetas);
//
//
//        $products->each(function ($product, $index) use (&$greatestCategoryOrder, $user, &$warnings, &$siteCounter, &$productCounter, &$categoryCounter) {
//            $rowNumber = $index + 2;
//            /*IMPORT CATEGORIES*/
//            $category = $user->categories()->where('category_name', $product['category'])->first();
//            if (is_null($category)) {
//                if ($this->request->has('no_new_categories') && $this->request->get('no_new_categories') == 'on') {
//                    $warnings[] = "Category name in row #{$rowNumber} does not exist in your account, this product and its sites were NOT imported.";
//                    return true;
//                } else {
//                    $category = Category::create([
//                        'category_name' => $product['category'],
//                        'category_order' => $greatestCategoryOrder++,
//                        'user_id' => $user->getKey()
//                    ]);
//                    $categoryCounter++;
//                }
//            }
//            $existingProduct = $category->products()->where('product_name', $product['product'])->first();
//            if (is_null($existingProduct)) {
//                if ($this->request->has('no_new_products') && $this->request->get('no_new_products') == 'on') {
//                    $warnings[] = "Product '{$product['product']}' in row #{$rowNumber} does not exist in your account, this product and its sites were NOT imported.";
//                    return true;
//                } else {
//                    $existingProduct = Product::create([
//                        'product_name' => $product['product'],
//                        'user_id' => $user->getKey(),
//                        'product_order' => 99999,
//                        'category_id' => $category->getKey()
//                    ]);
//                    $productCounter++;
//                }
//            }
//            $meta = $existingProduct->meta;
//            if (array_has($product, 'sku') && !is_null(array_get($product, 'sku'))) {
//                $meta->sku = array_get($product, 'sku');
//            }
//            if (array_has($product, 'supplier') && !is_null(array_get($product, 'supplier'))) {
//                $meta->supplier = array_get($product, 'supplier');
//            }
//            if (array_has($product, 'brand') && !is_null(array_get($product, 'brand'))) {
//                $meta->brand = array_get($product, 'brand');
//            }
//            if (array_has($product, 'cost_price') && !is_null(array_get($product, 'cost_price'))) {
//                $meta->cost_price = array_get($product, 'cost_price');
//            }
//            $meta->save();
//
//        });

        $status = true;

        event(new AfterStoreProducts());

        return compact(['status', 'siteCounter', 'productCounter', 'categoryCounter', 'warnings']);
    }

    public function storeSites(StoreValidator $storeValidator)
    {
        event(new BeforeStoreSites());
        set_time_limit(3000);
        $storeValidator->validate($this->request->all());
        $user = auth()->user();

        $file = $this->request->file('file');

        $urls = [];
        $errors = [];

        /*TODO data collection and validation*/
        //import products
        $result = Excel::load($file->getPathname(), function ($reader) use (&$urls, &$errors) {
            $data = $reader->all();
            foreach ($data as $index => $url) {
                $rowNumber = $index + 2;
                if (!isset($url->category) || is_null($url->category)) {
                    $errors[] = "Category name is missing in row #{$rowNumber}";
                }
                if (!isset($url->product) || is_null($url->product)) {
                    $errors[] = "Product name is missing in row #{$rowNumber}";
                }
                if (!isset($url->url) || is_null($url->url)) {
                    $errors[] = "URL is missing in row #{$rowNumber}";
                } elseif (filter_var($url->url, FILTER_VALIDATE_URL) === false) {
                    $errors[] = "URL is invalid in row #{$rowNumber}";
                }
                $urlData = $url->all();
                $urls [] = $urlData;
            }
        }, 'Windows-1252');

        $urls = collect($urls);

        $siteLimit = null;
        if ($user->needSubscription && $user->subscription->isValid()) {
            $criteria = auth()->user()->subscriptionCriteria();
            if (isset($criteria->site) && $criteria->site != 0) {
                $siteLimit = intval($criteria->site);
            }
        }

        $groupedSites = $urls->groupBy(function ($item, $key) {
            return $item['category'] . "$ $" . $item['product'];
        });

        $categories = $user->categories()->with('products')->get();

        $newCategories = [];
        $greatestCategoryOrder = $this->categoryRepo->getCategoriesCount();
        $categoryNames = $categories->pluck('category_name');
        foreach ($urls as $url) {
//            $category = Category::where('category_name', '=', $url['category'])->first();
            $category = $categories->filter(function ($category) use ($url) {
                return $category->category_name == $url['category'];
            })->first();
            if (!is_null($category)) {
                if ($this->request->has('no_new_products')) {
//                    $product = $category->products()->where('product_name', '=', $url['product'])->first();
                    $product = $category->products->filter(function ($product) use ($url) {
                        return $product->product_name == $url['product'];
                    })->first();
                    if (is_null($product)) {
                        $errors[] = "Product {$url['product']} does not exist in Category {$url['category']} in your account.";
                    } else {
                        if (!is_null($siteLimit)) {
                            $numberOfImportingSites = count($groupedSites[$category->category_name . "$ $" . $product->product_name]);
                            if ($siteLimit - $product->sites()->count() < $numberOfImportingSites) {
                                $errors[] = "You have reached the site limit of your subscription plan. Please upgrade your subscription plan to add more sites.";
                                break;
                            }
                        }
                    }
                }
            } elseif ($this->request->has('no_new_categories')) {
                $errors[] = "{$url['category']} does not exist in your account.";
            }
        }

        if (count($errors) > 0) {
            return response(compact(['errors']), 422);
        }


        /*create new categories*/
        $spreadsheetCategories = $urls->pluck('category')->unique()->values();
        $categoryNames = $categories->pluck('category_name')->unique()->values();
        $diffedCategories = $spreadsheetCategories->diff($categoryNames);
        foreach ($diffedCategories as $diffedCategory) {
            $newCategories[] = [
                'category_name' => $diffedCategory,
                'category_order' => $greatestCategoryOrder++,
                'user_id' => $user->getKey(),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        Category::insert($newCategories);

        $categories = $user->categories()->with('products')->get();
        $forProductPurposeCategories = [];
        foreach ($urls as $url) {
            if (!array_key_exists($url['category'], $forProductPurposeCategories)) {
                $forProductPurposeCategories[$url['category']] = [];
            }
            if (!in_array($url['product'], $forProductPurposeCategories[$url['category']])) {
                $forProductPurposeCategories[$url['category']][] = $url['product'];
            }
        }


        $categoryCounter = count($newCategories);
        $siteCounter = 0;

        $newProducts = [];
        foreach ($forProductPurposeCategories as $category_name => $forProductPurposeCategory) {
            $category = $categories->filter(function ($category) use ($category_name) {
                return $category->category_name == $category_name;
            })->first();
            $forProductPurposeProducts = collect($forProductPurposeCategory);
            $forProductPurposeProducts = $forProductPurposeProducts->unique()->values();
            $productNames = $category->products->pluck('product_name')->unique()->values();
            $diffedProducts = $forProductPurposeProducts->diff($productNames);
            foreach ($diffedProducts as $diffedProduct) {
                $newProducts[] = [
                    'product_name' => $diffedProduct,
                    'category_id' => $category->getKey(),
                    'product_order' => 99999,
                    'user_id' => $user->getKey()
                ];
            }
        }
        $productCounter = count($newProducts);
        Product::insert($newProducts);
        $product_ids = auth()->user()->products->pluck('product_id');
        $newMetas = [];
        foreach ($product_ids as $product_id) {
            $newMetas[] = [
                'product_id' => $product_id
            ];
        }
        ProductMeta::insert($newMetas);
        $categories = $user->categories()->with('products')->get();
        $newSites = [];
        $newDomains = [];
        foreach ($groupedSites as $categoryAndProduct => $groupedSite) {
            list($tempCategory, $tempProduct) = explode('$ $', $categoryAndProduct);
            $category = $categories->filter(function ($category) use ($tempCategory, $tempProduct) {
                return $category->category_name == $tempCategory;
            })->first();
            $product = $category->products->filter(function ($product) use ($tempProduct) {
                return $product->product_name == $tempProduct;
            })->first();
            foreach ($groupedSite as $url) {
                $newSites[] = [
                    'site_url' => $url['url'],
                    'product_id' => $product->getKey()
                ];
                $newDomain = parse_url($url['url'])['host'];
                if (!in_array($newDomain, $newDomains)) {
                    $newDomains[] = $newDomain;
                }
            }
        }
        $domains = Domain::all()->pluck('domain_url');
        $newDomains = collect($newDomains);

        $newDomains = $newDomains->diff($domains);


        $insertDomains = [];
        foreach ($newDomains as $newDomain) {
            $insertDomains[] = [
                'domain_url' => $newDomain
            ];
        }

        Domain::insert($insertDomains);
        $domainsNeedPreference = Domain::doesntHave('preference')->get();
        $domain_ids = $domainsNeedPreference->pluck('domain_id');
        $domainPreferences = [];
        foreach ($domain_ids as $domain_id) {
            $domainPreferences[] = [
                'domain_id' => $domain_id,
            ];
        }
        DomainPreference::insert($domainPreferences);

        Site::insert($newSites);

        $siteCounter = count($newSites);

        $categories = $user->categories()->with('sites')->get();
        $sites = $categories->pluck('sites')->flatten();

        $site_ids = $sites->pluck('site_id');

        $newSiteCrawlers = [];
        $newSitePreference = [];

        $domainWithPreferences = Domain::with('preference')->get();

        foreach ($sites as $site) {

            $domain = $domainsNeedPreference->filter(function ($domain) use ($site) {
                return $site->domain == $domain->domain_url;
            })->first();
            if (is_null($domain)) {
                continue;
            }
            $preference = $domain->preference;
            $newSiteCrawlers[] = [
                'site_id' => $site->getKey(),
                'crawler_class' => $domain->crawler_class,
                'parser_class' => $domain->parser_class,
            ];
            $newSitePreference[] = [
                'site_id' => $site->getKey(),
                'xpath_1' => !is_null($preference) ? $preference->xpath_1 : null,
                'xpath_2' => !is_null($preference) ? $preference->xpath_2 : null,
                'xpath_3' => !is_null($preference) ? $preference->xpath_3 : null,
                'xpath_4' => !is_null($preference) ? $preference->xpath_4 : null,
                'xpath_5' => !is_null($preference) ? $preference->xpath_5 : null,
            ];
        }

        SitePreference::insert($newSitePreference);
        Crawler::insert($newSiteCrawlers);


//        $greatestCategoryOrder = $this->categoryRepo->getCategoriesCount();
//        $domains = Domain::all();
//
//        $urls->each(function ($url, $index) use ($domains, &$categories, &$greatestCategoryOrder, $user, &$warnings, &$siteCounter, &$productCounter, &$categoryCounter, $siteLimit) {
//            $rowNumber = $index + 2;
//
////            $category = $user->categories()->where('category_name', $url['category'])->first();
//            $category = $categories->filter(function ($category) use ($url) {
//                return $category->category_name == $url['category'];
//            })->first();
//            if (is_null($category)) {
//                if ($this->request->has('no_new_categories')) {
//                    /*TODO add to warning*/
//                    $warnings[] = "Category in row #{$rowNumber} does not exist in your account, this Category and its Product Page URLs were NOT imported.";
//                    return true;
//                } else {
//                    /*TODO create new category*/
//                    $category = Category::create([
//                        'category_name' => $url['category'],
//                        'category_order' => $greatestCategoryOrder++,
//                        'user_id' => $user->getKey()
//                    ]);
//                    $categories->push($category);
//                    $categoryCounter++;
//                }
//            }
////            $product = $category->products()->where('product_name', '=', $url['product'])->first();
//            $product = $category->products->filter(function ($product) use ($url) {
//                return $product->product_name == $url['product'];
//            })->first();
//            if (is_null($product)) {
//                if ($this->request->has('no_new_products')) {
//                    $warnings[] = "Product in row #{$rowNumber} does not exist in Category:{$category->category_name}, this Product and its URLs were NOT imported.";
//                    return true;
//                } else {
//                    $product = Product::create([
//                        'product_name' => $url['product'],
//                        'product_order' => 99999,
//                        'category_id' => $category->getKey(),
//                        'user_id' => $user->getKey()
//                    ]);
//                    $category->products->push($product);
//                    $productCounter++;
//                }
//            }
//
//
//            if (!is_null($category) && !is_null($product)) {
//
//                /*validating subscription status*/
//                if (!is_null($siteLimit)) {
//                    $currentSiteCount = $product->sites()->count();
//                    if ($currentSiteCount >= $siteLimit) {
//                        $warnings[] = "You have reached the site limit in row #{$rowNumber}. Please upgrade your subscription plan to add more sites.";
//                        return true;
//                    }
//                }
//
//                $site = $product->sites()->save(new Site(array(
//                    "site_url" => $url['url'],
//                )));
//
////                $domainUrl = $site->domain;
//////                $domain = Domain::where('domain_url', '=', $domainUrl)->first();
////                $domain = $domains->filter(function($domain) use($domainUrl){
////                    return $domain->domain_url == $domainUrl;
////                })->first();
////
////                if (!is_null($domain)) {
//////                    $this->siteRepo->adoptDomainPreferences($site->getKey(), $domain->getKey());
////
////                    $preference = $site->preference;
////
////                    $targetPreference = $domain->preference;
////
////                    $preference->xpath_1 = $targetPreference->xpath_1;
////                    $preference->xpath_2 = $targetPreference->xpath_2;
////                    $preference->xpath_3 = $targetPreference->xpath_3;
////                    $preference->xpath_4 = $targetPreference->xpath_4;
////                    $preference->xpath_5 = $targetPreference->xpath_5;
////                    $preference->save();
////
////                    $site->crawler->update(array(
////                        "crawler_class" => $domain->crawler_class,
////                        "parser_class" => $domain->parser_class
////                    ));
////
////
////                    $site->last_crawled_at = null;
////                    $site->comment = null;
////                    $site->save();
////                }
//
//                $siteCounter++;
//            }
//        });
        $status = true;

        event(new AfterStoreSites());

        return compact(['status', 'siteCounter', 'productCounter', 'categoryCounter', 'warnings']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
