<?php

namespace Modules\Admin\Services;

use App\Models\GwApplication;
use App\Enums\ServiceType;
use App\Enums\ApplicationStatus;

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
     * Top cards (Fresh, Amendments, Intimations, Renewals, Revocations)
     */
    private function getSummaryCards(): array
    {
        return [
            'fresh' => $this->getFreshStats(),
            'amendments' => $this->getAmendmentStats(),
            'intimations' => $this->getIntimationStats(),
            'renewals' => $this->getRenewalStats(),
            'revocations' => $this->getRevocationStats(),
        ];
    }

    private function getFreshStats(): array
    {
        return $this->getServiceStats(ServiceType::gwFreshCodes());
    }

    private function getAmendmentStats(): array
    {
        return $this->getServiceStats(ServiceType::gwAmendmentCodes());
    }

    private function getIntimationStats(): array
    {
        return $this->getServiceStats(ServiceType::gwIntimationCodes());
    }

    private function getRenewalStats(): array
    {
        return $this->getServiceStats(ServiceType::gwRenewalCodes());
    }

    private function getRevocationStats(): array
    {
        return $this->getServiceStats(ServiceType::gwRevocationCodes());
    }

    private function getServiceStats(array $serviceTypes): array
    {
        $applications = GwApplication::query()
            ->with('payment')
            ->select(
                'application_id',
                'application_status',
                'payment_status'
            )
            ->where('delete_status', false)
            ->whereHas('services', function ($query) use ($serviceTypes) {
                $query->whereIn('service_type', $serviceTypes);
            })
            ->get();

        return [
            'total' => $applications->count(),

            'approved' => $applications
                ->filter->isApproved()
                ->count(),

            'inProcess' => $applications
                ->filter->isInProcess()
                ->count(),

            'rejected' => $applications
                ->filter->isRejected()
                ->count(),
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