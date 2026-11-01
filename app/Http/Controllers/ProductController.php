<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{

    private string $filePath = 'products.json';

    private function readProducts(): array
    {
        if (!Storage::exists($this->filePath)) {
            Storage::put($this->filePath, json_encode([], JSON_PRETTY_PRINT));
            return [];
        }

        $contents = Storage::get($this->filePath);
        $decoded  = json_decode($contents, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function writeProducts(array $products): void
    {
        Storage::put($this->filePath, json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }


    public function index()
    {
        $products = $this->readProducts();

        usort($products, fn($a, $b) => strcmp($a['datetime_submitted'], $b['datetime_submitted']));

        return view('products', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name'      => 'required|string|max:255',
            'quantity_in_stock' => 'required|integer|min:0',
            'price_per_item'    => 'required|numeric|min:0',
        ]);

        $products = $this->readProducts();

        $newProduct = [
            'id'                => uniqid('prod_', true),
            'product_name'      => trim($validated['product_name']),
            'quantity_in_stock' => (int) $validated['quantity_in_stock'],
            'price_per_item'    => (float) $validated['price_per_item'],
            'datetime_submitted'=> now()->format('Y-m-d H:i:s'),
            'total_value'       => (int) $validated['quantity_in_stock'] * (float) $validated['price_per_item'],
        ];

        $products[] = $newProduct;
        $this->writeProducts($products);

        // Return all products sorted so the front-end can re-render cleanly
        usort($products, fn($a, $b) => strcmp($a['datetime_submitted'], $b['datetime_submitted']));

        return response()->json([
            'success'  => true,
            'message'  => 'Product added successfully.',
            'product'  => $newProduct,
            'products' => $products,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'product_name'      => 'required|string|max:255',
            'quantity_in_stock' => 'required|integer|min:0',
            'price_per_item'    => 'required|numeric|min:0',
        ]);

        $products = $this->readProducts();
        $found    = false;

        foreach ($products as &$product) {
            if ($product['id'] === $id) {
                $product['product_name']      = trim($validated['product_name']);
                $product['quantity_in_stock'] = (int) $validated['quantity_in_stock'];
                $product['price_per_item']    = (float) $validated['price_per_item'];
                $product['total_value']       = (int) $validated['quantity_in_stock'] * (float) $validated['price_per_item'];
                $found = true;
                break;
            }
        }
        unset($product);

        if (!$found) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        $this->writeProducts($products);

        usort($products, fn($a, $b) => strcmp($a['datetime_submitted'], $b['datetime_submitted']));

        return response()->json([
            'success'  => true,
            'message'  => 'Product updated successfully.',
            'products' => $products,
        ]);
    }

    public function list()
    {
        $products = $this->readProducts();
        usort($products, fn($a, $b) => strcmp($a['datetime_submitted'], $b['datetime_submitted']));

        return response()->json([
            'success'  => true,
            'products' => $products,
        ]);
    }
}
