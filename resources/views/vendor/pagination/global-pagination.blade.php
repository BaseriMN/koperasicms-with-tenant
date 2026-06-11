@if ($paginator->hasPages())
    <nav class="kop-pagination" role="navigation" aria-label="Pagination">
        {{-- Butang Sebelum --}}
        @if ($paginator->onFirstPage())
            <span class="kp-btn kp-disabled" aria-disabled="true">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
            </span>
        @else
            <a class="kp-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelum">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
            </a>
        @endif

        {{-- Nombor halaman --}}
        @foreach ($elements as $element)
            {{-- "..." --}}
            @if (is_string($element))
                <span class="kp-dots">{{ $element }}</span>
            @endif

            {{-- Senarai nombor --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="kp-btn kp-active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="kp-btn" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Butang Seterusnya --}}
        @if ($paginator->hasMorePages())
            <a class="kp-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Seterusnya">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
            </a>
        @else
            <span class="kp-btn kp-disabled" aria-disabled="true">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
            </span>
        @endif
    </nav>

    {{-- Maklumat ringkas --}}
    <div class="kp-info">
        Memaparkan <strong>{{ $paginator->firstItem() ?? 0 }}</strong>–<strong>{{ $paginator->lastItem() ?? 0 }}</strong>
        daripada <strong>{{ $paginator->total() }}</strong> rekod
    </div>

    <style>
        .kop-pagination {
            display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
        }
        .kp-btn {
            min-width: 38px; height: 38px; padding: 0 12px;
            display: inline-flex; align-items: center; justify-content: center;
            border: 1px solid var(--line); border-radius: 10px;
            background: var(--panel); color: var(--ink);
            font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 600;
            text-decoration: none; cursor: pointer; transition: all .16s ease;
        }
        .kp-btn svg { width: 16px; height: 16px; }
        .kp-btn:hover { border-color: var(--gold); color: var(--gold); transform: translateY(-1px); }
        .kp-active {
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-soft) 100%);
            border-color: transparent; color: var(--ink);
            font-family: 'Fraunces', serif;
            box-shadow: 0 6px 16px -6px rgba(192,150,44,.6);
        }
        .kp-active:hover { transform: none; color: var(--ink); }
        .kp-disabled {
            opacity: .4; cursor: not-allowed; background: var(--bg-2);
        }
        .kp-disabled:hover { border-color: var(--line); color: var(--ink); transform: none; }
        .kp-dots { padding: 0 6px; color: var(--muted); font-weight: 600; }
        .kp-info {
            margin-top: 12px; font-size: 12.5px; color: var(--muted);
        }
        .kp-info strong { color: var(--ink); font-weight: 600; }
    </style>
@endif