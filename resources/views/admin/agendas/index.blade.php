@extends('layouts.admin')
@section('content')
<section class="section-fullscreen mb-4 section-alt py-3">
  <div class="container section-soft accented decor-gradient-top">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="text-center w-100">
            <h2 class="vm-title-center mb-1">Kelola Agenda</h2>
            <div class="vm-subtitle">Atur jadwal kegiatan sekolah</div>
        </div>
        <div class="ms-3 d-none d-md-block">
            <a href="{{ route('admin.agendas.create') }}" class="btn btn-primary">+ Buat Agenda</a>
        </div>
    </div>
  </div>
</section>

<div class="card-elevated p-3">
    @if(session('status'))
      <div class="alert alert-success mb-3">{{ session('status') }}</div>
    @endif

    <div class="row g-3">
        @forelse(($items ?? []) as $i => $it)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 shadow-animate">
                <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="mb-0 text-truncate" title="{{ $it->title ?? '-' }}">{{ $it->title ?? '-' }}</h6>
                        <span class="badge bg-primary-subtle text-primary">{{ $it->date ?? '-' }}</span>
                    </div>
                    <div class="text-muted small">{{ $it->place ?? '' }}</div>
                    <div class="d-flex gap-2 mt-2">
                        <a href="{{ route('admin.agendas.show', $it->id) }}" class="btn btn-sm btn-outline-primary flex-fill"><i class="ri-eye-line me-1"></i> Detail</a>
                        <a href="{{ route('admin.agendas.edit', $it->id) }}" class="btn btn-sm btn-outline-warning flex-fill"><i class="ri-edit-line me-1"></i> Edit</a>
                        <form action="{{ route('admin.agendas.destroy', $it->id) }}" method="POST" onsubmit="return confirm('Hapus agenda ini?')" class="m-0 p-0">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="ri-delete-bin-line me-1"></i> Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center text-muted py-4">Belum ada agenda.</div>
        @endforelse
    </div>
</div>
<div class="mt-3 d-md-none">
    <a href="{{ route('admin.agendas.create') }}" class="btn btn-primary w-100">+ Buat Agenda</a>
</div>
@endsection
