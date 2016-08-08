<?php namespace App\Http\Controllers;
use DB;
use App\Flow;
use App\Group;
use App\Post;
use App\Groupmember;
use App\Messageinbox;
use App\Messageoutbox;
use App\Message;
use App\Keyword;
use App\Flowcandidate;
use App\Messagerecipient;
use Session;
use App\Quotation;
use Illuminate\Http\Request;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class FlowController extends Controller {

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
	}

	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function create(Request $request)
	{
		
		if (!Session::has('email')) {
				return redirect('user/login');
		}
		
		if ($request->isMethod('post')) {
			$input = $request->all();		
			/**
			 * Store value into database table
			 */
			 $current_user_id =  Session::get('userid');
			 $flow = DB::table('fo_flow')->insertGetId([
			'title' => $input['title'],
			'description' => $input['description'],
			'keyword' => $input['keyword'],
			'url' => $input['url'],
			'user_created' => $current_user_id,
			'flow_date_created' => date('Y-m-d d:h:i'),
			]);	
			/*
			 * check whether keyword is new or not
			 */
			$keywords = explode(',',$input['keyword']);
			$search_result =DB::table('fo_keyword')->get();
			foreach($search_result as $k){
				$keywordarray[] = $k->keyword;
			}
			for($i=0;$i<count($keywords);$i++){
				if(!in_array($keywords[$i],$keywordarray)){
					$Keyword = new Keyword;	
					$Keyword->keyword = $keywords[$i];					
					$Keyword->save();
				}
			}
			$group = new Group;	
			$group->title = $input['title'];
			$group->flow_id = $flow;
			$group->user_created = $current_user_id;
			$group->maximum_participant = 25;
			
			$group->status = 0;
			$group->save();
			
			
			$request->session()->flash('success', 'Your Flow sent to admin for aprooval successfully !');												
			return redirect('flow/mylist');
			
		}
		
		return view('flow/create');
	}
	
	public function edit(Request $request,$id)
	{
		
		if (!Session::has('email')) {
				return redirect('user/login');
		}
				
		if ($request->isMethod('post')) {
			$input = $request->all();					
			/**
			 * Store value into database table
			 */
			$flow = Flow::find($id);	
			$flow->title = $input['title'];
			$flow->keyword = $input['keyword'];
			$flow->url = $input['url'];
			$flow->description = $input['description'];
			$flow->save();
			/*
			* check whether keyword is new or not
			*/
			$keywords = explode(',',$input['keyword']);
			$search_result =DB::table('fo_keyword')->get();
			foreach($search_result as $k){
				$keywordarray[] = $k->keyword;
			}
			for($i=0;$i<count($keywords);$i++){
				if(!in_array($keywords[$i],$keywordarray)){
					$Keyword = new Keyword;	
					$Keyword->keyword = $keywords[$i];					
					$Keyword->save();
				}
			}
			
			
			$request->session()->flash('success', 'Your Flow updated successfully !');												
			return redirect('flow/mylist');
			
		}
		
	}		
	
	public function mylist()
	{
		
		if (!Session::has('email')) {
			return redirect('user/login');
		}
		/**
		 * List of current logged in user's flow list from where he can edit it.
		 */ 
		$current_user_id =  Session::get('userid');
		$my_flow = DB::table('fo_flow')
		->select('fo_flow.id','fo_flow.title','fo_flow.description','fo_flow.status','fo_flow.flow_date_created')
		->leftJoin('fo_group', 'fo_flow.id', '=', 'fo_group.flow_id')
		->where('fo_flow.user_created',$current_user_id)
		->get();

		foreach($my_flow as $flow){
			$flow->total_participant = DB::table('fo_group_member')->select('fo_group_member.id','fo_group_member.flow_id','fo_group_member.created_at','fo_group_member.status','fo_user.username','fo_group.title as group_title')->leftJoin('fo_user', 'fo_user.id', '=', 'fo_group_member.user_id')->leftjoin('fo_group','fo_group.id','=','fo_group_member.group_id')
			->where('fo_group_member.flow_id',$flow->id)
			->count();   

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

		
		
		return view('flow/myflow',['myflow' => $my_flow]);
	}
	
	public function groupcreate(Request $request)
	{
		if (!Session::has('email')) {
				return redirect('user/login');
		}
		/**
		 * Create new group
		 */ 
		 
		
		$current_user_id =  Session::get('userid'); 
		if ($request->isMethod('post')) {
			$input = $request->all();					
			/**
			 * Store value into database table
			 */
			$group = new Group;	
			$group->title = $input['group_title'];
			$group->flow_id = $input['flow_id'];
			$group->user_created = $current_user_id;
			$group->maximum_participant = $input['maximum_participant'];
			$group->status = 0;
			$group->save();
			$request->session()->flash('success', 'Your Group Sent to Admin for Approve. !');												
			return redirect('flow/groupcreate');
			
		}
		
		$flow = DB::table('fo_flow')
		->where('user_created',$current_user_id)
		->get();   
		return view('flow/groupcreate', compact('flow',$flow));
				
	}
	
	public function mygrouplist()
	{
		
		if (!Session::has('email')) {
				return redirect('user/login');
		}
		/**
		 * current login user's group list
		 */ 
		
		$current_user_id =  Session::get('userid');
		$my_group = DB::table('fo_group')->select('fo_group.id','fo_flow.title as flow_title','fo_group.flow_id','fo_group.status','fo_group.maximum_participant','fo_group.title','fo_group.status','fo_group.maximum_participant','fo_group.created_at','fo_user.username')->leftJoin('fo_user', 'fo_user.id', '=', 'fo_group.user_created')
		->leftJoin('fo_flow', 'fo_flow.id', '=', 'fo_group.flow_id')
		->where('fo_group.user_created',$current_user_id)
		->get();    
		
		return view('flow/mygroup',['my_group' => $my_group]);
	}
	
	public function detail(Request $request,$id)
	{
		
		if (!Session::has('email')) 
		{
				//return redirect('user/login');
		}
		$current_user_id =  Session::get('userid');
		if ($request->isMethod('post'))
		{
			$input = $request->all();					
			/**
			 * Store value into database table
			 */
			$post =new Post;	
			$post->flow_id = $id;
			$post->parent_id = '0';
			$post->text = $input['comment'];
			$post->user_created = $current_user_id;			
			$post->save();
			$request->session()->flash('success', 'Your Comment Added successfully !');												
			return redirect('flow/detail/'.$id);
			
		}
		
		/**
		 * fetch detail of flow
		 */ 		 
		$flow = DB::table('fo_flow')->select('fo_flow.id','fo_flow.url','fo_flow.status','fo_flow.keyword','fo_flow.title','fo_flow.description','fo_user.username','fo_user.id as userid','fo_flow.flow_date_created','fo_flow.user_created')->leftJoin('fo_user', 'fo_user.id', '=', 'fo_flow.user_created')->where('fo_flow.id',$id)->first();       								
		if($flow->status==0){
			$request->session()->flash('danger', 'You are not permitted to do this !');												
			return redirect('/');
		}
		
		/**
		 * fetch comment of current flow
		 */ 
		
		$flow_comments = DB::table('fo_post')->select('fo_post.text','fo_post.created_at','fo_user.username','fo_user.id as userid')->leftJoin('fo_user', 'fo_user.id', '=', 'fo_post.user_created')->where('fo_post.flow_id',$id)->get();       								
		
		/**
		 * fetch current flow's group
		 */ 
		
		$flow_group = DB::table('fo_group')->select('fo_group.id','fo_flow.title as flow_title','fo_group.status','fo_group.maximum_participant','fo_group.title','fo_group.status','fo_group.maximum_participant','fo_group.created_at','fo_user.username')->leftJoin('fo_user', 'fo_user.id', '=', 'fo_group.user_created')
		->leftJoin('fo_flow', 'fo_flow.id', '=', 'fo_group.flow_id')		
		->where('fo_group.flow_id',$id)
		->where('fo_group.status',0)
		->first();    
		
		
		/**
		 * fetch current flow's accepted user
		 */ 
		if(!empty($current_user_id)){
			$flow_accepted_user = DB::table('fo_group_member')->select('fo_group_member.user_id','fo_user.username')->leftJoin('fo_user', 'fo_user.id', '=', 'fo_group_member.user_id')		
			->where('fo_group_member.flow_id',$id)
			->where('fo_group_member.status',1)
			->where('fo_group_member.user_id','<>',$current_user_id)
			->get();    	
		}else{
			$flow_accepted_user = array();
		}
		
		/*
		 *  List of requester
		 */
		
		$group_member = DB::table('fo_group_member')->select('fo_group_member.id','fo_group_member.flow_id','fo_group_member.created_at','fo_group_member.status','fo_user.username','fo_user.id as userid','fo_group.title as group_title')->leftJoin('fo_user', 'fo_user.id', '=', 'fo_group_member.user_id')->leftjoin('fo_group','fo_group.id','=','fo_group_member.group_id')
		->where('fo_group_member.flow_id',$id)
		->get();    									
		
		
		/**
		 * fetch schema
		 */ 
		
		$schema = DB::table('fo_schema')		
		->get();    
		
		return view('flow/detail',['flow' => $flow,'flow_comments'=>$flow_comments,'flow_group'=>$flow_group,'flow_accepted_user'=>$flow_accepted_user,'group_member' => $group_member,'schema'=>$schema]);
	}
	
	public function adduserintogroup_ajax(Request $request)
	{
		/**
		 * add participant request to table
		 */ 		 	 
		 
		$current_user_id =  Session::get('userid');
		
		/**
		 * already send request or not
		 */ 
		 $input = $request->all();	
		 
		 $myrequest = DB::table('fo_group_member')
		->where('user_id',$current_user_id)
		->where('group_id',$input['groupid'])
		->where('flow_id',$input['flowid'])
		->count();  
		if($myrequest==0){
					
			$group_member = new Groupmember;
			$group_member->group_id = $input['groupid'];
			$group_member->flow_id = $input['flowid'];
			$group_member->user_id = $current_user_id;
			$group_member->status = 0;
			$group_member->save();
			
			$Flowcandidate = new Flowcandidate;
			$Flowcandidate->prop_schema_id = $input['prop_schema_id'];
			$Flowcandidate->prop_location = $input['prop_location'];
			$Flowcandidate->prop_business_enabled = $input['prop_business_enabled'];
			$Flowcandidate->prop_owners = $input['number_of_owner'];
			$Flowcandidate->group_member_id = $group_member->id;	
			$Flowcandidate->save();
			
			echo "1";
			exit;
		}else{
			echo "0";
			exit;
		}
	}
	
	public function checkjoiningrequest($id)
	{
		/**
		 * find user list who send request for joining to particular flow.
		 */ 

		$group_member = DB::table('fo_group_member')
		->select('fo_group_member.id','fo_group_member.flow_id','fo_group_member.created_at','fo_group_member.status','fo_user.username','fo_group.title as group_title','fo_group.status as group_status','fo_flow_candidate.prop_schema_id','fo_flow_candidate.prop_location','fo_flow_candidate.prop_business_enabled','fo_flow_candidate.prop_owners')
		->leftJoin('fo_user', 'fo_user.id', '=', 'fo_group_member.user_id')
		->leftjoin('fo_group','fo_group.id','=','fo_group_member.group_id')
		->leftjoin('fo_flow_candidate','fo_group_member.id','=','fo_flow_candidate.group_member_id')		
		->where('fo_group_member.flow_id',$id)
		->get();    
		
		return view('flow/checkjoiningrequest',['group_member' => $group_member]);
		
	}
	
	public function myrequeststatus()
	{		
		if (!Session::has('email')) 
		{
				return redirect('user/login');
		}
		/**
		 * Fetch data of logged in user's request.
		*/ 		 
		$current_user_id =  Session::get('userid'); 
		$my_request = DB::table('fo_group_member')
		->select('fo_group_member.id','fo_flow.title as flow_title','fo_group_member.created_at','fo_group_member.status','fo_user.username','fo_group.title as group_title','fo_group.status as group_status','fo_flow_candidate.prop_schema_id','fo_flow_candidate.prop_location','fo_flow_candidate.prop_business_enabled','fo_flow_candidate.prop_owners')
		->leftJoin('fo_user', 'fo_user.id', '=', 'fo_group_member.user_id')
		->leftjoin('fo_group','fo_group.id','=','fo_group_member.group_id')
		->leftJoin('fo_flow', 'fo_flow.id', '=', 'fo_group_member.flow_id')
		->leftjoin('fo_flow_candidate','fo_group_member.id','=','fo_flow_candidate.group_member_id')		
		->where('fo_group_member.user_id',$current_user_id)
		->get();   		
		return view('flow/myrequeststatus',['my_request' => $my_request]);
		
	}
	
	public function joiningrequest_activedeactive(Request $request ,$id)
	{
		/**
		 * change status of joining request
		 */ 
			if (!Session::has('email')) 
			{
					return redirect('user/login');
			}
		/**
		 * find current status
		*/
		$joiningstatus = DB::table('fo_group_member')->where('id',$id)->first();
		$current_status = $joiningstatus->status;
		if($current_status==0)
			$status = 1;
		else
			$status = 0;
			

		$Groupmember = Groupmember::find($id);	
		$Groupmember->status = $status;
		
		$Groupmember->save();
		$request->session()->flash('success', 'Status successfully changed!');												
		return redirect('flow/checkjoiningrequest/'.$joiningstatus->flow_id);
		
	}
	
	public function joiningrequest_fired(Request $request,$id,$flow_id)
	{
		
		/**
		 * fired(delete) joining request
		 */ 
		$Groupmember = Groupmember::find($id);	
		$Groupmember->status = '2';
		
		$Groupmember->save();
		$request->session()->flash('success', 'Request fired successfully!');												
		return redirect('flow/checkjoiningrequest/'.$flow_id);
		
	}
	
	public function sendmessage_ajax(Request $request)
	{
		/**
		 * Send Message to user
		 */ 		 	 
		 
		$current_user_id =  Session::get('userid');		
		$input = $request->all();	

		$Message = new Message;
		$Message->user_sender = $current_user_id;
		$Message->text = $input['message'];	
		$Message->save();
		$message_id = $Message->id;
		
		$Messagerecipient = new Messagerecipient;
		$Messagerecipient->message_id = $message_id;
		$Messagerecipient->user_recipient = $input['user_id'];			
		$Messagerecipient->save();
		
		$Messageinbox = new Messageinbox;
		$Messageinbox->user_id = $input['user_id'];
		$Messageinbox->message_id = $message_id;
		$Messageinbox->is_deleted = 0;
		$Messageinbox->is_read = 0;				
		$Messageinbox->save();
		
		$Messageoutbox = new Messageoutbox;
		$Messageoutbox->user_id = $current_user_id;
		$Messageoutbox->message_id = $message_id;
		$Messageoutbox->is_deleted = 0;
		$Messageoutbox->is_read = 0;				
		$Messageoutbox->save();
				
		echo "1";
		exit;
		
	}
	public function changejoiningstatus(Request $request,$status,$id)
	{
			/*
			*  Change Status
			*/
			$group_member = Groupmember::find($id);
			$group_member->status = $status;
			$group_member->save();
			$request->session()->flash('success', 'Status successfully changed!');												
			return redirect('flow/myrequeststatus');
		
	}
	public function submitgroup(Request $request, $id)
	{
			/*
			*  Submit Group
			* Check whethere atleast one user is accepted or not
			*/		
			
			$activegroupmember = DB::table('fo_group_member')
			->where('group_id',$id)
			->where('status',1)			
			->count();
			if($activegroupmember==0){			
				$request->session()->flash('danger', 'To submit group, you need to approve atleast one user!');												
				return redirect('flow/mylist');				
			}
			
			$group = Group::find($id);				
			$group->status = 1;
			$group->save();
			$request->session()->flash('success', 'Group Submitted to admin!');												
			return redirect('flow/mylist');			
	}
	
	public function keyword_ajax(Request $request)
	{
		$input = $request->all();	
		$term = explode(',',trim($input['term']));
		$myterm = $term[count($term)-1];
		//echo $myterm;
		$return_arr = array();
		$search_result =DB::table('fo_keyword')			
		->where('keyword', 'like', '%'.$myterm.'%')				
		->get();
		
		foreach($search_result as $keyword){
			$return_arr[] =  $keyword->keyword;
		}
		echo json_encode($return_arr);exit;
	}

}
