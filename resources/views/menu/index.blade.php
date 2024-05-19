@extends('layouts.app')

@push('before-styles')
@endpush

@section('content')
    @if($type == "index")
        @include('menu.table')
    @else
        @include('menu.form')
    @endif
@stop

@push('after-scripts')
    @include('menu.script')
@endpush