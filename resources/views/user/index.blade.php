@extends('layouts.backend.app')

@push('before-styles')
@endpush

@section('content')
    @if($type == "index")
        @include('backend.master.categori.table')
    @else
        @include('backend.master.categori.form')
    @endif
@stop

@push('after-scripts')
    @include('backend.master.categori.script')
@endpush