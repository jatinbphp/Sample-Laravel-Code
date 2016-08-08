<?php namespace App\Http\Controllers;
use DB;
use App\Flow;
use App\Group;
use App\Post;
use App\Groupmember;
use App\Messageinbox;
use App\Messageoutbox;
use App\Message;
use App\Messagerecipient;
use Session;
use App\Quotation;
use Illuminate\Http\Request;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class MessageController extends Controller {

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
	
	public function inbox()
	{		
		/**
		 *  List of user's inbox messages
		*/
		if (!Session::has('email')) {
				return redirect('user/login');
		}
		
		$current_user_id =  Session::get('userid');	
		$inbox = DB::table('fo_user_inbox')->select('fo_user_inbox.id as fo_user_inbox_primary_key','fo_user_inbox.is_read','fo_user_inbox.message_id','fo_user_inbox.created_at','fo_message.text','fo_message.user_sender','fo_user.username as sender')
		->leftjoin('fo_message','fo_message.id','=','fo_user_inbox.message_id')
		->leftJoin('fo_user', 'fo_user.id', '=', 'fo_message.user_sender')		
		->where('fo_user_inbox.user_id',$current_user_id)
		->where('fo_user_inbox.is_deleted',0)
		->orderBy('fo_user_inbox.created_at','desc')
		->get();    							
		
		return view('message/inbox',['inbox' => $inbox]);
		
	}
	
	public function outbox()
	{		
		/**
		 *  List of user's outbox messages
		*/
		if (!Session::has('email')) {
				return redirect('user/login');
		}
		
		$current_user_id =  Session::get('userid');	
		$outbox = DB::table('fo_user_outbox')->select('fo_user_outbox.id as fo_user_outbox_primary_key','fo_user_outbox.message_id','fo_message_recipient.user_recipient','fo_user_outbox.created_at','fo_message.text','fo_message.user_sender','fo_user.username as recipient')
		->leftjoin('fo_message','fo_message.id','=','fo_user_outbox.message_id')
		->leftJoin('fo_message_recipient', 'fo_message_recipient.message_id', '=', 'fo_user_outbox.message_id')				
		->leftJoin('fo_user', 'fo_user.id', '=', 'fo_message_recipient.user_recipient')						
		->where('fo_user_outbox.user_id',$current_user_id)
		->where('fo_user_outbox.is_deleted',0)
		->orderBy('fo_user_outbox.created_at','desc')
		->get();    					
		return view('message/outbox',['outbox' => $outbox]);
		
	}
	
	public function readunreadinbox_ajax(Request $request)
	{
		/**
		 *  Mark message as read unread
		*/
		$input = $request->all();	
		$current_status = $input['status'];
		if($current_status==1)
			$status = 0;
		else
			$status = 1;
			
		$input = $request->all();	
		$Messageinbox = Messageinbox::find($input['id']);		
		$Messageinbox->is_read = $status;				
		$Messageinbox->save();
		
		$current_user_id =  Session::get('userid');
		echo $unread_inbox = DB::table('fo_user_inbox')
		->where('is_read',0)
		->where('user_id',$current_user_id)
		->count();
		
		exit;

	}
	public function deleteinbox_ajax(Request $request)
	{
		/**
		 *  Mark message as delete
		*/
		$input = $request->all();				
		
		$Messageinbox = Messageinbox::find($input['id']);		
		$Messageinbox->is_deleted = 1;				
		$Messageinbox->save();
		echo "1";exit;
	}
	
	public function deleteoutbox_ajax(Request $request)
	{
		/**
		 *  Mark message as delete
		*/
		$input = $request->all();				
		
		$Messageoutbox = Messageoutbox::find($input['id']);		
		$Messageoutbox->is_deleted = 1;				
		$Messageoutbox->save();
		echo "1";exit;		
	}
	
	public function sendmessagereply_ajax(Request $request)
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

}
