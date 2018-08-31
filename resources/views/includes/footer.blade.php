<!-- ***** Footer ***** -->
    <footer class="footer-main">
    	<div class="container">
    		
    		<div class="row">
    			<div class="col-md-12 text-center">
    				<a href="{{ url('/') }}">
    					<img src="{{ asset('public/img/watermark.png') }}" />
    				</a>
    			   <p class="margin-tp-xs">{{ $settings->description }}</p>
    			   
    			   <ul class="list-inline">
					   @if( $settings->twitter != '' ) 
					   <li><a target="_blank" href="{{$settings->twitter}}" class="ico-social"><i class="fa fa-twitter"></i></a></li>
					   @endif
					 
					 @if( $settings->facebook != '' )   
					   <li><a target="_blank" href="{{$settings->facebook}}" class="ico-social"><i class="fa fa-facebook"></i></a></li>
					 @endif
					
					 @if( $settings->instagram != '' )   
					   <li><a target="_blank" href="{{$settings->instagram}}" class="ico-social"><i class="fa fa-instagram"></i></a></li>
					 @endif
					 
					 @if( $settings->linkedin != '' )   
					   <li><a target="_blank" href="{{$settings->linkedin}}" class="ico-social"><i class="fa fa-linkedin"></i></a></li>
					   @endif
					 
					 @if( $settings->googleplus != '' )   
					   <li><a target="_blank" href="{{$settings->googleplus}}" class="ico-social"><i class="fa fa-google-plus"></i></a></li>
					 @endif
					 </ul >
					 
					 <ul class="list-inline margin-bottom-zero">
					 	@foreach( \App\Models\Pages::all() as $page )
					 	<li><a href="{{ url('page',$page->slug) }}">{{ $page->title }}</a></li>
					 	@endforeach
					 </ul>
					 
    			</div><!-- ./End col-md-* -->
    		</div><!-- ./End Row -->
    	</div><!-- ./End Container -->
    </footer><!-- ***** Footer ***** -->

<footer class="subfooter">
	<div class="container">
	<div class="row">
    			<div class="col-md-12 text-center padding-top-20">
    				<p>&copy; {{ $settings->title }} - <?php echo date('Y'); ?> </p>
    			</div><!-- ./End col-md-* -->
	</div>
</div>
</footer>    
