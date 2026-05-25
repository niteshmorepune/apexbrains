@extends('layouts.franchise')
@section('title', 'Dashboard')
@section('content')
<div class="grid grid-cols-2 gap-6">
    <x-kpi-card label="Internal Students" :value="$stats['internal_students']" />
    <x-kpi-card label="External Students" :value="$stats['external_students']" />
</div>
@endsection
