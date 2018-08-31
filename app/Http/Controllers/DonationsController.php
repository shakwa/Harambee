<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\AdminSettings;
use App\Models\Campaigns;
use App\Models\Donations;
use App\Models\User;
use Fahim\PaypalIPN\PaypalIPNListener;
use App\Helper;
use Mail;

class DonationsController extends Controller
{
	public function __construct( AdminSettings $settings, Request $request) {
		$this->settings = $settings::first();
		$this->request = $request;
	}
	
    /**
     *  
     * @return \Illuminate\Http\Response
     */
    public function show($id, $slug = null)
    {
    	
	   $response = Campaigns::where('id',$id)->where('status','active')->firstOrFail();
	   
	   // Redirect if campaign is ended
	   if( $response->finalized == 1 ) {
	   	 return redirect('campaign/'.$response->id);
	   }
		
		$uri = $this->request->path();
		
		if( str_slug( $response->title ) == '' ) {
				$slugUrl  = '';
			} else {
				$slugUrl  = str_slug( $response->title );
			}
			
			$url_image = 'donate/'.$response->id.'/'.$slugUrl;
			
			//<<<-- * Redirect the user real page * -->>>
			$uriImage     =  $this->request->path();
			$uriCanonical = $url_image;
			
			if( $uriImage != $uriCanonical ) {
				return redirect($uriCanonical);
			}
			
		return view('default.donate')->withResponse($response);
    }// End Method
    
    public function send(){
    	
		$messages = array (
		'amount.min' => trans('misc.amount_minimum', ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
	);
	
	$campaign = Campaigns::findOrFail($this->request->_id);
	
		$validator = Validator::make($this->request->all(), [
			'amount' => 'required|integer|min:5',
	        'full_name'     => 'required|max:25',
	        'email'     => 'required|max:100',
	        'country'     => 'required',
	        'postal_code'     => 'required|max:30',
	        'comment'     => 'max:100',
    	],$messages);
		
		if ($validator->fails()) {
		        return response()->json([
				        'success' => false,
				        'errors' => $validator->getMessageBag()->toArray(),
				    ]);
		    }
		
		if ( $this->settings->paypal_sandbox == 'true') {
			// SandBox
			$action = "https://www.sandbox.paypal.com/cgi-bin/webscr";
			} else {
			// Real environment
			$action = "https://www.paypal.com/cgi-bin/webscr";
			}

		$urlSuccess = url('paypal/donation/success',$campaign->id);
		$urlCancel   = url('paypal/donation/cancel',$campaign->id);
		$urlPaypalIPN = url('paypal/ipn');

		return response()->json([
				        'success' => true,
				        'formPP' => '<form id="form_pp" name="_xclick" action="'.$action.'" method="post"  style="display:none">
				        <input type="hidden" name="cmd" value="_donations">
				        <input type="hidden" name="return" value="'.$urlSuccess.'">
				        <input type="hidden" name="cancel_return"   value="'.$urlCancel.'">
				        <input type="hidden" name="notify_url" value="'.$urlPaypalIPN.'">
				        <input type="hidden" name="currency_code" value="'.$this->settings->currency_code.'">
				        <input type="hidden" name="amount" id="amount" value="'.$this->request->amount.'">
				        <input type="hidden" name="custom" value="id='.$campaign->id.'&fn='.$this->request->full_name.'&mail='.$this->request->email.'&cc='.$this->request->country.'&pc='.$this->request->postal_code.'&cm='.$this->request->comment.'&anonymous='.$this->request->anonymous.'">
				        <input type="hidden" name="item_name" value="'.trans('misc.donation_for').' '.$campaign->title.'">
				        <input type="hidden" name="business" value="'.$this->settings->paypal_account.'">
				        <input type="submit">
				        </form>',
				    ]);
		
    	
    }// End Method
    
    public function paypalIpn(){
    	
		$ipn = new PaypalIPNListener();
		
		$ipn->use_curl = false;
		
		if ( $this->settings->paypal_sandbox == 'true') {
			// SandBox
			$ipn->use_sandbox = true;
			} else {
			// Real environment
			$ipn->use_sandbox = false;
			}
			
	    $verified = $ipn->processIpn();
	    
		//$report = Helper::checkTextDb($ipn->getTextReport()); // Report the transation
		
		$custom  = $_POST['custom'];
		parse_str($custom, $donation);
		
		$payment_status = $_POST['payment_status'];
		$txn_id               = $_POST['txn_id'];
		$amount             = $_POST['mc_gross'];
		
	
	    if ($verified) {
	        if($payment_status == 'Completed'){
	          // Check outh POST variable and insert in DB
	          
	          $verifiedTxnId = Donations::where('txn_id',$txn_id)->first();
	        
			if( !isset( $verifiedTxnId ) ) {
				
				$sql = new Donations;
		          $sql->campaigns_id = $donation['id'];
				  $sql->txn_id = $txn_id;
				  $sql->fullname = $donation['fn'];
				  $sql->email = $donation['mail'];
				  $sql->country = $donation['cc'];
				  $sql->postal_code = $donation['pc'];
				  $sql->donation = $amount;
				  $sql->payment_gateway = 'Paypal';
				  $sql->comment = $donation['cm'];
				  $sql->anonymous = $donation['anonymous'];
				  $sql->save();
				  
				  $sender           = $this->settings->email_no_reply;
				  $titleSite          = $this->settings->title;
				  $_emailUser    = $donation['mail'];
				  $campaignID   = $donation['id'];
				  $fullNameUser = $donation['fn'];
				  
				  Mail::send('emails.thanks-donor', array( 'data' => $campaignID, 'fullname' => $fullNameUser, 'title_site' => $titleSite ), 
					function($message) use ( $sender, $fullNameUser, $titleSite, $_emailUser)
						{
						    $message->from($sender, $titleSite)
						    	->to($_emailUser, $fullNameUser)
								->subject( trans('misc.thanks_donation').' - '.$titleSite );
						});
			}// <--- Verified Txn ID
		          
		          
	        } // <-- Payment status
	    } else {
	    	//Some thing went wrong in the payment !
	    }
    	
    }// End Method

    	
}
