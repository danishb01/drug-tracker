<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchDrugRequest;
use App\Http\Resources\DrugSearchResource;
use App\Services\RxNormService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\RateLimiter;

class DrugController extends Controller
{
    public function __construct(
        private RxNormService $rxNormService
    ) {}

    /**
     * Search for drugs using RxNorm API
     * 
     * @param SearchDrugRequest $request
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function search(SearchDrugRequest $request): AnonymousResourceCollection|JsonResponse
    {
        // Rate limiting: 10 requests per minute per IP
        $key = 'drug_search_' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json([
                'message' => 'Too many search requests. Please try again later.',
            ], 429);
        }

        RateLimiter::hit($key, 60); // 1 minute window

        try {
            $drugs = $this->rxNormService->searchDrugs($request->drug_name);
            
            return DrugSearchResource::collection($drugs);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error searching for drugs. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
