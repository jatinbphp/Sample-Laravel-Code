<?php namespace App\Http\Controllers;
use DB;
use Session;
use App\Quotation;
use App\User;
use View;
use Illuminate\Http\Request;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class UserController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| User Controller
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
	public function register(Request $request)
	{
		
		if ($request->isMethod('post')) {
			$input = $request->all();
			
			/**
			 * email and nickname duplication validation check
			 */
			 
			$duplicate_users = DB::table('fo_user')
                    ->where('username', $input['username'])
                    ->orWhere('email', $input['email'])
                    ->count();
			
			if($duplicate_users==0){
				/**
				 * Store value into database table
				 */
				 DB::table('fo_user')->insert([
				'username' => $input['username'],
				'email' => $input['email'],
				'password' => md5($input['password']),
				]);	
				$request->session()->flash('success', 'You registered successfully with us!');
												
				return redirect('user/register');
			}else{
				$request->session()->flash('danger', 'Username or email is already exists!');
				$request->session()->reflash();
				
				$request->session()->keep(['username', 'email']);
				return redirect('user/register')->withInput();;
			}
		}
		
		return view('users/register');
	}
	
	public function login(Request $request)
	{
		
		if ($request->isMethod('post')) {
			$input = $request->all();
			
			/**
			 * check whether email and password are there in database or not
			 */
			 
			$valid_login = DB::table('fo_user')
                    ->where('email', $input['email'])
                    ->Where('password', md5($input['password']))
                    ->first();                    
			
			
			
			if(count($valid_login)==1){
				/**
				 * Store value into database table
				 */
				 
				 Session::put('userid', $valid_login->id);
				 Session::put('username', $valid_login->username);
				 Session::put('email', $valid_login->email);
												
				return redirect('home');
			}else{
				$request->session()->flash('danger', 'Wrong Username or Password!');
				$request->session()->reflash();
				
				$request->session()->keep(['email']);
				return redirect('user/login')->withInput();
			}
		}
		
		return view('users/login');
	}
	
	public function logout(){
		/**
		 * release all the sessions of front end
		*/
		Session::forget('userid');
		Session::forget('username');
		Session::forget('email');
		return redirect('user/login');
		
	}
	
	public function profile(Request $request)
	{
		/**
		 * change user's profile
		*/
		$current_user_id =  Session::get('userid');
		if (!Session::has('email')) {
			return redirect('user/login');
		}
		if ($request->isMethod('post')) {		
				$input = $request->all();	
				
				 
			$duplicate_users = DB::table('fo_user')			
			->where('email', $input['email'])
			->where('id','<>', $current_user_id)
             ->count();
				
			if($duplicate_users==0){
					
					$user = User::find($current_user_id);						
					$user->email = $input['email'];					
					if($input['password']!="")
						$user->password = md5($input['password']);
						
					$user->firstname = $input['firstname'];
					$user->lastname = $input['lastname'];
					$user->address = $input['address'];
					$user->phone1 = $input['phone1'];
					$user->phone2 = $input['phone2'];
					$user->public = $input['public'];
					$user->date_bom = date("Y-m-d",strtotime($input['date_bom']));
					$user->save();
					$request->session()->flash('success', 'Your Profile updated successfully !');												
					return redirect('user/profile');
			}else{
					$request->session()->flash('danger', 'Email is already exist !');												
					return redirect('user/profile');
			}
		}
		
		return View::make('users/profile') // pulls app/views/nerd-edit.blade.php
		->with('user', User::find($current_user_id));
	}
	
	public function submitprofiletoadmin_ajax()
	{
		/*
		 *  Submit profile to admin
		 * 
		 */
		$current_user_id =  Session::get('userid');
		$user = User::find($current_user_id);			
		$user->status = 1;
		$user->save();
		exit;
	}
		
	public function changepassword(Request $request)
	{
		/*
		 * Change Password
		 * 
		 */
		$current_user_id =  Session::get('userid');
		$input = $request->all();
		if (!Session::has('email')) {
			return redirect('user/login');
		}
		
		$user = User::find($current_user_id);	
		if($input['password']!="")
		$user->password = md5($input['password']);		
		$user->save();
		$request->session()->flash('success', 'Your Password updated successfully !');												
		return redirect('user/profile');
		
	}
	public function detail($id)
	{
		/*
		 * Fetch User details.
		 *  
		 */
		$public_profile = DB::table('fo_user')					
		->select('id','public','username')
		->where('id', $id)
		->first();

		/*
		 * Fetch Flow list where current user is already send request
		 *  
		*/

		$my_request = DB::table('fo_group_member')
		->select('fo_group_member.id','fo_flow.title as flow_title','fo_flow.id as flow_id','fo_group_member.created_at','fo_group_member.status','fo_user.username','fo_group.title as group_title')
		->leftJoin('fo_user', 'fo_user.id', '=', 'fo_group_member.user_id')
		->leftjoin('fo_group','fo_group.id','=','fo_group_member.group_id')
		->leftJoin('fo_flow', 'fo_flow.id', '=', 'fo_group_member.flow_id')
		->where('fo_group_member.status',1)
		->where('fo_group_member.user_id',$id)
		->get();   		

		return view('users/detail',['public'=>$public_profile,'my_request'=>$my_request]); 
		 
	}
	

}
