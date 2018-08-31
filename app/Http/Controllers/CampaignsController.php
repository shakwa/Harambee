<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\AdminSettings;
use App\Models\Campaigns;
use App\Models\Updates;
use App\Models\User;
use App\Helper;
use Carbon\Carbon;
use Mail;

class CampaignsController extends Controller
{
	
	public function __construct( AdminSettings $settings, Request $request) {
		$this->settings = $settings::first();
		$this->request = $request;
	}
	
	protected function validator(array $data, $id = null) {
	 	
    	Validator::extend('ascii_only', function($attribute, $value, $parameters){
    		return !preg_match('/[^x00-x7F\-]/i', $value);
		});
		
		$sizeAllowed = $this->settings->file_size_allowed * 1024;
		$dimensions = explode('x',$this->settings->min_width_height_image);
		
		$messages = array (
		'photo.required' => trans('misc.please_select_image'),
		'description.required' => trans('misc.description_required'),
		'goal.min' => trans('misc.amount_minimum', ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
        "photo.max"   => trans('misc.max_size').' '.Helper::formatBytes( $sizeAllowed, 1 ),
	);
		
		// Create Rules
		if( $id == null ) {
			return Validator::make($data, [
			'photo'           => 'required|mimes:jpg,gif,png,jpe,jpeg|image_size:>='.$dimensions[0].',>='.$dimensions[1].'|max:'.$this->settings->file_size_allowed.'',
        	'title'             => 'required|min:3|max:45',
        	'goal'             => 'required|integer|min:'.$this->settings->min_campaign_amount,
        	 'location'        => 'required|max:50',
            'description'  => 'required|min:20',	        
        ], $messages);
		
		// Update Rules
		} else {
			return Validator::make($data, [
				'photo'           => 'mimes:jpg,gif,png,jpe,jpeg|image_size:>='.$dimensions[0].',>='.$dimensions[1].'|max:'.$this->settings->file_size_allowed.'',
		    	'title'             => 'required|min:3|max:45',
		    	'goal'             => 'required|integer|min:'.$this->settings->min_campaign_amount,
		    	 'location'        => 'required|max:50',
		        'description'  => 'required|min:20',
		        ]);
		}
		
    }

	public function create() {
		
		// PATHS
		$temp            = 'public/temp/';
	    $path_small    = 'public/campaigns/small/'; 
		$path_large   = 'public/campaigns/large/';
		
		$input      = $this->request->all();
		$validator = $this->validator($input);
		
		 if ($validator->fails()) {
	        return response()->json([
			        'success' => false,
			        'errors' => $validator->getMessageBag()->toArray(),
			    ]); 
	    } //<-- Validator
	    
	    if( $this->request->hasFile('photo') )	{
	    	
			$extension    = $this->request->file('photo')->getClientOriginalExtension();
			$file_large     = strtolower(Auth::user()->id.time().str_random(40).'.'.$extension);
			$file_small     = strtolower(Auth::user()->id.time().str_random(40).'.'.$extension);
			
			if( $this->request->file('photo')->move($temp, $file_large) ) {
				
				set_time_limit(0);
				
				//=============== Image Large =================//
				$width  = Helper::getWidth( $temp.$file_large );
				$height = Helper::getHeight( $temp.$file_large );
				$max_width = '800';
				
				if( $width < $height ) {
					$max_width = '400';
				}
				
				if ( $width > $max_width ) {
					$scale = $max_width / $width;
					$uploaded = Helper::resizeImage( $temp.$file_large, $width, $height, $scale, $temp.$file_large );
				} else {
					$scale = 1;
					$uploaded = Helper::resizeImage( $temp.$file_large, $width, $height, $scale, $temp.$file_large );
				}
				
				//=============== Small Large =================//
				Helper::resizeImageFixed( $temp.$file_large, 400, 300, $temp.$file_small );
				
				//======= Copy Folder Small and Delete...
				if ( \File::exists($temp.$file_small) ) {
					\File::copy($temp.$file_small, $path_small.$file_small);
					\File::delete($temp.$file_small);
				}//<--- IF FILE EXISTS
				
				
				//======= Copy Folder Large and Delete...
				if ( \File::exists($temp.$file_large) ) {
					\File::copy($temp.$file_large, $path_large.$file_large);
					\File::delete($temp.$file_large);
				}//<--- IF FILE EXISTS
				
			}

			$image_small  = $file_small;
			$image_large  = $file_large; 
			
	    }//<====== End HasFile
	    
	    
	    $sql                        = new Campaigns;
		$sql->title                = trim($this->request->title);
		$sql->small_image   = $image_small;
		$sql->large_image   = $image_large;
		$sql->description     = trim(Helper::checkTextDb($this->request->description));
		$sql->user_id          = Auth::user()->id;
		$sql->date               = Carbon::now();
		$sql->token_id         = str_random(200);
		$sql->goal               = trim($this->request->goal);
		$sql->location          = trim($this->request->location);
		$sql->save();
		
		$id_campaign = $sql->id;
	    
	    return response()->json([
				        'success' => true,
				        'target' => url('campaign',$id_campaign),
				    ]);
		
	}//<<--- End Method

	
	public function view($id, $slug = null){
		
		$response = Campaigns::where('id',$id)->where('status','active')->firstOrFail();
		
		$uri = $this->request->path();
		
		if( str_slug( $response->title ) == '' ) {
				$slugUrl  = '';
			} else {
				$slugUrl  = str_slug( $response->title );
			}
			
			$url_image = 'campaign/'.$response->id.'/'.$slugUrl;
			
			//<<<-- * Redirect the user real page * -->>>
			$uriImage     =  $this->request->path();
			$uriCanonical = $url_image;
			
			if( $uriImage != $uriCanonical ) {
				return redirect($uriCanonical);
			}
			
		return view('campaigns.view')->withResponse($response);
		
	}// End Method
	
	public function contactOrganizer() {
		
		$settings  = AdminSettings::first();
		
		$emailUser = User::find($this->request->id);
		
		if( $emailUser->email == '' ) {
			return response()->json([
				        'success' => false,
				        'error_fatal' => trans('misc.error'),
				    ]);
		}
	   	   
		$validator = Validator::make($this->request->all(), [
		'name'       => 'required|max:30',
		'email'       => 'required|email',
		'message'       => 'required|min:10',
	    	]);
				   
		   if ($validator->fails()) {
		        return response()->json([
				        'success' => false,
				        'errors' => $validator->getMessageBag()->toArray(),
				    ]);
		    }
		   
		   $sender = $settings->email_no_reply;
		   $replyTo = $this->request->email;
		   $user    = $this->request->name;
		   $titleSite = $settings->title;
		   $data = $this->request->message;
		   $_emailUser = $emailUser->email;
		   $_nameUser = $emailUser->name;
		   
		Mail::send('emails.contact-organizer', array( 'data' => $data ), 
		function($message) use ( $sender, $replyTo, $user, $titleSite, $_emailUser, $_nameUser)
			{
			    $message->from($sender, $titleSite)
			    	->to($_emailUser, $_nameUser)
			        ->replyTo($replyTo, $user)
					->subject( $titleSite.' - '.$user );
			});
			
			return response()->json([
				        'success' => true,
				        'msg' => trans('misc.msg_success'),
				    ]);
		
	}// End Method
	
	public function edit($id){
		
		$data = Campaigns::where('id', $this->request->id)
		->where('finalized', '0')
		->where('user_id', Auth::user()->id)
		->firstOrFail();
				
		return view('campaigns.edit')->withData($data);
	}//<---- End Method
	
		public function post_edit() {
		
		$sql = Campaigns::where('id',$this->request->id)->where('finalized','0')->first();

		if( !isset($sql) ) {
			return response()->json([
				        'fatalError' => true,
				         'target' => url('/'),
				    ]); 
					exit;
		}
			
		// PATHS
		$temp            = 'public/temp/';
	    $path_small    = 'public/campaigns/small/'; 
		$path_large   = 'public/campaigns/large/';
		
		// Old images
		$old_small     = $path_small.$sql->small_image;
		$old_large     = $path_large.$sql->large_image;
		
		$image_small  = $sql->small_image;
		$image_large  = $sql->large_image; 
		
		
		$input      = $this->request->all();
		$validator = $this->validator($input,$sql->id);
		
		 if ($validator->fails()) {
	        return response()->json([
			        'success' => false,
			        'errors' => $validator->getMessageBag()->toArray(),
			    ]); 
	    } //<-- Validator
	    
	    if( $this->request->hasFile('photo') )	{
	    	
			$extension    = $this->request->file('photo')->getClientOriginalExtension();
			$file_large     = strtolower(Auth::user()->id.time().str_random(40).'.'.$extension);
			$file_small     = strtolower(Auth::user()->id.time().str_random(40).'.'.$extension);
			
			if( $this->request->file('photo')->move($temp, $file_large) ) {
				
				set_time_limit(0);
				
				//=============== Image Large =================//
				$width  = Helper::getWidth( $temp.$file_large );
				$height = Helper::getHeight( $temp.$file_large );
				$max_width = '800';
				
				if( $width < $height ) {
					$max_width = '600';
				}
				
				if ( $width > $max_width ) {
					$scale = $max_width / $width;
					$uploaded = Helper::resizeImage( $temp.$file_large, $width, $height, $scale, $temp.$file_large );
				} else {
					$scale = 1;
					$uploaded = Helper::resizeImage( $temp.$file_large, $width, $height, $scale, $temp.$file_large );
				}
				
				//=============== Small Large =================//
				Helper::resizeImageFixed( $temp.$file_large, 400, 300, $temp.$file_small );
				
				//======= Copy Folder Small and Delete...
				if ( \File::exists($temp.$file_small) ) {
					\File::copy($temp.$file_small, $path_small.$file_small);
					\File::delete($temp.$file_small);
				}//<--- IF FILE EXISTS
				
				
				//======= Copy Folder Large and Delete...
				if ( \File::exists($temp.$file_large) ) {
					\File::copy($temp.$file_large, $path_large.$file_large);
					\File::delete($temp.$file_large);
				}//<--- IF FILE EXISTS
				
				// Delete Old Images
				\File::delete($old_large);
				\File::delete($old_small);
				
				$image_small  = $file_small;
			   $image_large  = $file_large; 
				
			}
			
	    }//<====== End HasFile
	    
	    if(  isset($this->request->finish_campaign) ) {
	    	$finish_campaign = '1';
			$endCampaign = true;
	    } else {
	    	$finish_campaign = '0';
			$endCampaign = false;
	    }
	    
		$sql->title                = trim($this->request->title);
		$sql->small_image   = $image_small;
		$sql->large_image   = $image_large;
		$sql->description     = trim(Helper::checkTextDb($this->request->description));
		$sql->user_id          = Auth::user()->id;
		$sql->goal               = trim($this->request->goal);
		$sql->location          = trim($this->request->location);
		$sql->finalized          = $finish_campaign;
		$sql->save();
		
		$id_campaign = $sql->id;
	    
	    return response()->json([
				        'success' => true,
				        'target' => url('campaign',$id_campaign),
				        'finish_campaign' => $endCampaign
				        
				    ]);
		
	}//<<--- End Method
	
	public function delete($id) {
		
		$data = Campaigns::where('id', $this->request->id)
		->where('user_id', Auth::user()->id)
		->firstOrFail();
		
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
		 
		 return redirect('/');
				
	}//<<--- End Method
	
	
	public function update($id){
		
		$data = Campaigns::where('id', $this->request->id)
		->where('user_id', Auth::user()->id)
		->firstOrFail();
		
		return view('campaigns.update')->withData($data);
	}//<---- End Method
	
	public function post_update(){
		
		// PATHS
		$temp   = 'public/temp/';
		$path   = 'public/campaigns/updates/';
		
		$sizeAllowed = $this->settings->file_size_allowed * 1024;
		$dimensions = explode('x',$this->settings->min_width_height_image);
		
		$input      = $this->request->all();
		$validator = Validator::make($input, [
			'photo'           => 'mimes:jpg,gif,png,jpe,jpeg|image_size:>='.$dimensions[0].',>='.$dimensions[1].'|max:'.$this->settings->file_size_allowed.'',
            'description'  => 'required|min:20',	        
        ]);
		
		$image = '';
		
		 if ($validator->fails()) {
	        return response()->json([
			        'success' => false,
			        'errors' => $validator->getMessageBag()->toArray(),
			    ]); 
	    } //<-- Validator
	    
	    if( $this->request->hasFile('photo') )	{
	    	
			$extension    = $this->request->file('photo')->getClientOriginalExtension();
			$file     = strtolower(Auth::user()->id.time().str_random(40).'.'.$extension);
			
			if( $this->request->file('photo')->move($temp, $file) ) {
				
				set_time_limit(0);
				
				//=============== Image Large =================//
				$width  = Helper::getWidth( $temp.$file );
				$height = Helper::getHeight( $temp.$file );
				$max_width = '800';
				
				if( $width < $height ) {
					$max_width = '600';
				}
				
				if ( $width > $max_width ) {
					$scale = $max_width / $width;
					$uploaded = Helper::resizeImage( $temp.$file, $width, $height, $scale, $temp.$file );
				} else {
					$scale = 1;
					$uploaded = Helper::resizeImage( $temp.$file, $width, $height, $scale, $temp.$file );
				}
								
				//======= Copy Folder Small and Delete...
				if ( \File::exists($temp.$file) ) {
					\File::copy($temp.$file, $path.$file);
					\File::delete($temp.$file);
				}//<--- IF FILE EXISTS

				$image = $file;
			}			
			
	    }//<====== End HasFile
	    
	    $sql                        = new Updates;
		$sql->image           = $image;
		$sql->description     = trim(Helper::checkTextDb($this->request->description));
		$sql->campaigns_id = $this->request->id;
		$sql->date               = Carbon::now();
		$sql->token_id         = str_random(200);
		$sql->save();
			    	    
	    return response()->json([
				        'success' => true,
				        'target' => url('campaign',$this->request->id),
				    ]);
		
	}//<---- End Method
	
	public function edit_update($id){
			
		$data = Updates::where('id', $id)->firstOrFail();
		
		if(  $data->campaigns()->user_id != Auth::user()->id ){
			abort('404');
		}
		
		return view('campaigns.edit-update')->withData($data);
	}//<---- End Method
	
	public function post_edit_update(){
		
		$sql = Updates::find($this->request->id);
		
		// PATHS
		$temp   = 'public/temp/';
		$path   = 'public/campaigns/updates/';
		
	    $image = $sql->image;
		
		$sizeAllowed = $this->settings->file_size_allowed * 1024;
		$dimensions = explode('x',$this->settings->min_width_height_image);
		
		$input      = $this->request->all();
		$validator = Validator::make($input, [
			'photo'           => 'mimes:jpg,gif,png,jpe,jpeg|image_size:>='.$dimensions[0].',>='.$dimensions[1].'|max:'.$this->settings->file_size_allowed.'',
            'description'  => 'required|min:20',	        
        ]);
				
		 if ($validator->fails()) {
	        return response()->json([
			        'success' => false,
			        'errors' => $validator->getMessageBag()->toArray(),
			    ]); 
	    } //<-- Validator
	    
	    if( $this->request->hasFile('photo') )	{
	    	
			$extension    = $this->request->file('photo')->getClientOriginalExtension();
			$file     = strtolower(Auth::user()->id.time().str_random(40).'.'.$extension);
			
			if( $this->request->file('photo')->move($temp, $file) ) {
				
				set_time_limit(0);
				
				//=============== Image Large =================//
				$width  = Helper::getWidth( $temp.$file );
				$height = Helper::getHeight( $temp.$file );
				$max_width = '800';
				
				if( $width < $height ) {
					$max_width = '600';
				}
				
				if ( $width > $max_width ) {
					$scale = $max_width / $width;
					$uploaded = Helper::resizeImage( $temp.$file, $width, $height, $scale, $temp.$file );
				} else {
					$scale = 1;
					$uploaded = Helper::resizeImage( $temp.$file, $width, $height, $scale, $temp.$file );
				}
								
				//======= Copy Folder Small and Delete...
				if ( \File::exists($temp.$file) ) {
					\File::copy($temp.$file, $path.$file);
					\File::delete($temp.$file);
				}//<--- IF FILE EXISTS
				
				// Delete Old Images
				if( \File::exists($path.$sql->image) ) {
					\File::delete($path.$sql->image);
				}
				
				$image = $file;
			}
			
	    }//<====== End HasFile
	    
		$sql->image        = $image;
		$sql->description = trim(Helper::checkTextDb($this->request->description));
		$sql->save();
			    	    
	    return response()->json([
				        'success' => true,
				        'target' => url('campaign',$sql->campaigns_id),
				    ]);
		
	}//<---- End Method
	
	
	public function delete_image_update(){
		
		$res = Campaigns::where('id', $this->request->id)
		->where('user_id', Auth::user()->id)
		->first();
		
		$path = 'public/campaigns/updates/';
		
		$data = Updates::where('id', $this->request->id)->first();
		
		if( isset( $data ) ) {
			if ( \File::exists($path.$data->image) ) {
					\File::delete($path.$data->image);
				}//<--- IF FILE EXISTS
				
			$data->image = '';
			$data->save();
		}
		
}
	
	
}
