@extends('vendor.installer.layouts.master')
@section('template_title')
    Step 3 | Purchase Info
@endsection

@section('title')
    <i class="fa fa-sign-in-alt fa-fw" aria-hidden="true"></i>
    Purchase Info
@endsection

@section('container')
    <div class="tabs tabs-full">

        <form method="post" action="{{ route('LaravelInstaller::purchaseSaveWizard') }}" class="">
            <div class="" id="">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="form-group {{ $errors->has('purchase_code') ? ' has-error ' : '' }}">
                    <label for="purchase_code">
                        Purchase code
                    </label>
                    <input type="text" name="purchase_code" id="purchase_code" value="" placeholder="input your purchase code" />
                    @if ($errors->has('purchase_code'))
                        <span class="error-block">
                            <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                            {{ $errors->first('purchase_code') }}
                        </span>
                    @endif
                </div>


                <div class="buttons">
                    <input type="submit" value="Configure Admin Info" class="button">
                </div>
            </div>
        </form>

    </div>
@endsection