<?php

namespace App\Services;

use App\Models\Declaration;
use Illuminate\Support\Facades\Auth;

class DeclarationService extends Service
{
    public function store(array $data)
    {
        $data['created_by'] = Auth::check() ? Auth::user()->id : null;
        $data['total'] = $this->parseTotal($data['total']);

        return Declaration::create($data);
    }

    public function update(array $data, mixed $declaration)
    {
        if (key_exists('total', $data)) {
            $data['total'] = $this->parseTotal($data['total']);
        }

        $declaration->update($data);
        $declaration->refresh();

        return $declaration;
    }

    public function delete(mixed $declaration)
    {
        $declaration->delete();
    }

    public function get(mixed $declaration)
    {
        $declaration->load([
            'placement',
            'createdBy',
        ]);

        return $declaration;
    }

    public function list(int $perPage = 25, string $query = null)
    {
    }

    public function listAgencyDeclarations(int $perPage = 25, string $searchQuery = null, mixed $agency)
    {
        $agency_id = $agency->id;

        $query = Declaration::with('placement', 'createdBy')
            ->join('placements', 'declarations.placement_id', '=', 'placements.id')
            ->whereHas('placement.employee', function ($query) use ($agency_id) {
                $query->where('agency_id', $agency_id);
            })
            ->orderBy('placements.start_at', 'DESC')
            ->select('declarations.*');  // avoid getting columns from joined table

        if (!is_null($searchQuery)) {
            // apply search query if needed
            $query->where(function ($query) use ($searchQuery) {
                $query->where('title', 'like', '%'.$searchQuery.'%');
            });
        }

        return $query->paginate($perPage);
    }

    private function parseTotal(int $total)
    {
        if ($total > 0) {
            // Parse total from cents to decimal
            return round(($total / 100), 2);
        }

        return $total;
    }
}
