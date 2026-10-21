<?php

namespace Modules\Admin\Repositories\Others;

use App\Models\ClientQuery;
use App\Models\ClientQueryRemark;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ClientQueryRepository
{
    public function getAll(
        int $limit = 25,
        ?string $search = null,
        ?string $sortColumn = 'created_at',
        ?string $sortDirection = 'desc',
        mixed $status = null
    ): LengthAwarePaginator {
        $query = ClientQuery::with(['division.divisionDescription', 'grievanceSubject', 'remarks']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('contact', 'ilike', "%{$search}%")
                  ->orWhere('details', 'ilike', "%{$search}%")
                  ->orWhere('address', 'ilike', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '' && $status !== 'all') {
            $query->where('status', (int) $status);
        }

        $allowedSorts = ['id', 'name', 'email', 'contact', 'status', 'created_at'];
        $sortColumn = in_array($sortColumn, $allowedSorts) ? $sortColumn : 'id';
        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortColumn, $sortDirection)->paginate($limit);
    }

    public function getStatusCounts(): array
    {
        $rawCounts = DB::table('save_queries')
            ->select('status', DB::raw('count(id) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $total = array_sum($rawCounts);
        $pending = $rawCounts[0] ?? 0;
        $inProgress = $rawCounts[1] ?? 0;
        $resolved = $rawCounts[2] ?? 0;

        return [
            'all' => $total,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'resolved' => $resolved,
        ];
    }

    public function findById($id): ?ClientQuery
    {
        $query = ClientQuery::findByPublicId($id);
        if (!$query && is_numeric($id)) {
            $query = ClientQuery::find($id);
        }

        if ($query) {
            $query->load(['division.divisionDescription', 'grievanceSubject', 'remarks']);
        }

        return $query;
    }

    public function updateStatus($id, int $status, ?string $remark = null): ?ClientQuery
    {
        $query = $this->findById($id);
        if (!$query) {
            return null;
        }

        DB::transaction(function () use ($query, $status, $remark) {
            $query->status = $status;
            $query->save();

            if (!empty(trim((string) $remark))) {
                ClientQueryRemark::create([
                    'query_id' => $query->id,
                    'remark'   => trim($remark),
                    'status'   => $status,
                ]);
            }
        });

        return $this->findById($id);
    }

    public function addRemark($id, string $remark, ?int $status = null): ?ClientQuery
    {
        $query = $this->findById($id);
        if (!$query) {
            return null;
        }

        DB::transaction(function () use ($query, $remark, $status) {
            if ($status !== null) {
                $query->status = $status;
                $query->save();
            }

            ClientQueryRemark::create([
                'query_id' => $query->id,
                'remark'   => trim($remark),
                'status'   => $status ?? $query->status,
            ]);
        });

        return $this->findById($id);
    }

    public function delete($id): bool
    {
        $query = $this->findById($id);
        if (!$query) {
            return false;
        }

        return DB::transaction(function () use ($query) {
            ClientQueryRemark::where('query_id', $query->id)->delete();
            return (bool) $query->delete();
        });
    }
}
