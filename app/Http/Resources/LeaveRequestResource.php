<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee' => [
                'id' => $this->employee?->id,
                'name' => $this->employee?->name,
                'email' => $this->employee?->email
            ],
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'total_days' => $this->total_days,
            'reason' => $this->reason,
            'attachment' => $this->attachment ? asset('storage/' . $this->attachment) : null,
            'status' => strtoupper($this->status),
            'approved_at' => $this->approved_at,
            'created_at' => $this->created_at
        ];
    }
}
