<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\District;

class DistrictController extends Controller
{
    /**
     * Get all districts with state_code 3
     */
    public function index()
    {
        $districts = District::where('state_code', 3)
            ->select([
                'districts_id',
                'state_code',
                'district_code',
                'district_english',
                'district_punjabi',
                'status'
            ])
            ->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Districts retrieved successfully.',
            'data' => $districts
        ], 200);
    }
}
