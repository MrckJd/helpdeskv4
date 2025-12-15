<?php

namespace App\Jobs;

use App\Models\Barangay;
use App\Models\Municipality;
use App\Services\PSGCApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class PSGCSync implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {

        $execute = function () {
            try{
                $municipalities = PSGCApiService::getMunicipalities();



                if(empty($municipalities)) {
                    return;
                }

                foreach ($municipalities as $municipality) {
                    if(Municipality::where('code', $municipality['code'])->exists()) {
                        continue;
                    }
                    $municipality_id = Municipality::create([
                        'code' => $municipality['code'],
                        'name' => $municipality['name'],
                        'oldName' => $municipality['oldName'] ?? null,
                        'isCapital' => $municipality['isCapital'] ?? false,
                        'isCity' => $municipality['isCity'] ?? false,
                        'isMunicipality' => $municipality['isMunicipality'] ?? false,
                        'districtCode' => $municipality['districtCode'] ?? null,
                        'provinceCode' => $municipality['provinceCode'] ?? null,
                        'regionCode' => $municipality['regionCode'] ?? null,
                        'islandGroupCode' => $municipality['islandGroupCode'] ?? null,
                    ]);

                    $barangays = PSGCApiService::getBarangays($municipality['code']);
                    if(Barangay::where('municipalityCode', $municipality['code'])->exists()) {
                        continue;
                    }
                    if(!empty($barangays)) {
                        foreach ($barangays as $barangay) {
                            $municipality_id->barangays()->create([
                                'code' => $barangay['code'],
                                'name' => $barangay['name'],
                                'oldName' => $barangay['oldName'] ?? null,
                                'isUrban' => $barangay['isUrban'] ?? false,
                                'municipalityCode' => $barangay['municipalityCode'] ?? null,
                                'provinceCode' => $barangay['provinceCode'] ?? null,
                                'regionCode' => $barangay['regionCode'] ?? null,
                                'islandGroupCode' => $barangay['islandGroupCode'] ?? null,
                            ]);

                        }
                    }
                }
            }catch (\Exception $e) {
                dd($e->getMessage());
            }
        };
        $execute();
    }
}
