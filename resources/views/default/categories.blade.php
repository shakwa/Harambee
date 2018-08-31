@extends('app')

@section('title'){{ trans('misc.categories').' - ' }}@endsection

@section('content') 
<div class="jumbotron md index-header jumbotron_set jumbotron-cover">
      <div class="container wrap-jumbotron position-relative">
        <h2 class="title-site">{{ trans('misc.categories') }}</h2>
        <p class="subtitle-site"><strong>{{trans('misc.browse_by_category')}}</strong></p>
      </div>
    </div>

<div class="container margin-bottom-40">
	
<!-- Col MD -->
<div class="col-md-12 margin-top-20 margin-bottom-20">	
     	
     @foreach(  $data->chunk(3) as $column )
	    		
	    		<div class="col-md-3 col-center margin-bottom-15">
	    			<ul class="list-unstyled imagesCategory">
	    		@foreach ($column as $category)
	    		
	        				<li>
	        					<a class="link-category" href="{{ url('category') }}/{{ $category->slug }}">{{ $category->name }} ({{$category->campaigns()->count()}}) </a>
	        					</li>
	        					
	        			@endforeach
	        			
	        				</ul>
	        			</div>
	        	@endforeach	
 </div><!-- /COL MD -->
 
 </div><!-- container wrap-ui -->
  
@endsection

