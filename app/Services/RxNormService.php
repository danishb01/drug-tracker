<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Response;

class RxNormService
{
    private const BASE_URL = 'https://rxnav.nlm.nih.gov/REST';
    private const CACHE_TTL = 600; // 10 minutes

    /**
     * Search for drugs using RxNorm getDrugs endpoint
     * 
     * @param string $drugName
     * @return array
     */
    public function searchDrugs(string $drugName, int $limit = 5): array
    {
        $cacheKey = "drug_search_{$drugName}";
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($drugName, $limit) {
            $response = Http::get(self::BASE_URL . '/drugs.json', [
                'name' => $drugName,
                'allsrc' => 1,
                'tty' => 'SBD'
            ]);

            if (!$response->successful()) {
                \Log::error('RxNorm searchDrugs API failed', ['status' => $response->status(), 'body' => $response->body()]);
                return [];
            }

            $data = $response->json();
            
            // Debug the response structure
            \Log::info('RxNorm searchDrugs API response', ['data' => $data]);
            
            $drugs = $data['drugGroup']['conceptGroup'] ?? [];
            
            $results = [];
            $count = 0;
            
            foreach ($drugs as $group) {
                if (isset($group['conceptProperties'])) {
                    foreach ($group['conceptProperties'] as $concept) {
                        if ($count >= $limit) break;
                        
                        $drugInfo = $this->getDrugDetails($concept['rxcui']);
                        if ($drugInfo) {
                            $results[] = $drugInfo;
                            $count++;
                        }
                    }
                }
            }
            
            return $results;
        });
    }

    /**
     * Get detailed drug information including base names and dose forms
     * 
     * @param string $rxcui
     * @return array|null
     */
    public function getDrugDetails(string $rxcui): ?array
    {
        $cacheKey = "drug_details_{$rxcui}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($rxcui) {
            $response = Http::get(self::BASE_URL . "/rxcui/{$rxcui}/historystatus.json");
            
            if (!$response->successful()) {
                \Log::error('RxNorm getDrugDetails API failed', ['status' => $response->status(), 'body' => $response->body(), 'rxcui' => $rxcui]);
                return null;
            }

            $data = $response->json();

            // Debug the response structure
            \Log::info('RxNorm getDrugDetails API response', ['data' => $data]);

            $drug_name = $data['rxcuiStatusHistory']['attributes']['name'];
            $base_names = collect($data['rxcuiStatusHistory']['definitionalFeatures']['ingredientAndStrength'] ?? [])
            ->pluck('baseName')->unique()->values();
            $dose_form_group_names = collect($data['rxcuiStatusHistory']['definitionalFeatures']['doseFormGroupConcept'] ?? [])
            ->pluck('doseFormGroupName')->unique()->values();

            return [
                'rxcui' => $rxcui,
                'drug_name' => $drug_name,
                'base_names' => $base_names,
                'dose_form_group_names' => $dose_form_group_names
            ];

        });
    }

    /**
     * Validate if an rxcui exists
     * 
     * @param string $rxcui
     * @return bool
     */
    public function validateRxcui(string $rxcui): bool
    {
        $cacheKey = "rxcui_validation_{$rxcui}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($rxcui) {
            $response = Http::get(self::BASE_URL . "/rxcui/{$rxcui}.json");
            return $response->successful();
        });
    }
} 