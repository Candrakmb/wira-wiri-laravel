@extends('layouts.app')

@push('before-styles')
@endpush

@section('content')
    @if($type == "index")
        @include('order.table')
    @else
        @include('order.form')
    @endif
@stop

@push('after-scripts')
    @include('order.script')
@endpush