<?php $settings = App\Models\AdminSettings::first(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('description_custom'){{{ $settings->description }}}">
    <meta name="keywords" content="{{{ $settings->keywords }}}" />
    <link rel="shortcut icon" href="{{{ asset('public/img/favicon.png') }}}" />

	<title>@section('title')@show @if( isset( $settings->title ) ){{{$settings->title}}}@endif</title>
	
	@include('includes.css_general')

	<!-- Fonts -->
	<link href='http://fonts.googleapis.com/css?family=Raleway:100,600' rel='stylesheet' type='text/css'>

	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
	
	@yield('css')
	
	<!-- Scripts -->
    <script>
        window.Laravel = <?php echo json_encode([
            'csrfToken' => csrf_token(),
        ]); ?>
    </script>
    
</head>
<body>
	<div class="popout font-default"></div>
	<div class="wrap-loader">
		<i class="fa fa-cog fa-spin fa-3x fa-fw cog-loader"></i>
		<i class="fa fa-cog fa-spin fa-3x fa-fw cog-loader-small"></i>
	</div>
	@include('includes.navbar')
    
		@yield('content')

			@include('includes.footer')
	
		@include('includes.javascript_general')
	
	@yield('javascript')
	
</body>
</html>
