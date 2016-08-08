<?php namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Database\Eloquent\Model;
use DB;
use Session;
abstract class Controller extends BaseController {

	use DispatchesCommands, ValidatesRequests;
	public function __construct()
	{
		
		//$this->middleware('auth');
		 $current_user_id =  Session::get('userid');
		 $unread_inbox = DB::table('fo_user_inbox')
		->where('is_read',0)
		->where('user_id',$current_user_id)
		->count();

		$total_untouched_request = DB::table('fo_group_member')		
		->leftJoin('fo_flow', 'fo_flow.id', '=', 'fo_group_member.flow_id')				
		->where('user_created',1)
		->where('fo_group_member.status',0)
		->count();    

		Session::put('total_untouched_request', $total_untouched_request);		
		Session::put('unread_inbox', $unread_inbox);
	}
}
