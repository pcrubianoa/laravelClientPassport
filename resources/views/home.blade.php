@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Timeline</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    @if (Auth::user()->token)
                        @if ($tweets->count())
                            @foreach ($tweets as $tweet)
                                <div class="media">
                                    <div class="media-left mr-3">
                                        <a href="">
                                            <img width="30" src="http://cdn.onlinewebfonts.com/svg/img_87237.png" alt="User Avatar">
                                        </a>
                                    </div>
                                    <div class="media-body">
                                        <strong>{{ $tweet->user->name }}</strong>
                                        <p>{{ $tweet->body }}</p>
                                    </div>
                                </div>  
                            @endforeach
                        @endif
                    @else
                    <p>Please <a href="{{ url('/auth/twitter') }}">authorize with Twitter</a></p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
