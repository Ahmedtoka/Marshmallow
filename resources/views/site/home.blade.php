@extends('layouts.site')

@section('content')
    @php $tinted = false; @endphp
    @foreach ($sections as $key => $section)
        {{-- One finder per page: when the hero is visible it carries the finder. --}}
        @continue($key === 'class_finder' && $sections->has('hero'))
        @continue(! view()->exists('site.home.'.$key))
        <div data-track-section="{{ $key }}">
            @include('site.home.'.$key, ['section' => $section, 'tint' => $tinted])
        </div>
        @php
            if (! in_array($key, ['hero', 'enroll_cta'], true)) {
                $tinted = ! $tinted;
            }
        @endphp
    @endforeach
@endsection
