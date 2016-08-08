<?php namespace App\Http\Controllers;
use DB;
use Session;
use App\Quotation;
use Illuminate\Http\Request;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;


class HomeController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Home Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders your application's "dashboard" for users that
	| are authenticated. Of course, you are free to change or remove the
	| controller as you wish. It is just here to get your app started!
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		//$this->middleware('auth');
		if (!Session::has('email')) {
			return redirect('user/login');
		}
	}

	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
			if (!Session::has('email')) {
			//	return redirect('user/login');
			}
			/*
			 *  Search record in from home page
			 * 
			*/
			if ($request->isMethod('post')) {
				$input = $request->all();

				$search_term = $input['search'];

				$search_result =DB::table('fo_flow')
				->select('fo_flow.id','fo_flow.title','fo_flow.description','fo_user.username','fo_flow.keyword','fo_user.id as userid','fo_user.username as userstatus','fo_flow.flow_date_created')
				->leftJoin('fo_user', 'fo_user.id', '=', 'fo_flow.user_created')
				->where('fo_flow.status', '=', 1)
				->Where(function ($query) use ($search_term) {
				$query->where('title', 'like', '%'.$search_term.'%')
				->orWhere('fo_flow.keyword', 'like', '%'.$search_term.'%')
				->orWhere('description', 'like', '%'.$search_term.'%');
				})
				->get(); 				
				return view('home',['all_flow' => $search_result,'search_term'=>$search_term]);	
									
			}
			
			/*
			 *  Find list of all the Flow
			 * 
			*/
		$all_flow = DB::table('fo_flow')->select('fo_flow.id','fo_flow.title','fo_flow.keyword','fo_flow.description','fo_user.username','fo_user.id as userid','fo_user.status as userstatus','fo_flow.flow_date_created')
		->leftJoin('fo_user', 'fo_user.id', '=', 'fo_flow.user_created')->where('fo_flow.status',1)->get();       						
		return view('home',['all_flow' => $all_flow,'search_term'=>'']);

	}

}
