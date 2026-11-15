@props(['conference', 'title', 'sub' => null, 'stamp' => false])
{{-- Page header: FRI logo, page title and the shared navigation card --}}
<a href="{{ route('home') }}" class="siteHeaderLink">
    <div class="siteHeader">
        <img src="{{ asset('images/FRI_logo.png') }}" alt="FRI Logo" class="friLogo" loading="lazy"/>
    </div>
</a>

<header>
    <div class="heroMain">
        <div class="heroTitle">
            <div class="heroText">
                <div class="heroHeading">
                    <h1>{!! $title !!}</h1>
                    @if($sub)
                        <p class="sub">{{ $sub }}</p>
                    @endif
                    @if($stamp)
                        <div class="editionStamp" aria-label="{{ $conference->editionNumber() }}. ročník {{ $conference->year }}">
                            <span class="editionStampMain">{{ $conference->editionNumber() }}. ročník</span>
                            <span class="editionStampYear">{{ $conference->year }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <x-site.nav-card :conference="$conference" />
</header>
