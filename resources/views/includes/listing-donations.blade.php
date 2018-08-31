<?php $hashAvatar = md5( strtolower( trim( $donation->email ) ) );  ?>
<li class="list-group-item">
       	<div class="media">
			  <div class="media-left">
			      <img class="media-object img-circle holderImage" data-src="holder.js/40x40?bg=f45302&fg=FFFFFF&text={{strtoupper($letter)}}" width="40" height="40" alt="{{$letter}}">
			  </div>
			  <div class="media-body">
			    <h4 class="media-heading">{{$donation->fullname}}</h4>
			    <span class="btn-block recent-donation-amount font-default">
			    	{{$settings->currency_symbol.number_format($donation->donation)}}
			    </span>
			    @if( $donation->comment != '' )
			    <p class="margin-bottom-5">{{$donation->comment}}</p>
			    @endif
			    <small class="btn-block timeAgo text-muted" data="{{ date('c', strtotime( $donation->date )) }}"></small>
			  </div>
		</div>
</li>