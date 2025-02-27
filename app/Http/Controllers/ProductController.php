<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function search($query)
{
    try {
        Log::info("Search Query: " . $query);

        $response = Http::get("https://svcs.ebay.com/services/search/FindingService/v1", [
            'OPERATION-NAME' => 'findItemsByKeywords',
            'SERVICE-VERSION' => '1.0.0',
            'SECURITY-APPNAME' => env('EBAY_APP_ID'), // Your eBay App ID
            'RESPONSE-DATA-FORMAT' => 'JSON',
            'keywords' => $query,
            'paginationInput.entriesPerPage' => 5
        ]);

        if ($response->failed()) {
            Log::error("eBay API Request Failed!", $response->json());
            return response()->json(["error" => "eBay API request failed"], 500);
        }

        $data = $response->json();
        Log::info("eBay API Response:", $data);

        if (!isset($data['findItemsByKeywordsResponse'][0]['searchResult'][0]['item'])) {
            return response()->json(["error" => "No items found"], 404);
        }

        $items = $data['findItemsByKeywordsResponse'][0]['searchResult'][0]['item'];
        $prices = [];

        foreach ($items as $item) {
            $prices[] = [
                "title" => $item['title'][0],
                "price" => $item['sellingStatus'][0]['currentPrice'][0]['__value__'] . " " . $item['sellingStatus'][0]['currentPrice'][0]['@currencyId'],
                "url" => $item['viewItemURL'][0] // ✅ Adding the product link
            ];
        }

        return response()->json($prices);

    } catch (\Exception $e) {
        Log::error("Search Error: " . $e->getMessage());
        return response()->json(["error" => "Internal Server Error"], 500);
    }
}

}
