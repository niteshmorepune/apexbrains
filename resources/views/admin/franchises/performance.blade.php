@extends('layouts.admin')
@section('title', 'Franchise Performance Comparison')
@section('page-title', 'Franchise Performance Comparison')

@section('breadcrumb')
    <a href="{{ route('admin.franchises.index') }}" class="text-fran hover:underline">Franchises</a>
    <span class="mx-1 text-gray-400">/</span>
    <span>Performance</span>
@endsection

@section('page-actions')
    <a href="{{ route('admin.franchises.index') }}"
       class="inline-flex items-center gap-2 border border-border text-gray-600 text-sm font-medium px-4 py-2 rounded-xl hover:bg-bg-light transition-colors">
        ← All Franchises
    </a>
    <button onclick="window.print()"
            class="inline-flex items-center gap-2 bg-fran text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-fran-dark transition-colors">
        Export
    </button>
@endsection

@section('content')

<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <table id="franchise-perf-table" class="w-full text-sm">
        <thead>
            <tr class="bg-admin text-white text-xs uppercase tracking-wide">
                <th onclick="sortPerfTable(this,0)" data-sortable data-type="num"  class="px-4 py-3 text-left w-12 cursor-pointer select-none hover:bg-admin-light">Rank <span class="sort-ind opacity-40">↕</span></th>
                <th onclick="sortPerfTable(this,1)" data-sortable data-type="text" class="px-4 py-3 text-left cursor-pointer select-none hover:bg-admin-light">Franchise <span class="sort-ind opacity-40">↕</span></th>
                <th onclick="sortPerfTable(this,2)" data-sortable data-type="text" class="px-4 py-3 text-left cursor-pointer select-none hover:bg-admin-light">City <span class="sort-ind opacity-40">↕</span></th>
                <th onclick="sortPerfTable(this,3)" data-sortable data-type="num"  class="px-4 py-3 text-right cursor-pointer select-none hover:bg-admin-light">Students <span class="sort-ind opacity-40">↕</span></th>
                <th onclick="sortPerfTable(this,4)" data-sortable data-type="num"  class="px-4 py-3 text-right cursor-pointer select-none hover:bg-admin-light">Revenue <span class="sort-ind opacity-40">↕</span></th>
                <th onclick="sortPerfTable(this,5)" data-sortable data-type="num"  class="px-4 py-3 text-center cursor-pointer select-none hover:bg-admin-light">Growth <span class="sort-ind opacity-40">↕</span></th>
                <th onclick="sortPerfTable(this,6)" data-sortable data-type="num"  class="px-4 py-3 text-right cursor-pointer select-none hover:bg-admin-light">Attendance % <span class="sort-ind opacity-40">↕</span></th>
                <th onclick="sortPerfTable(this,7)" data-sortable data-type="num"  class="px-4 py-3 text-right cursor-pointer select-none hover:bg-admin-light">Avg Score <span class="sort-ind opacity-40">↕</span></th>
                <th onclick="sortPerfTable(this,8)" data-sortable data-type="num"  class="px-4 py-3 text-right cursor-pointer select-none hover:bg-admin-light">Pass Rate <span class="sort-ind opacity-40">↕</span></th>
                <th class="px-4 py-3 text-center">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($franchises as $f)
                <tr class="hover:bg-bg-light transition-colors">
                    <td class="px-4 py-3" data-sort="{{ $f->rank }}">
                        @if($f->rank === 1)
                            <span class="text-lg">🥇</span>
                        @elseif($f->rank === 2)
                            <span class="text-lg">🥈</span>
                        @elseif($f->rank === 3)
                            <span class="text-lg">🥉</span>
                        @else
                            <span class="font-semibold text-gray-500">#{{ $f->rank }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3" data-sort="{{ $f->name }}">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-fran-light text-fran font-bold text-xs flex items-center justify-center flex-shrink-0">
                                {{ strtoupper(substr($f->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-admin text-sm">{{ $f->name }}</p>
                                <p class="text-xs text-gray-400">{{ $f->owner_name }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $f->city }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-admin" data-sort="{{ $f->students_count }}">{{ number_format($f->students_count) }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-fran" data-sort="{{ $f->monthly_revenue }}">₹{{ number_format($f->monthly_revenue) }}</td>
                    <td class="px-4 py-3 text-center" data-sort="{{ $f->growth }}">
                        @if($f->growth >= 0)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">
                                ↑ {{ $f->growth }}%
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-red-500 bg-red-50 px-2 py-0.5 rounded-full">
                                ↓ {{ abs($f->growth) }}%
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-gray-700" data-sort="{{ $f->attendance_rate }}">{{ $f->attendance_rate }}%</td>
                    <td class="px-4 py-3 text-right text-gray-700" data-sort="{{ $f->avg_score }}">{{ $f->avg_score }}%</td>
                    <td class="px-4 py-3 text-right text-gray-700" data-sort="{{ $f->pass_rate }}">{{ $f->pass_rate }}%</td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('admin.franchises.show', $f) }}"
                           class="text-xs text-fran font-medium hover:underline">View Details</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="px-4 py-12 text-center text-gray-400">No active franchises found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
function sortPerfTable(th, colIndex) {
    const table = document.getElementById('franchise-perf-table');
    const tbody = table.tBodies[0];
    const rows  = Array.from(tbody.rows).filter(r => !r.querySelector('td[colspan]'));
    if (rows.length < 2) return;

    const type = th.dataset.type || 'text';
    const dir  = th.dataset.dir === 'asc' ? 'desc' : 'asc';

    // reset indicators on all sortable headers
    table.querySelectorAll('th[data-sortable]').forEach(h => {
        h.dataset.dir = '';
        const ind = h.querySelector('.sort-ind');
        if (ind) { ind.textContent = '↕'; ind.classList.add('opacity-40'); }
    });
    th.dataset.dir = dir;
    const ind = th.querySelector('.sort-ind');
    if (ind) { ind.textContent = dir === 'asc' ? '↑' : '↓'; ind.classList.remove('opacity-40'); }

    const cellVal = (row) => {
        const cell = row.cells[colIndex];
        const raw  = cell.dataset.sort !== undefined ? cell.dataset.sort : cell.textContent.trim();
        return type === 'num' ? (parseFloat(raw) || 0) : raw.toLowerCase();
    };

    rows.sort((a, b) => {
        const av = cellVal(a), bv = cellVal(b);
        if (av < bv) return dir === 'asc' ? -1 : 1;
        if (av > bv) return dir === 'asc' ? 1 : -1;
        return 0;
    });

    rows.forEach(r => tbody.appendChild(r));
}
</script>

@endsection
