@extends('admin.layout')

@section('css')
<link href="{{ asset('public/plugins/iCheck/all.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h4>
            {{ trans('admin.admin') }} 
            	<i class="fa fa-angle-right margin-separator"></i> 
            		{{ trans('misc.payment_settings') }}
            		
          </h4>

        </section>

        <!-- Main content -->
        <section class="content">
        	
        	 @if(Session::has('success_message'))
		    <div class="alert alert-success">
		    	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">×</span>
								</button>
		       <i class="fa fa-check margin-separator"></i> {{ Session::get('success_message') }}	        
		    </div>
		@endif

        	<div class="content">
        		
        		<div class="row">
    
        	<div class="box box-danger">
                <div class="box-header with-border">
                  <h3 class="box-title">{{ trans('misc.payment_settings') }}</h3>
                </div><!-- /.box-header -->
               
                <!-- form start -->
                <form class="form-horizontal" method="POST" action="{{ url('panel/admin/payments') }}" enctype="multipart/form-data">
                	
                	<input type="hidden" name="_token" value="{{ csrf_token() }}">	
			
					@include('errors.errors-forms')
					
									
                      <!-- Start Box Body -->
                  <div class="box-body">
                    <div class="form-group">
                      <label class="col-sm-2 control-label">{{ trans('admin.currency_code') }}</label>
                      <div class="col-sm-10">
                      	<select name="currency_code" class="form-control">
                      		
                      		<option @if( $settings->currency_code == 'USD' ) selected="selected" @endif value="USD">USD</option>
						  	<option @if( $settings->currency_code == 'EUR' ) selected="selected" @endif  value="EUR">EUR</option>
						  	<option @if( $settings->currency_code == 'GBP' ) selected="selected" @endif value="GBP">GBP</option>
						  	<option @if( $settings->currency_code == 'AUD' ) selected="selected" @endif value="AUD">AUD</option>
						  	<option @if( $settings->currency_code == 'JPY' ) selected="selected" @endif value="JPY">JPY</option>
						  	
						  	<option @if( $settings->currency_code == 'BRL' ) selected="selected" @endif value="BRL">BRL</option>
						  	<option @if( $settings->currency_code == 'MXN' ) selected="selected" @endif  value="MXN">MXN</option>
						  	<option @if( $settings->currency_code == 'SEK' ) selected="selected" @endif value="SEK">SEK</option>
						  	<option @if( $settings->currency_code == 'CHF' ) selected="selected" @endif value="CHF">CHF</option>
						  	
						  	
						  	
						  	<option @if( $settings->currency_code == 'SGD' ) selected="selected" @endif value="SGD">SGD</option>
						  	<option @if( $settings->currency_code == 'DKK' ) selected="selected" @endif value="DKK">DKK</option>
						  	<option @if( $settings->currency_code == 'RUB' ) selected="selected" @endif value="RUB">RUB</option>
                          </select>
                      </div>
                    </div>
                  </div><!-- /.box-body -->
                            
                     <!-- Start Box Body -->
                  <div class="box-body">
                    <div class="form-group">
                      <label class="col-sm-2 control-label">{{ trans('admin.paypal_account') }}</label>
                      <div class="col-sm-10">
                        <input type="text" value="{{ $settings->paypal_account }}" name="paypal_account" class="form-control" placeholder="{{ trans('admin.paypal_account') }}">
                      	<p class="help-block">{{ trans('admin.paypal_account_donations') }}</p>
                      </div>
                    </div>
                  </div><!-- /.box-body -->
                  
                  <!-- Start Box Body -->
                  <div class="box-body">
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Paypal Sandbox</label>
                      <div class="col-sm-10">
                      	
                      	<div class="radio">
                        <label class="padding-zero">
                          <input type="radio" name="paypal_sandbox" @if( $settings->paypal_sandbox == 'true' ) checked="checked" @endif value="true" checked>
                          On
                        </label>
                      </div>
                      
                      <div class="radio">
                        <label class="padding-zero">
                          <input type="radio" name="paypal_sandbox" @if( $settings->paypal_sandbox == 'false' ) checked="checked" @endif value="false">
                          Off
                        </label>
                      </div>
                      
                      </div>
                    </div>
                  </div><!-- /.box-body -->
                  
                  <div class="box-footer">
                    <button type="submit" class="btn btn-success">{{ trans('admin.save') }}</button>
                  </div><!-- /.box-footer -->
                </form>
              </div>
        			        		
        		</div><!-- /.row -->
        		
        	</div><!-- /.content -->
        	
          <!-- Your Page Content Here -->

        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->
@endsection

@section('javascript')
	
	<!-- icheck -->
	<script src="{{ asset('public/plugins/iCheck/icheck.min.js') }}" type="text/javascript"></script>
	
	<script type="text/javascript">
		//Flat red color scheme for iCheck
        $('input[type="radio"]').iCheck({
          radioClass: 'iradio_flat-red'
        });
        
	</script>
	

@endsection
