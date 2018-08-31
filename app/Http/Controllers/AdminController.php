<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\AdminSettings;
use App\Models\Campaigns;
use App\Models\Donations;
use App\Helper;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Image;

class AdminController extends Controller {
	
	public function __construct( AdminSettings $settings) {
		$this->settings = $settings::first();
	}
	
		 public function index(Request $request) {
	 	
		$query = $request->input('q');
		
		if( $query != '' && strlen( $query ) > 2 ) {
		 	$data = User::where('name', 'LIKE', '%'.$query.'%')
			->orWhere('username', 'LIKE', '%'.$query.'%')
		 	->orderBy('id','desc')->paginate(20);
		 } else {
		 	$data = User::orderBy('id','desc')->paginate(20);
		 }
		
    	return view('admin.members', ['data' => $data,'query' => $query]);
	 }

	public function edit($id) {
		
		$data = User::findOrFail($id);
		
		if( $data->id == 1 || $data->id == Auth::user()->id ) {
			\Session::flash('info_message', trans('admin.user_no_edit'));
			return redirect('panel/admin/members');
		}
    	return view('admin.edit-member')->withData($data);
	
	}//<--- End Method
	
	public function update($id, Request $request) {
    	
    $user = User::findOrFail($id);
		
	$input = $request->all();
	
	if( !empty( $request->password ) )	 {
		$rules = array(
			'name' => 'required|min:3|max:25',
			'email'     => 'required|email|unique:users,email,'.$id,
			 'password' => 'min:6',
			);
			
			$password = \Hash::make($request->password);
			
	} else {
		$rules = array(
			'name' => 'required|min:3|max:25',
			'email'     => 'required|email|unique:users,email,'.$id,
			);
			
			$password = $user->password;
	}
		
	   $this->validate($request,$rules);
	  
	  $user->name = $request->name;
	  $user->email = $request->email;
	  $user->role = $request->role;
	  $user->password = $password;
      $user->save();

    \Session::flash('success_message', trans('admin.success_update'));

    return redirect('panel/admin/members');
	
	}//<--- End Method
	
	public function destroy($id)
    {
    	$user = User::findOrFail($id);
		
    	if( $user->id == 1 || $user->id == Auth::user()->id ) {
    		return redirect('panel/admin/members');
			exit;
    	}
				
		// Find User
		
		// Stop Campaigns
		$allCampaigns = Campaigns::where('user_id',$id)->update(array('finalized' => '1'));
		
		//<<<-- Delete Avatar -->>>/
		$fileAvatar    = 'public/avatar/'.$user->avatar;
			
		if ( \File::exists($fileAvatar) && $user->avatar != 'default.jpg' ) {
			 \File::delete($fileAvatar);	
		}//<--- IF FILE EXISTS
		
			
        $user->delete();
		return redirect('panel/admin/members');
		
    }//<--- End Method
    
    public function add_member() {
    	return view('admin.add-member');
    }
	
