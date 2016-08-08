<?php namespace App\Http\Controllers;
use DB;
use Session;
use App\Flow;
use App\User;
use App\Group;
use App\Quotation;
use Illuminate\Http\Request;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;


class AdminController extends Controller {

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
		
	}

	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function index()
	{
		if (!Session::has('admin_username')) {
				return redirect('admin/login');
		}	
		return view('admin/index');
	}

	public function login(Request $request)
	{
		
			if (Session::has('admin_username')) {
					return redirect('admin/index');
			}	
			if ($request->isMethod('post')) {
			$input = $request->all();		
				
			/**
			 * check authentication
			 */
			 
			$valid_login = DB::table('admin')
                    ->where('username', $input['username'])
                    ->Where('password', md5($input['password']))
                    ->first();                    
			
			
			
			if(count($valid_login)==1){
				/**
				 * Store value into database table
				 */
				 
				 Session::put('admin_userid', $valid_login->id);
				 Session::put('admin_username', $valid_login->username);
				 
												
				return redirect('admin/index');
			}else{
				$request->session()->flash('danger', 'Username Email or Password!');
				$request->session()->reflash();
				
				$request->session()->keep(['email']);
				return redirect('admin/login')->withInput();
			}
		}
						
		return view('admin/login');
	}
	
	public function logout()
	{
		/**
		 * release all the sessions of front end
		*/
		Session::forget('admin_userid');
		Session::forget('admin_username');		
		return redirect('admin/login');
	}
	
	public function userlist()
	{	
		
		if (!Session::has('admin_username')) {
				return redirect('admin/login');
		}	
		/**
		 * Disply list of user for admin
		*/
		$alluser = DB::table('fo_user')->get();                    		
		
		return view('admin/user_list',['users' => $alluser]);
	}
	
	public function flowlist()
	{	
		if (!Session::has('admin_username')) {
				return redirect('admin/login');
		}	
		/**
		 * Check list of Flow.
		*/
		
		$allflow = DB::table('fo_flow')
		->select('fo_flow.id','fo_flow.status', 'fo_flow.title','fo_flow.keyword','fo_user.username','fo_flow.flow_date_created')
		->leftJoin('fo_user', 'fo_user.id', '=', 'fo_flow.user_created')
		->get();       						
		
		
		$allflow = DB::table('fo_flow')
		->select('fo_flow.id','fo_flow.title','fo_flow.description','fo_flow.status','fo_flow.keyword','fo_flow.flow_date_created','fo_user.username')		
		->leftJoin('fo_user', 'fo_user.id', '=', 'fo_flow.user_created')		
		->get();

		foreach($allflow as $flow){			

			$group = DB::table('fo_group')->select('fo_group.id as groupid','fo_group.status as status_group','fo_group.maximum_participant')
			->where('fo_group.flow_id',$flow->id)
			->first();   
			
			if(count($group)!=0){
				$flow->maximum_participant = $group->maximum_participant;
				$flow->groupid = $group->groupid;
				$flow->status_group = $group->status_group;
			}else{
				$flow->maximum_participant = 0;
				$flow->groupid = 0;
				$flow->status_group = 0;
			}
		}
				
		return view('admin/flowlist',['all_flow' => $allflow]);
	}
	
	public function flowview($id)
	{	
		
		if (!Session::has('admin_username')) {
				return redirect('admin/login');
		}	
		/**
		 * Flow view detail
		*/
		$flow = DB::table('fo_flow')->select('fo_flow.id', 'fo_flow.title','fo_flow.description','fo_user.username','fo_flow.flow_date_created')->leftJoin('fo_user', 'fo_user.id', '=', 'fo_flow.user_created')->where('fo_flow.id',$id)->first();       						
		
		return view('admin/flowview',['flow' => $flow]);
	}
	
	public function grouplist(Request $request){	
		
		if (!Session::has('admin_username')) {
				return redirect('admin/login');
		}	
		/**
		 * Check list of Flow.
		*/
		
		
		if ($request->isMethod('post')) {
			$input = $request->all();			
			
			$group = Group::find($input['group_id']);				
			$group->status = $input['status'];
			$group->save();						
			$request->session()->flash('success', 'Group Updated successfully !');												
			return redirect('admin/flowlist');			
		}
		
		
		$allgroup = DB::table('fo_group')->select('fo_group.id','fo_flow.title as flow_title','fo_group.status','fo_group.maximum_participant','fo_group.title','fo_group.status','fo_group.maximum_participant','fo_group.created_at','fo_user.username')->leftJoin('fo_user', 'fo_user.id', '=', 'fo_group.user_created')
		->leftJoin('fo_flow', 'fo_flow.id', '=', 'fo_group.flow_id')
		->get();       						
		
		return view('admin/grouplist',['all_group' => $allgroup]);
	}
	
	public function group_activedeactive(Request $request,$id)
	{
		if (!Session::has('admin_username')) {
				return redirect('admin/login');
		}	
		/**
		 * find current status
		*/
		$allgroup = DB::table('fo_group')->where('id',$id)->first();
		$current_status = $allgroup->status;
		if($current_status==0)
			$status = 1;
		else
			$status = 0;
			

		$Group = Group::find($id);	
		$Group->status = $status;
		
		$Group->save();
		$request->session()->flash('success', 'Status successfully changed!');												
		return redirect('admin/grouplist');
		
	}


	public function flow_activedeactive(Request $request,$id)
	{
		if (!Session::has('admin_username')) {
				return redirect('admin/login');
		}	
		/**
		 * find current status
		*/
		$flow = DB::table('fo_flow')->where('id',$id)->first();
		$current_status = $flow->status;
		if($current_status==0)
			$status = 1;
		else
			$status = 0;
			

		$flow_up = Flow::find($id);	
		$flow_up->status = $status;
		
		$flow_up->save();
		$request->session()->flash('success', 'Status successfully changed!');												
		return redirect('admin/flowlist');
		
	}
	public function userview($id)
	{
		if (!Session::has('admin_username')) {
				return redirect('admin/login');
		}	
		/**
		 * Flow view detail
		*/
		$user = DB::table('fo_user')
		->where('id',$id)
		->first();       								
		
		return view('admin/userview',['user' => $user]);		
	}
	
	
	public function groupuser($id)
	{
		/**
		 * find user list who send request for joining to particular flow.
		 */ 

		$group_member = DB::table('fo_group_member')->select('fo_group_member.id','fo_group_member.flow_id','fo_group_member.created_at','fo_group_member.status','fo_user.username','fo_group.title as group_title')->leftJoin('fo_user', 'fo_user.id', '=', 'fo_group_member.user_id')->leftjoin('fo_group','fo_group.id','=','fo_group_member.group_id')
		->where('fo_group_member.group_id',$id)
		->get();    						
		return view('admin/groupuser',['group_member' => $group_member,'gid'=>$id]);
		
	}
	
	public function verifieduser(Request $request,$id)
	{
		/**
		 * Make user as verified
		 */ 
		 
		$user = User::find($id);			
		$user->status = 2;
		$user->save();
		$request->session()->flash('success', 'User verified successfully changed!');												
		return redirect('admin/userlist');
		
	}

}
