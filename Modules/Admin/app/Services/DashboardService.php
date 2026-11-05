<?php

namespace Modules\Admin\Services;

use App\Models\DamHeadwork;
use App\Models\DamDailyReading;
use App\Models\User;
use App\Models\NoticeboardTemplate;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Main dashboard response
     */
    public function adminDashboard(): array
    {
        return [
            'success' => true,
            'status' => 200,
            'message' => 'Dashboard data fetched successfully.',
            'data' => [
                'summary' => $this->getSummaryCards(),
                'applicationStatus' => $this->getApplicationStatus(),
                'applicationsByUnitType' => $this->getApplicationsByUnitType(),
                'applicationsByArea' => $this->getApplicationsByArea(),
                'groundWaterStatus' => $this->getGroundWaterStatus(),
                'officerPendencies' => $this->getOfficerPendencies(),
                'telemetryStatus' => $this->getTelemetryStatus(),
            ]
        ];
    }

    /**
     * Top cards
     */
    private function getSummaryCards(): array
    {
        $totalHeadworks = DamHeadwork::count();
        $totalReadings = DamDailyReading::count();
        $todayReadings = DamDailyReading::whereDate('created_at', Carbon::today())->count();
        $totalUsers = User::count();

        return [
            'headworks' => [
                'total' => $totalHeadworks,
                'label' => 'Total Headworks & Dams'
            ],
            'readings' => [
                'total' => $totalReadings,
                'today' => $todayReadings,
                'label' => 'Daily Readings'
            ],
            'users' => [
                'total' => $totalUsers,
                'label' => 'Active System Users'
            ],
            'fresh' => [
                'total' => $totalHeadworks,
                'approved' => $totalHeadworks,
                'inProcess' => 0,
                'rejected' => 0
            ],
            'amendments' => ['total' => 0, 'approved' => 0, 'inProcess' => 0, 'rejected' => 0],
            'intimations' => ['total' => 0, 'approved' => 0, 'inProcess' => 0, 'rejected' => 0],
            'renewals' => ['total' => 0, 'approved' => 0, 'inProcess' => 0, 'rejected' => 0],
            'revocations' => ['total' => 0, 'approved' => 0, 'inProcess' => 0, 'rejected' => 0],
        ];
    }

    /**
     * Monthly Application Status chart
     */
    private function getApplicationStatus(): array
    {
        return [
            'months' => ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
            'submitted' => [44,55,57,58,61,58,63,60,66,0,0,0],
            'pending'   => [76,85,101,98,87,105,91,114,94,0,0,0],
            'approved'  => [35,41,36,26,45,48,52,53,41,0,0,0],
            'rejected'  => [0,0,0,0,0,0,0,0,0,0,0,0],
        ];
    }

    /**
     * Applications by Unit Type (bar chart)
     */
    private function getApplicationsByUnitType(): array
    {
        return [
            'labels' => ['Commercial', 'Industrial', 'Residential/Private', 'Municipal', 'Government', 'Others'],
            'values' => [620, 430, 350, 210, 180, 130],
        ];
    }

    /**
     * Applications by Area (donut chart)
     */
    private function getApplicationsByArea(): array
    {
        return [
            'labels' => ['Yellow', 'Orange', 'Green'],
            'values' => [20, 48, 32],
        ];
    }

    /**
     * Ground Water Extraction status
     */
    private function getGroundWaterStatus(): array
    {
        return [
            'months' => ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
            'approved' => [20,40,30,26,45,48,52,53,41,0,0,0],
            'pending'  => [76,85,101,98,87,105,91,114,94,0,0,0],
            'rejected' => [35,41,36,26,45,48,52,53,41,0,0,0],
        ];
    }

    /**
     * Officer-wise pendencies chart
     */
    private function getOfficerPendencies(): array
    {
        return [
            'officers' => ['Officer A','Officer B','Officer C','Officer D','Officer E','Officer F'],
            'groundwater' => [20,40,30,15,10,13],
            'drillingRig' => [6,30,5,0,8,10],
            'waterTankers' => [20,12,5,11,9,13],
        ];
    }

    /**
     * Telemetry meter status
     */
    private function getTelemetryStatus(): array
    {
        return [
            'mappedToCloud' => 96,
            'notMapped' => 4,
        ];
    }
}