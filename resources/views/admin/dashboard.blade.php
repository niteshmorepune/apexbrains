@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<div class="grid grid-cols-3 gap-6">
    <x-kpi-card label="Internal Students" :value="$stats['total_internal_students']" />
    <x-kpi-card label="External Students" :value="$stats['total_external_students']" />
    <x-kpi-card label="Active Franchises" :value="$stats['active_franchises']" />
</div>
@endsection
