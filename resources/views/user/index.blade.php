@extends('layouts.app')

@push('before-styles')
@endpush

@section('content')
    @if($type == "index")
        @include('user.table')
    @else
        @include('user.form')
    @endif
@stop

@push('after-scripts')
    @include('user.script')
@endpush