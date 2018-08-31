@if( $data->count() != 0 )
@foreach ( $data as $key )
    @include('includes.list-campaigns')
@endforeach
@if( $data->hasMorePages() )
<div class="col-xs-12 loadMoreSpin">
{{ $data->links('vendor.pagination.loadmore') }}
</div>
@endif
@endif