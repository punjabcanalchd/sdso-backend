<?php

namespace Modules\Admin\Services\Others;

use App\Models\ClientQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Repositories\Others\ClientQueryRepository;

class ClientQueryService
{
    protected ClientQueryRepository $repository;

    public function __construct(ClientQueryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getQueries(
        int $limit = 25,
        ?string $search = null,
        ?string $sortColumn = 'created_at',
        ?string $sortDirection = 'desc',
        mixed $status = null
    ): LengthAwarePaginator {
        $paginator = $this->repository->getAll($limit, $search, $sortColumn, $sortDirection, $status);

        $collection = $paginator->getCollection();
        $emails = $collection->pluck('email')->filter()->unique()->toArray();
        $emailLogs = collect();
        if (!empty($emails)) {
            $emailLogs = DB::table('email_logs')
                ->whereIn('email_id', $emails)
                ->where('message', 'like', '%Entity Name%')
                ->orderBy('id', 'desc')
                ->get();
        }

        $divisionIds = $collection->pluck('division_id')->filter()->unique()->toArray();
        $subdivisionsByDivision = [];
        if (!empty($divisionIds)) {
            $subs = DB::table('subdivisions')
                ->join('subdivisions_descriptions', 'subdivisions.subdivision_id', '=', 'subdivisions_descriptions.subdivision_id')
                ->whereIn('subdivisions.division_id', $divisionIds)
                ->where('subdivisions_descriptions.language_id', 1)
                ->get(['subdivisions.division_id', 'subdivisions.subdivision_id', 'subdivisions_descriptions.name']);
            foreach ($subs as $sub) {
                $subdivisionsByDivision[$sub->division_id][] = $sub->name;
            }
        }

        $entityToOfficeMap = [
            'HARIPUR DISTY'          => 'JALANDHAR SUB DIVISION',
            'SIDHWAN BRANCH'         => 'DORAHA SUB DIVISION',
            'PHUL DISTY'             => 'SEHNA SUB DIVISION',
            'JARG MINOR'             => 'AMLOH SUB DIVISION',
            'KARAMGARH DISTY-280'    => 'PANJAWA SUB DIVISION ABOHAR',
        ];

        $paginator->getCollection()->transform(function ($query) use ($emailLogs, $subdivisionsByDivision, $entityToOfficeMap) {
            return $this->formatQuery($query, false, $emailLogs, $subdivisionsByDivision, $entityToOfficeMap);
        });

        return $paginator;
    }

    public function getStatusCounts(): array
    {
        return $this->repository->getStatusCounts();
    }

    public function getQueryById(string|int $id): ?array
    {
        $query = $this->repository->findById($id);
        if (!$query) {
            return null;
        }

        return $this->formatQuery($query, true);
    }

    public function updateStatus(string|int $id, int $status, ?string $remark = null): ?array
    {
        $query = $this->repository->updateStatus($id, $status, $remark);
        if (!$query) {
            return null;
        }

        return $this->formatQuery($query, true);
    }

    public function addRemark(string|int $id, string $remark, ?int $status = null): ?array
    {
        $query = $this->repository->addRemark($id, $remark, $status);
        if (!$query) {
            return null;
        }

        return $this->formatQuery($query, true);
    }

    public function deleteQuery(string|int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function formatQuery(
        ClientQuery $query,
        bool $detailed = false,
        $emailLogs = null,
        array $subdivisionsByDivision = [],
        array $entityToOfficeMap = []
    ): array {
        $divisionName = 'N/A';
        if ($query->division) {
            $divisionDesc = $query->division->divisionDescription ?? $query->division->description->first();
            if ($divisionDesc) {
                $divisionName = $divisionDesc->name;
            }
        }

        $subjectName = 'N/A';
        if ($query->grievanceSubject) {
            $subjectName = $query->grievanceSubject->subject;
        } elseif (!empty($query->subject)) {
            $subjectName = (string) $query->subject;
        }

        // Resolve Entity Name
        $entityName = $query->water_entity_code ? (string) $query->water_entity_code : 'N/A';
        if ($emailLogs) {
            foreach ($emailLogs as $log) {
                if ($log->email_id === $query->email && (!empty($query->name) && str_contains($log->message, $query->name))) {
                    if (preg_match('/Entity Name\s*:\s*([^<\r\n]+)/i', $log->message, $m)) {
                        $entityName = trim($m[1]);
                        break;
                    }
                }
            }
        } elseif (!empty($query->email)) {
            $log = DB::table('email_logs')
                ->where('email_id', $query->email)
                ->where('message', 'like', '%' . $query->name . '%')
                ->where('message', 'like', '%Entity Name%')
                ->orderBy('id', 'desc')
                ->first();
            if ($log && preg_match('/Entity Name\s*:\s*([^<\r\n]+)/i', $log->message, $m)) {
                $entityName = trim($m[1]);
            }
        }

        if (empty($entityToOfficeMap)) {
            $entityToOfficeMap = [
                'HARIPUR DISTY'          => 'JALANDHAR SUB DIVISION',
                'SIDHWAN BRANCH'         => 'DORAHA SUB DIVISION',
                'PHUL DISTY'             => 'SEHNA SUB DIVISION',
                'JARG MINOR'             => 'AMLOH SUB DIVISION',
                'KARAMGARH DISTY-280'    => 'PANJAWA SUB DIVISION ABOHAR',
            ];
        }

        // Resolve Office Name
        $officeName = $entityToOfficeMap[$entityName] ?? null;
        if (!$officeName && $query->division_id) {
            if (isset($subdivisionsByDivision[$query->division_id][0])) {
                $officeName = $subdivisionsByDivision[$query->division_id][0];
            } else {
                $sub = DB::table('subdivisions')
                    ->join('subdivisions_descriptions', 'subdivisions.subdivision_id', '=', 'subdivisions_descriptions.subdivision_id')
                    ->where('subdivisions.division_id', $query->division_id)
                    ->where('subdivisions_descriptions.language_id', 1)
                    ->first();
                $officeName = $sub ? $sub->name : 'N/A';
            }
        }
        $officeName = $officeName ?? 'N/A';

        $statusMap = [
            0 => ['label' => 'Pending', 'class' => 'warning'],
            1 => ['label' => 'In Progress', 'class' => 'info'],
            2 => ['label' => 'Resolved', 'class' => 'success'],
        ];

        $statusInfo = $statusMap[$query->status] ?? ['label' => 'Unknown', 'class' => 'secondary'];

        $pdfUrl = null;
        if (!empty($query->upload_pdf)) {
            $pdfUrl = asset('storage/' . ltrim($query->upload_pdf, '/'));
        }

        $data = [
            'id'                  => $query->id,
            'public_id'           => $query->public_id,
            'name'                => $query->name ?? 'N/A',
            'email'               => $query->email ?? 'N/A',
            'contact'             => $query->contact ?? 'N/A',
            'subject'             => $subjectName,
            'subject_code'        => $query->subject_code,
            'details'             => $query->details ?? '',
            'address'             => $query->address ?? 'N/A',
            'division_id'         => $query->division_id,
            'division_name'       => $divisionName,
            'entity_name'         => $entityName,
            'office_name'         => $officeName,
            'division_stacked'    => [
                'division' => $divisionName,
                'entity'   => $entityName,
                'office'   => $officeName,
            ],
            'water_entity_code'   => $query->water_entity_code ?? 'N/A',
            'upload_pdf'          => $query->upload_pdf,
            'pdf_url'             => $pdfUrl,
            'has_attachment'      => !empty($query->upload_pdf),
            'status'              => $query->status,
            'status_label'        => $statusInfo['label'],
            'status_class'        => $statusInfo['class'],
            'created_at'          => $query->created_at ? $query->created_at->format('Y-m-d H:i:s') : null,
            'created_at_display'  => $query->created_at ? $query->created_at->format('Y-m-d H:i:s') : '',
            'remarks_count'       => $query->remarks ? $query->remarks->count() : 0,
        ];

        if ($detailed) {
            $data['remarks'] = ($query->remarks ?? collect())->map(function ($remark) use ($statusMap) {
                $remStatusInfo = $statusMap[$remark->status] ?? ['label' => 'Unknown', 'class' => 'secondary'];
                return [
                    'id'                 => $remark->id,
                    'remark'             => $remark->remark,
                    'status'             => $remark->status,
                    'status_label'       => $remStatusInfo['label'],
                    'status_class'       => $remStatusInfo['class'],
                    'created_at'         => $remark->created_at ? $remark->created_at->format('Y-m-d H:i:s') : null,
                    'created_at_display' => $remark->created_at ? $remark->created_at->format('d-m-Y H:i') : '',
                ];
            })->all();
        }

        return $data;
    }
}
