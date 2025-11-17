@extends('layouts.admin')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Detail Agenda</h4>
    <a href="{{ route('admin.agendas.index') }}" class="btn btn-light">Kembali</a>
</div>
<div class="dashboard-card">
    <h5>{{ $item['title'] ?? '-' }}</h5>
    <div class="text-muted small mb-2">Tanggal: {{ $item['date'] ?? '-' }} • Tempat: {{ $item['place'] ?? '-' }}</div>
    @if(!empty($item['poster_url']))
        <div class="mb-3"><img src="{{ $item['poster_url'] }}" alt="Poster" style="max-width:280px;border-radius:8px"></div>
    @endif
    <p>{{ $item['description'] ?? '-' }}</p>
</div>
@endsection
