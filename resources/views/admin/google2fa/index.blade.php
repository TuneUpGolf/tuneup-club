@php
      $languages = \App\Facades\UtilityFacades::languages();
@endphp
@extends('layouts.app')
@section('title', __('Two Factor Authentication'))
@section('auth-topbar')
    <li class="language-btn">
        <select class="nice-select"
            onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);"
            id="language">
            @foreach ($languages as $language)
                <option class="" @if ($lang == $language) selected @endif
                    value="{{ route('change.lang', $language) }}">{{ Str::upper($language) }}
                </option>
            @endforeach
        </select>
    </li>
@endsection
@section('content')
    <div class="card card-primary">
        <div class="card-header">
            {{ __('Two Factor Authentication') }}
        </div>
        <div class="card-body">
            {!! Form::open(['route' => '2fa', 'method' => 'POST','class'=>'form-horizontal']) !!}
                <div class="form-group mb-3">
                    <div class="form-group">
                        {{ Form::label('email', __('One time Password')) }}
                        {!! Form::text('one_time_password', null, [
                            'class' => 'form-control',
                            'id' => 'one_time_password',
                            'placeholder' => __('Enter one time password'),
                        ]) !!}
                        @if ($errors->has('email'))
                            <span class="invalid-feedback d-block">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                        @endif
                    </div>
                    @if ($errors->has('one_time_password'))
                        <span class="invalid-feedback d-block">
                            <strong>{{ $errors->first('one_time_password') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="text-center">
                    {{ Form::button(__('Sign in'), ['type' => 'submit', 'class' => 'btn btn-primary my-4']) }}
                </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection
