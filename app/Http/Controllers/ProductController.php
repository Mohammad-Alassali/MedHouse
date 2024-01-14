<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductInfoResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use ErrorException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stichoza\GoogleTranslate\GoogleTranslate;
use StillCode\ArPhpLaravel\ArPhpLaravel;

class ProductController extends Controller
{
    /**
     * Returns paginated products with links for the next and previous page
     *
     * @return JsonResponse
     */

    public function filteredProduct(): JsonResponse
    {
        $products = Product::query()
            ->exclude(['description'])
            ->filter(request(['classification', 'company', 'search_word']))
            ->latest()
            ->paginate(5);
        $productsArray = $products->toArray();
        $data = ProductResource::collection($products);
        $details['data'] = $data;
        $details['is_there_next'] = (bool)$productsArray['next_page_url'];
        $details['next_page_url'] = $productsArray['next_page_url'] ?: "null";
        return $this->success($details, $data->count() ? "good" : "There are not products with these information");
    }


    public function latestProduct(): JsonResponse
    {
        $products = Product::query()->latest()->paginate(12);
        return $this->success(ProductResource::collection($products));
    }


    /**
     * Returns paginated products with links for the next and previous page
     * ordered by number of sales
     *
     * @return JsonResponse
     */

    public function bySales(): JsonResponse
    {
        $products = Product::filter(request(['classification', 'company', 'search_word']))
            ->latest('number_of_sales')
            ->paginate(5);
        $productsArray = $products->toArray();
        $data = ProductResource::collection($products);
        $details['data'] = $data;
        $details['is_there_next'] = (bool)$productsArray['next_page_url'];
        $details['next_page_url'] = $productsArray['next_page_url'];
        return $this->success($details);
    }

    /**
     * Create a product
     *
     * @param ProductRequest $request
     * @return JsonResponse
     */

    public function store(ProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $lang = new GoogleTranslate();
        $data['scientific_name'] = $lang->setTarget('en')->setSource('ar')->translate($data['scientific_name']);
        $data['scientific_name_ar'] = $lang->setTarget('ar')->setSource('en')->translate($data['scientific_name']);
        $data['commercial_name'] = $lang->setTarget('en')->setSource('ar')->translate($data['commercial_name']);
        $data['commercial_name_ar'] = $lang->setTarget('ar')->setSource('en')->translate($data['commercial_name']);

        $data['description'] = $lang->setTarget('en')->setSource('ar')->translate($data['description']);
        if ($request->hasFile('photo')) {
            $data['photo'] = ImageController::store($data['photo'], 'Products');
        }
        Product::query()->create($data);
        return $this->success(null);
    }

    /**
     * Update a product with a given new information
     *
     * @param Request $request
     * @param Product $product
     * @return JsonResponse
     */

    public function update(Request $request, Product $product): JsonResponse
    {
        $data = $request->only([
            'scientific_name',
            'commercial_name',
            'description',
            'quantity',
            'price',
            'photo'
        ]);
        $lang = new GoogleTranslate();
        if ($request->hasFile('photo')) {
            $data['photo'] = ImageController::update($data['photo'], $product['photo'], 'Products');

        }
        if ($request->has('scientific_name')) {
            try {
                $data['scientific_name_ar'] = ArPhpLaravel::en2ar($data['scientific_name']);

            } catch (ErrorException) {
                $data['scientific_name_ar'] = $data['scientific_name'];
                $data['scientific_name'] = $lang->setTarget('en')->setSource('ar')->translate($data['scientific_name']);
            }
        }
        if ($request->has('commercial_name')) {
            try {
                $data['commercial_name_ar'] = ArPhpLaravel::en2ar($data['commercial_name']);

            } catch (ErrorException) {
                $data['commercial_name_ar'] = $data['commercial_name'];
                $data['commercial_name'] = $lang->setTarget('en')->setSource('ar')->translate($data['commercial_name']);
            }
        }
        if ($request->has('description')) {
            $data['description'] = $lang->setTarget('en')->setSource('ar')->translate($data['description']);
        }

        $product->update($data);
        return $this->success(null);
    }

    /**
     * Show a product with details
     * with ability to add the product to the user's latest searches
     * if he opens the product in search page
     *
     * @param Product $product
     * @return JsonResponse
     */

    public function show(Product $product): JsonResponse
    {
        if (request()->query('in_search')) {
            auth()->user()->latestSearches()->syncWithoutDetaching($product->id);
        }
        $data['product'] = new ProductInfoResource($product);
        return $this->success($data);
    }

    /**
     * Delete a product
     *
     * @param Product $product
     * @return JsonResponse
     */

    public function destroy(Product $product): JsonResponse
    {
        if ($product->photo) {
            ImageController::destroy($product->photo);
        }
        $product->delete();
        return $this->success(null);
    }

    /**
     * Returns the latest searched products
     *
     * @return JsonResponse
     */

    public function latestSearches(): JsonResponse
    {
        $products = collect(auth()->user()->latestSearches()->latest()->get())
            ->map(fn($product) => new ProductResource($product));
        if ($products->isEmpty()) {
            return $this->success(null, 'There are not products yet');
        }
        return $this->success($products);
    }

    /**
     * Delete the product from user's latest searches
     *
     * @param integer $product
     * @return JsonResponse
     */

    public function destroyLatestSearch(int $product): JsonResponse
    {
        auth()->user()->latestSearches()->detach($product);
        return $this->success(null);
    }

    /**
     * Returns all the favorite products sorted by newest
     *
     * @return JsonResponse
     */

    public function favoriteProduct(): JsonResponse
    {
        $products = collect(auth()->user()->favorites()->latest()->get())
            ->map(fn($product) => new ProductResource($product));
        if ($products->isEmpty()) {
            return $this->success(null, 'There are not products yet');
        }
        return $this->success($products);
    }

    /**
     * Add a product to favorite
     *
     * @param int $product
     * @return JsonResponse
     */

    public function addToFavorite(int $product): JsonResponse
    {
        try {
            auth()->user()->favorites()->syncWithoutDetaching((array)$product);
        } catch (QueryException) {
            return $this->failed('There is not a product with this id');
        }
        return $this->success(null);
    }

    /**
     * remove a product from favorite
     *
     * @param int $product
     * @return JsonResponse
     */

    public function removeFromFavorite(int $product): JsonResponse
    {
        auth()->user()->favorites()->detach($product);
        return $this->success(null);
    }

    public function getOTC(): JsonResponse
    {
        return
            $this->success(
                ProductResource::collection(Product::query()
                    ->where('is_otc', '=', true)
                    ->get()
                )
            );
    }
}
