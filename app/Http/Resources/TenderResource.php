<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_name' => $this->project_name,
            'validity_start' => $this->validity_start,
            'validity_end' => $this->validity_end,
            'company' => $this->company,
            'project_title' => $this->project_title,
            'location' => $this->location,
            'customer' => $this->customer,
            'prepared_by' => $this->prepared_by,
            'date' => $this->date,
            'conditions' => $this->conditions,
            'boq' => $this->boq,
            'logo' => $this->logo,
            'documents' => json_decode($this->documents, true), // decode JSON string to array
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

    }
}
