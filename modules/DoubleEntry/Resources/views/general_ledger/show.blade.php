@extends('layouts.admin')

@include($class->views['header'])

@section('content')
    <div class="card">
        @include($class->views['filter'])
    </div>

    @include($class->views['content'])
@endsection
