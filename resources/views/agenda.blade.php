@extends('layouts.app')

@section('title', 'Agenda - SMKN 4 BOGOR')

@section('content')
    <!-- Hero Header (match Gallery style) -->
    <section class="section-fullscreen mb-4 section-alt py-3">
        <div class="container section-soft accented decor-gradient-top py-4 py-md-5">
            <div class="text-center mb-2">
                <h1 class="vm-title-center">Agenda Sekolah</h1>
                <p class="vm-subtitle mb-0">Jadwal kegiatan dan agenda penting SMKN 4 Bogor</p>
            </div>
        </div>
    </section>

    <!-- Grid Section -->
    <section class="py-4">
        <div class="container">
            @if(!empty($items))
                <div class="row g-3">
                    @foreach($items as $i => $it)
                        @php
                            $dateStr = $it['date'] ?? null;
                            try { 
                                \Carbon\Carbon::setLocale('id');
                                $dateLabel = $dateStr ? \Carbon\Carbon::parse($dateStr)->translatedFormat('d F Y') : null; 
                            }
                            catch (\Exception $e) { $dateLabel = $dateStr; }
                            
                            // Array warna cerah untuk badge tanggal
                            $colors = [
                                ['bg' => '#E0F2FE', 'text' => '#0369A1'], // Sky blue
                                ['bg' => '#DBEAFE', 'text' => '#1E40AF'], // Blue
                                ['bg' => '#E0E7FF', 'text' => '#4338CA'], // Indigo
                                ['bg' => '#EDE9FE', 'text' => '#6D28D9'], // Violet
                                ['bg' => '#FCE7F3', 'text' => '#BE185D'], // Pink
                                ['bg' => '#FEE2E2', 'text' => '#B91C1C'], // Red
                                ['bg' => '#FFEDD5', 'text' => '#C2410C'], // Orange
                                ['bg' => '#FEF3C7', 'text' => '#B45309'], // Amber
                            ];
                            $colorIndex = $i % count($colors);
                            $badgeColor = $colors[$colorIndex];
                        @endphp
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm border-0 rounded-4 hoverable">
                                @if(!empty($it['poster_url']))
                                    <div class="ratio ratio-16x9">
                                        <img src="{{ $it['poster_url'] }}" alt="{{ $it['title'] ?? '' }}" style="object-fit:cover; border-radius: 1rem 1rem 0 0;">
                                    </div>
                                @endif
                                <div class="card-body p-3">
                                    <div class="mb-2">
                                        <span class="badge fw-semibold" style="background-color: {{ $badgeColor['bg'] }}; color: {{ $badgeColor['text'] }};">{{ $dateLabel ?? '-' }}</span>
                                    </div>
                                    <h3 class="h6 fw-bold mb-2" style="color:#1E3A8A;">{{ $it['title'] ?? 'Tanpa Judul' }}</h3>
                                    @if(!empty($it['place']))
                                        <div class="small text-secondary mb-2 d-flex align-items-center gap-1 fw-semibold">
                                            <i class="ri-map-pin-line"></i>
                                            <span>{{ $it['place'] }}</span>
                                        </div>
                                    @endif
                                    @if(!empty($it['description']))
                                        <p class="text-secondary small mb-3 fw-semibold" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $it['description'] }}</p>
                                    @endif
                                    <a href="{{ route('agenda.show', $it['id']) }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold w-100">Baca Selengkapnya</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
            @else
                <div class="text-center py-5">
                    <div class="d-inline-block p-3 rounded-circle" style="background:#E2E8F0; box-shadow: inset 0 0 0 2px #cbd5e1;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="16" rx="2" stroke="#1E3A8A" stroke-width="1.5"/>
                            <path d="M3 9H21" stroke="#1E3A8A" stroke-width="1.5"/>
                            <path d="M8 3V7" stroke="#1E3A8A" stroke-width="1.5"/>
                            <path d="M16 3V7" stroke="#1E3A8A" stroke-width="1.5"/>
                        </svg>
                    </div>
                    <h2 class="h5 mt-3 mb-1" style="color:#1E3A8A;">Belum ada agenda</h2>
                    <p class="text-secondary mb-0">Agenda akan ditampilkan di sini setelah ditambahkan oleh admin.</p>
                </div>
            @endif
        </div>
    </section>
    @push('styles')
    <style>
        :root{
            --brand-navy: #1E3A8A; /* biru keabuan gelap */
            --brand-surface: #E2E8F0; /* abu kebiruan terang */
            --brand-soft-blue: #93C5FD; /* biru muda untuk badge */
        }
        /* Base card hover */
        .hoverable{ transition: transform .25s ease, box-shadow .25s ease; }
        .hoverable:hover{ transform: translateY(-2px); box-shadow: 0 16px 32px rgba(30,58,138,.18)!important; }

        /* Timeline layout */
        .timeline-vertical{ padding-left: 0; }
        .timeline-line{ position: absolute; top: 0; bottom: 0; left: 8px; width: 2px; background: linear-gradient(180deg, rgba(30,58,138,.2), rgba(30,58,138,.08)); box-shadow: 0 0 0 1px rgba(30,58,138,.05); }
        .timeline-item{ position: relative; display: flex; align-items: flex-start; gap: .35rem; margin-bottom: .6rem; }
        .timeline-marker{ position: relative; z-index: 2; margin-left: 0; margin-right: 0; margin-top: .2rem; width: 22px; height: 22px; border-radius: 50%; background: #ffffff; border: 2px solid var(--brand-surface); display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 8px rgba(30,58,138,.12); }
        /* Let card take the remaining horizontal space */
        .timeline-item .agenda-card{ flex: 1 1 0%; min-width: 0; }

        /* Agenda card look */
        .agenda-card{ position: relative; background: #ffffff; border: 1px solid rgba(30,58,138,.06); box-shadow: 0 8px 24px rgba(2, 6, 23, 0.05); }
        /* connector to the timeline */
        .agenda-card::before{ content:""; position:absolute; left:-6px; top:16px; width:6px; height:6px; border-radius:50%; background:#1E3A8A; box-shadow: 0 0 0 3px #fff; }
        .agenda-card::after{ content:""; position:absolute; left:-16px; top:18px; width:10px; height:2px; background: rgba(30,58,138,.25); border-radius:1px; }

        /* Desktop: nudge card closer to the line */
        @media (min-width: 768px){
            .timeline-item .agenda-card{ margin-left: -2px; }
            .timeline-item .agenda-card::before{ left:-8px; }
            .timeline-item .agenda-card::after{ left:-18px; }
        }
        .badge-date{ background: rgba(147,197,253,.25); color: var(--brand-navy); border: 1px solid rgba(30,58,138,.20); padding: .35rem .65rem; border-radius: 999px; font-weight: 600; font-size: .85rem; letter-spacing: .2px; }
        .clamp-2{ display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .btn-ghost{ background: transparent; border: 1px solid #1E3A8A; color: #1E3A8A; }
        .btn-ghost:hover{ background: rgba(30,58,138,.06); color: #1E3A8A; }

        /* Subtle surface shadow for section to avoid flat feel */
        section.py-5{ box-shadow: 0 2px 0 rgba(30,58,138,.03) inset; }

        /* Reveal on scroll */
        .reveal{ opacity: 0; transform: translateY(16px); transition: opacity .6s ease, transform .6s ease; }
        .reveal.visible{ opacity: 1; transform: translateY(0); }

        /* Responsive */
        @media (max-width: 767.98px){
            .timeline-line{ display:none; }
            .timeline-vertical{ padding-left: 0; }
            .timeline-item{ gap: .5rem; margin-bottom: .75rem; }
            .timeline-marker{ width: 20px; height: 20px; margin-top: .2rem; margin-right: .25rem; }
            .timeline-item .agenda-card{ width: 100%; margin-left: 0 !important; }
            .agenda-card::before, .agenda-card::after{ display: none; }
            .agenda-card h3{ font-size: 1rem; }
        }
    </style>
    @endpush
    @push('scripts')
    <script>
        (function(){
            try{
                var items = document.querySelectorAll('.timeline-item.reveal');
                if(!('IntersectionObserver' in window) || !items.length){
                    items.forEach(function(el){ el.classList.add('visible'); });
                    return;
                }
                var io = new IntersectionObserver(function(entries){
                    entries.forEach(function(entry){ if(entry.isIntersecting){ entry.target.classList.add('visible'); io.unobserve(entry.target); } });
                }, {rootMargin: '0px 0px -10% 0px', threshold: .15});
                items.forEach(function(el){ io.observe(el); });
            }catch(e){
                // noop
            }
        })();
    </script>
    @endpush
@endsection



