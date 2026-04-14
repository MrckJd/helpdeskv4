<?php

namespace App\Filament\Clusters\Feedbacks\Widgets;

use App\Models\Feedback;

class CustomerTypeWidget extends GenderChart
{
    protected static ?string $heading = 'Customer Type';

    protected function getData(): array
    {
        $query = Feedback::query();

        if ($this->selectedOrganizationId) {
            $query->where('organization_id', $this->selectedOrganizationId);
        }

        $data = match($this->filter) {
            'internal' => $query->whereHas('category', function($q) {
                $q->where('service_type', 'internal');
            }),
            'external' => $query->whereHas('category', function ($q) {
                $q->where('service_type', 'external');
            }),
            default => $query,
        };

        $data = $query
            ->selectRaw('client_type, COUNT(*) as count')
            ->groupBy('client_type')
            ->pluck('count', 'client_type')
            ->toArray();
        // dd($data);
        return [
            'datasets' => [
                [
                    'data' => array_values($data),
                    'backgroundColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                    ],
                ],
            ],
            'labels'=> ['Citizen', 'Business', 'Government'],
        ];
    }
}
