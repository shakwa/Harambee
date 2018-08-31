<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminSettings;
use App\Models\Campaigns;
use App\Models\Categories;

class HomeController extends Controller
{

    /**
     *  
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       
	   $settings = AdminSettings::first();
       $data      = Campaigns::orderBy('id','DESC')->paginate($settings->result_request);
		
		return view('index.home')->withData($data);
    }
	
	public function search(Request $request) {

		$q = trim($request->input('q'));
		$settings = AdminSettings::first();
		
		$page = $request->input('page');
		
		$data = Campaigns::where( 'title','LIKE', '%'.$q.'%' )
		->where('status', 'active' )
		->orWhere('location','LIKE', '%'.$q.'%')
		->where('status', 'active' )
		->groupBy('id')
		->orderBy('id', 'desc' )
		->paginate( $settings->result_request );

		
		$title = trans('misc.result_of').' '. $q .' - ';
		
		$total = $data->total();
		
		//<--- * If $page not exists * ---->
		if( $page > $data->lastPage() ) {
			abort('404');
		}
		
		//<--- * If $q is empty or is minus to 1 * ---->
		if( $q == '' || strlen( $q ) <= 1 ){
			return redirect('/');
		}
		
		return view('default.search', compact( 'data', 'title', 'total', 'q' ));
		
	}// End Method
	
	public function category($slug) {
		
		$settings = AdminSettings::first();
			
		 $category = Categories::where('slug','=',$slug)->first();
	  	 $data       = Campaigns::where('status', 'active')->where('categories_id',$category->id)->orderBy('id','DESC')->paginate($settings->result_request);
				
		return view('default.category', ['data' => $data, 'category' => $category]);
		
	}// End Method
}
