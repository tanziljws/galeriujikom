@extends('layouts.app')

@section('title', ($agenda->title ?? 'Detail Agenda') . ' - SMKN 4 BOGOR')

@push('styles')
<style>
    /* Hero untuk halaman agenda - lebih kecil dan pill shape */
    .agenda-hero { background: linear-gradient(135deg, #E8F1F8 0%, #D4E4F1 100%); color: #1E3A8A; padding: 1.5rem 0 1.25rem; position: relative; overflow: hidden; border-radius: 0 0 50px 50px; }
    .agenda-hero:after { content:""; position:absolute; right:-60px; top:-60px; width:260px; height:260px; border-radius:50%; background: rgba(30,58,138,.05); filter: blur(2px); }
    .agenda-crumb { color: #1E3A8A; text-decoration:none; font-weight:600; display:inline-flex; align-items:center; gap:.4rem; font-size: 0.9rem; }
    .agenda-crumb:hover { text-decoration: underline; color: #2563EB; }
    .agenda-title { font-size: 1.75rem; font-weight: 700; line-height: 1.3; max-width: 900px; color: #1E3A8A; }
    .meta-chips { display:flex; gap:.5rem; flex-wrap:wrap; margin-top:.5rem; }
    .meta-chips .chip { background: rgba(30,58,138,.1); color: #1E3A8A; border:1px solid rgba(30,58,138,.2); font-size: 0.85rem; padding: 0.25rem 0.75rem; border-radius: 20px; }
    .content-card { background: var(--white); border-radius:16px; border:1px solid rgba(31,78,121,.12); box-shadow:0 12px 24px rgba(0,0,0,.06); padding:1.5rem; }
    .content-card p { margin-bottom: .85rem; line-height:1.8; color: var(--dark-gray); }
    .content-card h2, .content-card h3, .content-card h4 { color: var(--primary-blue); margin-top:1rem; font-weight:800; }
</style>
@endpush

@section('content')
    <!-- Hero -->
    <div class="agenda-hero">
        <div class="container">
            <a href="{{ route('agenda') }}" class="agenda-crumb mb-2">
                <i class="fas fa-arrow-left"></i> Kembali ke Agenda
            </a>
            <h1 class="agenda-title">{{ $agenda->title }}</h1>
            <div class="meta-chips">
                @if($agenda->date)
                    @php
                        \Carbon\Carbon::setLocale('id');
                        $dateFormatted = \Carbon\Carbon::parse($agenda->date)->translatedFormat('d F Y');
                    @endphp
                    <span class="chip"><i class="fas fa-calendar"></i> {{ $dateFormatted }}</span>
                @endif
                @if($agenda->place)
                    <span class="chip"><i class="fas fa-map-marker-alt"></i> {{ $agenda->place }}</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Detail Content -->
    <section class="py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <div class="content-card">
                        <h5 class="fw-semibold mb-3">Deskripsi Kegiatan</h5>
                        <p class="text-secondary fw-semibold">{{ $agenda->description ?? 'Tidak ada deskripsi.' }}</p>
                        
                        @if($agenda->date || $agenda->place)
                            <div class="border-top pt-3 mt-4">
                                <h6 class="fw-semibold mb-2">Detail Agenda</h6>
                                <div class="row text-secondary small fw-semibold">
                                    @if($agenda->date)
                                        <div class="col-md-6 mb-2">
                                            <i class="ri-calendar-line me-1"></i> 
                                            <strong>Tanggal:</strong> 
                                            @php
                                                \Carbon\Carbon::setLocale('id');
                                                echo \Carbon\Carbon::parse($agenda->date)->translatedFormat('l, d F Y');
                                            @endphp
                                        </div>
                                    @endif
                                    @if($agenda->place)
                                        <div class="col-md-6 mb-2">
                                            <i class="ri-map-pin-line me-1"></i> 
                                            <strong>Tempat:</strong> {{ $agenda->place }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
