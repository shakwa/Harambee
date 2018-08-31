<?php 
// ** Data User logged ** //
     $user = Auth::user();
	 $settings = App\Models\AdminSettings::first();
	 $data = App\Models\Campaigns::where('user_id',Auth::user()->id)
	 ->orderBy('id','DESC')
	 ->paginate(20);
	  ?>
@extends('app')

@section('title') {{ trans('misc.campaigns') }} - @endsection

@section('content') 
<div class="jumbotron md index-header jumbotron_set jumbotron-cover">
      <div class="container wrap-jumbotron position-relative">
        <h2 class="title-site">{{ trans('misc.campaigns') }}</h2>
      </div>
    </div>

<div class="container margin-bottom-40">
	
		<!-- Col MD -->
		<div class="col-md-8 margin-bottom-20">

<div class="table-responsive">
   <table class="table table-striped"> 
   	
   	@if( $data->total() !=  0 && $data->count() != 0 )
   	<thead> 
   		<tr>
   		 <th class="active">ID</th>
   		  <th class="active">{{ trans('misc.title') }}</th>
          <th class="active">{{ trans('misc.goal') }}</th>
          <th class="active">{{ trans('misc.funds_raised') }}</th>
          <th class="active">{{ trans('admin.status') }}</th>
          <th class="active">{{ trans('admin.date') }}</th>
          <th class="active">{{ trans('admin.actions') }}</th> 
          </tr>
   		  </thead> 
   		  
   		  <tbody> 
   		      @foreach( $data as $campaign )
                    <tr>
                      <td>{{ $campaign->id }}</td>
                      <td><img src="{{asset('public/campaigns/small').'/'.$campaign->small_image}}" width="20" /> 
                      	<a title="{{$campaign->title}}" href="{{ url('campaign',$campaign->id) }}" target="_blank">{{ str_limit($campaign->title,20,'...') }} <i class="fa fa-external-link-square"></i></a>
                      	</td>
                      <td>{{ $settings->currency_symbol.number_format($campaign->goal) }}</td>
                      <td>{{ $settings->currency_symbol.number_format($campaign->donations()->sum('donation')) }}</td>
                      <td>
                      	@if( $campaign->finalized == 0 )
                      	<span class="label label-success">{{trans('misc.active')}}</span>
                      	@else
                      	<span class="label label-default">{{trans('misc.finalized')}}</span>
                      	@endif
                      </td>
                      <td>{{ date('d M, y', strtotime($campaign->date)) }}</td>
                      <td> 
                     
                     @if( $campaign->finalized == 0 )
                      	<a href="{{ url('edit/campaign',$campaign->id) }}" class="btn btn-success btn-xs padding-btn">
                      		{{ trans('admin.edit') }}
                      	</a> 
                      	@else
                      	 {{trans('misc.finalized')}}
                      	@endif
                      	
                      	</td>
                    </tr><!-- /.TR -->
                    @endforeach
                    
                    @else
                    <hr />
                    	<h3 class="text-center no-found">{{ trans('misc.no_results_found') }}</h3>

                    @endif   		  		 		
   		  		 		</tbody> 
   		  		 		</table>
   		  		 		</div>
   		  		 	
   		  		 	@if( $data->lastPage() > 1 )	
   		  		 		{{ $data->links() }}
   		  		 		@endif
   		  		 		
		</div><!-- /COL MD -->
		
		<div class="col-md-4">
			@include('users.navbar-edit')
		</div>
		
 </div><!-- container -->
 
 <!-- container wrap-ui -->
@endsection

