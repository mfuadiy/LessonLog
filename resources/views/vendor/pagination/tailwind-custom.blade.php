@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-center mt-4">
        <ul class="inline-flex items-center -space-x-px">
            {{-- Tombol Previous --}}
            @if ($paginator->onFirstPage())
                <li>
                    <span class="px-3 py-2 leading-tight text-gray-400 bg-gray-100 border border-gray-300 rounded-l-lg cursor-not-allowed">
                        <i class="bi bi-chevron-left"></i>
                    </span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" 
                       class="px-3 py-2 leading-tight text-gray-700 bg-white border border-gray-300 hover:bg-blue-50 rounded-l-lg transition">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
            @endif

            {{-- Nomor Halaman --}}
            @foreach ($elements as $element)
                {{-- Tanda ... --}}
                @if (is_string($element))
                    <li>
                        <span class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300">
                            {{ $element }}
                        </span>
                    </li>
                @endif

                {{-- Link Nomor Halaman --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li>
                                <span class="px-3 py-2 leading-tight text-white bg-blue-600 border border-blue-600 font-semibold">
                                    {{ $page }}
                                </span>
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}" 
                                   class="px-3 py-2 leading-tight text-gray-700 bg-white border border-gray-300 hover:bg-blue-50 transition">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Tombol Next --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" 
                       class="px-3 py-2 leading-tight text-gray-700 bg-white border border-gray-300 hover:bg-blue-50 rounded-r-lg transition">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            @else
                <li>
                    <span class="px-3 py-2 leading-tight text-gray-400 bg-gray-100 border border-gray-300 rounded-r-lg cursor-not-allowed">
                        <i class="bi bi-chevron-right"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
