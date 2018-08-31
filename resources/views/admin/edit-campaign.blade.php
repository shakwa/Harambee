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
            		{{ trans('admin.edit') }}
            		
            		<i class="fa fa-angle-right margin-separator"></i> 
            		{{ $data->title }}
            		
          </h4>

        </section>

        <!-- Main content -->
        <section class="content">

        	<div class="content">
        		
       <div class="row">
       	
       	<div class="col-md-9">
    
        	<div class="box box-danger">
                <div class="box-header with-border">
                  <h3 class="box-title">{{ trans('admin.edit') }}</h3>
                </div><!-- /.box-header -->
               
                <!-- form start -->
                <form class="form-horizontal" method="POST" action="{{ url('panel/admin/campaigns/edit/'.$data->id) }}" enctype="multipart/form-data">
                	
                	<input type="hidden" name="_token" value="{{ csrf_token() }}">
			
					@include('errors.errors-forms')
									
                 <!-- Start Box Body -->
                  <div class="box-body">
                    <div class="form-group">
                      <label class="col-sm-2 control-label">{{ trans('misc.campaign_title') }}</label>
                      <div class="col-sm-10">
                        <input type="text" value="{{ $data->title }}" name="title" class="form-control" placeholder="{{ trans('misc.title') }}">
                      </div>
                    </div>
                  </div><!-- /.box-body -->
                 
                  
                  <!-- Start Box Body -->
                  <div class="box-body">
                    <div class="form-group">
                      <label class="col-sm-2 control-label">{{ trans('misc.campaign_goal') }}</label>
                      <div class="col-sm-10">
                        <input type="number" min="{{$settings->min_campaign_amount}}" autocomplete="off" value="{{ $data->goal }}" name="goal" class="form-control onlyNumber" placeholder="{{ trans('misc.campaign_goal') }}">
                      </div>
                    </div>
                  </div><!-- /.box-body -->
                  
                  <!-- Start Box Body -->
                  <div class="box-body">
                    <div class="form-group">
                      <label class="col-sm-2 control-label">{{ trans('misc.location') }}</label>
                      <div class="col-sm-10">
                        <input type="text" value="{{ $data->location }}" name="location" class="form-control onlyNumber" placeholder="{{ trans('misc.location') }}">
                      </div>
                    </div>
                  </div><!-- /.box-body -->
                  
                  <!-- Start Box Body -->
                  <div class="box-body">
                    <div class="form-group">
                      <label class="col-sm-2 control-label">{{ trans('misc.campaign_description') }}</label>
                      <div class="col-sm-10">
                        <textarea name="description" rows="5" id="description" class="form-control" placeholder="{{ trans('misc.campaign_description_placeholder') }}">{{ $data->description }}</textarea>
                      </div>
                    </div>
                  </div><!-- /.box-body -->
                  
                  <!-- Start Box Body -->
                  <div class="box-body">
                    <div class="form-group">
                      <label class="col-sm-2 control-label">{{ trans('admin.status') }}</label>
                      <div class="col-sm-10">
                        <select name="finalized" class="form-control" >
                      		<option @if($data->finalized == '0') selected="selected" @endif value="0">{{trans('admin.active')}}</option>
                      		<option @if($data->finalized == '1') selected="selected" @endif value="1">{{trans('misc.finalized')}}</option>
                          </select>
                      </div>
                    </div>
                  </div><!-- /.box-body -->
                  
                  
                  <div class="box-footer">
                  	 <a href="{{ url('panel/admin/campaigns') }}" class="btn btn-default">{{ trans('admin.cancel') }}</a>
                    <button type="submit" class="btn btn-success pull-right">{{ trans('admin.save') }}</button>
                  </div><!-- /.box-footer -->
                </form>
              </div>
              
        </div><!-- /. col-md-9 -->
        
        <div class="col-md-3">
        	
        	<div class="block-block text-center">
        		<img src="{{asset('public/campaigns/small').'/'.$data->small_image}}" class="thumbnail img-responsive">
        	</div>
		
		<div class="block-block text-center">
		{!! Form::open([
			            'method' => 'POST',
			            'url' => 'panel/admin/campaign/delete',
			            'class' => 'displayInline'
				        ]) !!}
				        {!! Form::hidden('id',$data->id ); !!}
	            	{!! Form::submit(trans('misc.delete_campaign'), ['class' => 'btn btn-lg btn-danger btn-block margin-bottom-10 actionDelete']) !!}
	        	{!! Form::close() !!}
	        </div>
		
		</div><!-- col-md-3 -->        			        		
        		
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
		
		$(document).ready(function() {
    $(".onlyNumber").keypress(function(event) {
        return /\d/.test(String.fromCharCode(event.keyCode));
    });
});

$(".actionDelete").click(function(e) {
   	e.preventDefault();
   	   	
   	var element = $(this);
	var id     = element.attr('data-url');
	var form    = $(element).parents('form');
	
	element.blur();
	
	swal(
		{   title: "{{trans('misc.delete_confirm')}}",  
		text: "{{trans('misc.confirm_delete_campaign')}}",
		  type: "warning", 
		  showLoaderOnConfirm: true,
		  showCancelButton: true,   
		  confirmButtonColor: "#DD6B55",  
		   confirmButtonText: "{{trans('misc.yes_confirm')}}",   
		   cancelButtonText: "{{trans('misc.cancel_confirm')}}",  
		    closeOnConfirm: false, 
		    }, 
		    function(isConfirm){  
		    	 if (isConfirm) {   
		    	 	form.submit(); 
		    	 	//$('#form' + id).submit();
		    	 	}
		    	 });
		    	 
		    	 
		 });
		 
		//Flat red color scheme for iCheck
        $('input[type="radio"]').iCheck({
          radioClass: 'iradio_flat-red'
        });
	
	</script>
	

@endsection
