@extends('layouts.app')

@section('content')
    <div class="container">
            <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right top_search" align="right">
                <div class="input-group">
                    <form method="POST" action="{{ route('tasks.store') }}">
                        @csrf
                        <button type="submit" class="btn btn-success pull-right"><i class="fa fa-plus"></i> Add New</button>
                    </form>
                </div>
            </div>
        <div class="row justify-content-center">
            <div class="col-md-12">
                <tasks-list :tasks="{{ $tasks }}"></tasks-list>
            </div>
        </div>
    </div>
@endsection