	public function storeMember(Request $request){
		
		$this->validate($request, [
			'name' => 'required|min:3|max:30',
            'email'     => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
		
		$user = new User;
		$user->name = $request->name;
		$user->email = $request->email;
		$user->role = $request->role;
		$user->avatar = 'default.jpg';
		$user->token = str_random(80);
		$user->password = \Hash::make($request->password);
		$user->save();
		
		 \Session::flash('success_message', trans('admin.success_add'));
		return redirect('panel/admin/members');
		
	}
    
	// START
	public function admin() {
		
		return view('admin.dashboard');
				
	}//<--- END METHOD
	
	public function settings() {
		
		return view('admin.settings')->withSettings($this->settings);
		
	}//<--- END METHOD
	
	public function saveSettings(Request $request) {
						
		$rules = array(
            'title'            => 'required',
	        'welcome_text' 	   => 'required',
	        'welcome_subtitle' => 'required',
	        'keywords'         => 'required',
	        'description'      => 'required',
	        'email_no_reply'   => 'required',
	        'email_admin'      => 'required',
        );
		
		$this->validate($request, $rules);
		
		$sql                      = AdminSettings::first();
		$sql->title               = $request->title;
		$sql->welcome_text        = $request->welcome_text;
		$sql->welcome_subtitle    = $request->welcome_subtitle;
		$sql->keywords            = $request->keywords;
		$sql->description         = $request->description;
		$sql->email_no_reply      = $request->email_no_reply;
		$sql->email_admin         = $request->email_admin;
		$sql->save();

		\Session::flash('success_message', trans('admin.success_update'));

    	return redirect('panel/admin/settings');
						
	}//<--- END METHOD
	
	public function settingsLimits() {
		
		return view('admin.limits')->withSettings($this->settings);
		
	}//<--- END METHOD
	
	public function saveSettingsLimits(Request $request) {
		
				
		$rules = array(
	        'min_campaign_amount'             => 'required|integer|min:1',
	        'min_donation_amount'             => 'required|integer|min:1',
        );
		
		$this->validate($request, $rules);
				
		$sql                      = AdminSettings::first();
		$sql->result_request      = $request->result_request;
		$sql->file_size_allowed   = $request->file_size_allowed;
		$sql->min_campaign_amount   = $request->min_campaign_amount;
		$sql->min_donation_amount   = $request->min_donation_amount;
		
		$sql->save();

		\Session::flash('success_message', trans('admin.success_update'));

    	return redirect('panel/admin/settings/limits');
						
	}//<--- END METHOD
	
	public function profiles_social(){
		return view('admin.profiles-social')->withSettings($this->settings);
	}//<--- End Method
	
	public function update_profiles_social(Request $request) {
			
		$sql = AdminSettings::find(1);
		
		$rules = array(
            'twitter'    => 'url',
            'facebook'   => 'url',
            'googleplus' => 'url',
            'linkedin'   => 'url',
        );
		
		$this->validate($request, $rules);
		
	    $sql->twitter       = $request->twitter;
		$sql->facebook      = $request->facebook;
		$sql->googleplus    = $request->googleplus;
		$sql->instagram     = $request->instagram;
		
		$sql->save();
	
	    \Session::flash('success_message', trans('admin.success_update'));
	
	    return redirect('panel/admin/profiles-social');
	}//<--- End Method
	
	public function donations(){
		
		$data = Donations::orderBy('id','DESC')->paginate(100);
		return view('admin.donations', ['data' => $data, 'settings' => $this->settings]);
	}//<--- End Method
	
	public function donationView($id){
		
		$data = Donations::findOrFail($id);
		return view('admin.donation-view', ['data' => $data, 'settings' => $this->settings]);
	}//<--- End Method
	
	public function payments(){
		return view('admin.payments-settings')->withSettings($this->settings);
	}//<--- End Method
	
	public function savePayments(Request $request) {
			
		$sql = AdminSettings::find(1);
		
		$rules = array(
            'paypal_account'    => 'required|email',
        );
		
		$this->validate($request, $rules);
		
		switch( $request->currency_code ) {
			case 'USD':
				$currency_symbol  = '$';
				break;
			case 'EUR':
				$currency_symbol  = '€';
				break;
			case 'GBP':
				$currency_symbol  = '£';
				break;
			case 'AUD':
				$currency_symbol  = '$';
				break;
			case 'JPY':
				$currency_symbol  = '¥';
				break;
				
			case 'BRL':
				$currency_symbol  = 'R$';
				break;
			case 'MXN':
				$currency_symbol  = '$';
				break;
			case 'SEK':
				$currency_symbol  = 'Kr';
				break;
			case 'CHF':
				$currency_symbol  = 'CHF';
				break;
			case 'SGD':
				$currency_symbol  = '$';
				break;
			case 'DKK':
				$currency_symbol  = 'Kr';
				break;
			case 'RUB':
				$currency_symbol  = 'руб';
				break;
		}
		
	    $sql->paypal_account       = $request->paypal_account;
		$sql->currency_symbol      = $currency_symbol;
		$sql->currency_code    = $request->currency_code;
		$sql->paypal_sandbox     = $request->paypal_sandbox;
		
		$sql->save();
	
	    \Session::flash('success_message', trans('admin.success_update'));
	
	    return redirect('panel/admin/payments');
	}//<--- End Method
	
	public function campaigns(){
		
		$data = Campaigns::orderBy('id','DESC')->paginate(50);
		return view('admin.campaigns', ['data' => $data, 'settings' => $this->settings]);
	}//<--- End Method
	
	public function editCampaigns($id){
		
		$data = Campaigns::findOrFail($id);
		return view('admin.edit-campaign', ['data' => $data, 'settings' => $this->settings]);
	}
	
	public function postEditCampaigns(Request $request){
		
		$sql = Campaigns::findOrFail($request->id);
		
		$messages = array (
		'description.required' => trans('misc.description_required'),
		'goal.min' => trans('misc.amount_minimum', ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
	);
	
		$rules = array(
            'title'             => 'required|min:3|max:45',
	    	'goal'             => 'required|integer|min:'.$this->settings->min_campaign_amount,
	    	 'location'        => 'required|max:50',
	        'description'  => 'required|min:20',
        );
		
		$this->validate($request, $rules, $messages);
		
		$sql->title = $request->title;
		$sql->goal = $request->goal;
		$sql->location = $request->location;
		$sql->description = $request->description;
		$sql->finalized = $request->finalized;
		$sql->save();

		\Session::flash('success_message', trans('admin.success_update'));
	    return redirect('panel/admin/campaigns');
	}
	
	public function deleteCampaign(Request $request){
		
		$data = Campaigns::findOrFail($request->id);
		
		$path_small     = 'public/campaigns/small/'; 
		$path_large     = 'public/campaigns/large/';
		$path_updates = 'public/campaigns/updates/';
		
		$updates = $data->updates()->get();
		
		//Delete Updates
		foreach ($updates as $key) {
			
			if ( \File::exists($path_updates.$key->image) ) {
					\File::delete($path_updates.$key->image);
				}//<--- if file exists
				
				$key->delete();
		}//<-- 
		
		// Delete Campaign
		if ( \File::exists($path_small.$data->small_image) ) {
					\File::delete($path_small.$data->small_image);
				}//<--- if file exists
				
				if ( \File::exists($path_large.$data->large_image) ) {
					\File::delete($path_large.$data->large_image);
				}//<--- if file exists
		
		 $data->delete();
		 
		 \Session::flash('success_message', trans('misc.success_delete'));
	    return redirect('panel/admin/campaigns');
	}
			
}// End Class