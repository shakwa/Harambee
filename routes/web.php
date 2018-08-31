<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/
/* 
 |-----------------------------------
 | Index
 |-----------------------------------
 */
Route::get('/', 'HomeController@index');

Route::get('home', function(){
	return redirect('/');
});
/* 
/* 
 |-----------------------------------
 | Authentication
 |-----------------------------------
 */	
Auth::routes();

// Logout
Route::get('/logout', 'Auth\LoginController@logout');

/* 
 |
 |-----------------------------------------------
 | Ajax Request
 |--------- -------------------------------------
 */
Route::get('ajax/donations', 'AjaxController@donations');
Route::get('ajax/campaign/updates', 'AjaxController@updatesCampaign');
Route::get('ajax/campaigns', 'AjaxController@campaigns');

/* 
 |
 |-----------------------------------
 | Contact Organizer
 |-----------------------------------
 */

Route::post('contact/organizer','CampaignsController@contactOrganizer');

/* 
 |
 |-----------------------------------
 | Details Campaign
 |--------- -------------------------
 */
Route::get('campaign/{id}/{slug?}','CampaignsController@view');

/* 
 |
 |-----------------------------------
 | User Views LOGGED
 |--------- -------------------------
 */
Route::group(['middleware' => 'auth'], function() {
	
	//<-------------- Create Campaign
	Route::get('create/campaign', function(){
	return view('campaigns.create');
	});
	//  Post
	Route::post('create/campaign','CampaignsController@create');
	
	//<-------------- Edit Campaign
	Route::get('edit/campaign/{id}','CampaignsController@edit');
	Route::post('edit/campaign/{id}','CampaignsController@post_edit');
	
	//<-------------- Post a Update
	Route::get('update/campaign/{id}','CampaignsController@update');
	Route::post('update/campaign/{id}','CampaignsController@post_update');
	
	//<-------------- Edit post a Update
	Route::get('edit/update/{id}','CampaignsController@edit_update');
	Route::post('edit/update/{id}','CampaignsController@post_edit_update');
	
	Route::post('delete/image/updates','CampaignsController@delete_image_update');
	
	// Delete Campaign
	Route::get('delete/campaign/{id}','CampaignsController@delete');
	
	// Account Settings
	Route::get('account','UserController@account');
	Route::post('account','UserController@update_account');
	
	// Password
	Route::get('account/password','UserController@password');
	Route::post('account/password','UserController@update_password');
	
	// Upload Avatar
	Route::post('upload/avatar','UserController@upload_avatar');
	
	// Campaigns
	Route::get('account/campaigns', function(){
	return view('users.campaigns');
	});
	
	// Donations
	Route::get('account/donations', function(){
	return view('users.donations');
	});
	
});
/* 
 |
 |-----------------------------------
 | Admin Panel
 |--------- -------------------------
 */
Route::group(['middleware' => 'role'], function() {
		
	// Dashboard
	Route::get('panel/admin','AdminController@admin');
	
	// Settings
	Route::get('panel/admin/settings','AdminController@settings');
	Route::post('panel/admin/settings','AdminController@saveSettings');
	
	// Limits
	Route::get('panel/admin/settings/limits','AdminController@settingsLimits');
	Route::post('panel/admin/settings/limits','AdminController@saveSettingsLimits');
	
	// Campaigns
	Route::get('panel/admin/campaigns','AdminController@campaigns');
	Route::post('panel/admin/campaigns','AdminController@saveCampaigns');
	
	// Edit Campaign
	Route::get('panel/admin/campaigns/edit/{id}','AdminController@editCampaigns');
	Route::post('panel/admin/campaigns/edit/{id}','AdminController@postEditCampaigns');
	
	// Delete Campaign
	Route::post('panel/admin/campaign/delete','AdminController@deleteCampaign');
	
	// Donations
	Route::get('panel/admin/donations','AdminController@donations');
	Route::get('panel/admin/donations/{id}','AdminController@donationView');
	
	// Members
	Route::resource('panel/admin/members', 'AdminController', 
		['names' => [
		    'edit'    => 'user.edit',
		    'destroy' => 'user.destroy'
		 ]]
	);
	
	// Add Member
	Route::get('panel/admin/member/add','AdminController@add_member');
	Route::post('panel/admin/member/add','AdminController@storeMember');
	
	// Pages
	Route::resource('panel/admin/pages', 'PagesController', 
		['names' => [
		    'edit'    => 'pages.edit',
		    'destroy' => 'pages.destroy'
		 ]]
	);
	
	// Payments Settings
	Route::get('panel/admin/payments','AdminController@payments');
	Route::post('panel/admin/payments','AdminController@savePayments');
	
	// Profiles Social
	Route::get('panel/admin/profiles-social','AdminController@profiles_social');
	Route::post('panel/admin/profiles-social','AdminController@update_profiles_social');
	
	
});

/* 
 |
 |-----------------------------------
 | Donations
 |--------- -------------------------
 */
Route::get('donate/{id}/{slug?}','DonationsController@show');
Route::post('donate/{id}','DonationsController@send');

// Paypal IPN
Route::post('paypal/ipn','DonationsController@paypalIpn');


	Route::get('paypal/donation/success/{id}', function($id){
			
			session()->put('donation_success', trans('misc.donation_success'));
			return redirect("campaign/".$id);
	});
	

	Route::get('paypal/donation/cancel/{id}', function($id){
			
			session()->put('donation_cancel', trans('misc.donation_cancel'));
	       return redirect("campaign/".$id);
	});
	
/* 
 |
 |------------------------
 | Pages Static Custom
 |--------- --------------
 */
Route::get('page/{page}', function( $page ){
	
	$response = App\Models\Pages::where( 'slug','=', $page )->first();
	
	$total = count( $response );
	
	if( $total == 0 ) {
		abort(404);
	} else {
		
		$title = $response->title.' - ';
		return view('pages.home', compact('response', 'title'));
	}

})->where('page','[^/]*' );

