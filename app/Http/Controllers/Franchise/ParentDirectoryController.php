<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\StudentParent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = StudentParent::with('student.currentLevel')
            ->whereHas('student', fn($q) => $q->where('is_active', true));

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhereHas('student', fn($sq) => $sq->where('first_name', 'like', '%' . $request->search . '%')
                      ->orWhere('last_name', 'like', '%' . $request->search . '%'));
            });
        }

        if ($request->filled('level')) {
            [$min, $max] = explode('-', $request->level . '-99');
            $query->whereHas('student', fn($q) => $q->whereHas('currentLevel', fn($lq) => $lq->whereBetween('number', [(int)$min, (int)$max])));
        }

        $parents = $query->where('is_primary', true)->orderBy('name')->paginate(30)->withQueryString();
        $levels  = Level::orderBy('number')->get();

        return view('franchise.parents.index', compact('parents', 'levels'));
    }
}
