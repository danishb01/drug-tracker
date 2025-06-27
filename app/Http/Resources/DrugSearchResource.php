<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DrugSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'rxcui' => $this['rxcui'],
            'drug_name' => $this['drug_name'],
            'base_names' => $this['base_names'] ?? [],
            'dose_form_group_names' => $this['dose_form_group_names'] ?? [],
        ];
    }
}
