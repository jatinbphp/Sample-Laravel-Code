<?php
use App\Flow;
use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'HomeController@index');
Route::any('home/index', 'HomeController@index');

Route::get('home', 'HomeController@index');


Route::get('user/register', 'UserController@register');
Route::post('user/register', 'UserController@register');
Route::get('user/login', 'UserController@login');
Route::post('user/login', 'UserController@login');
Route::get('user/logout', 'UserController@logout');
Route::post('user/changepassword', 'UserController@changepassword');
Route::get('user/detail/{id}', 'UserController@detail');

Route::any('user/profile', [
    'as' => 'user.profile', 'uses' => 'UserController@profile'
]);
Route::post('user/submitprofiletoadmin_ajax', 'UserController@submitprofiletoadmin_ajax');

Route::get('/admin', 'AdminController@index');
Route::get('admin/index', 'AdminController@index');
Route::get('admin/login', 'AdminController@login');
Route::post('admin/login', 'AdminController@login');
Route::get('admin/logout', 'AdminController@logout');
Route::get('admin/userlist', 'AdminController@userlist');
Route::get('admin/flowlist', 'AdminController@flowlist');
Route::get('admin/flowview/{id}', 'AdminController@flowview');
Route::any('admin/grouplist', 'AdminController@grouplist');
Route::get('admin/group_activedeactive/{id}', 'AdminController@group_activedeactive');
Route::get('admin/flow_activedeactive/{id}', 'AdminController@flow_activedeactive');
Route::get('admin/userview/{id}', 'AdminController@userview');
Route::get('admin/groupuser/{id}', 'AdminController@groupuser');
Route::get('admin/verifieduser/{id}', 'AdminController@verifieduser');


Route::get('flow/create', 'FlowController@create');
Route::post('flow/create', 'FlowController@create');
Route::get('flow/mylist', 'FlowController@mylist');
Route::post('flow/edit/{id}', 'FlowController@edit');
Route::any('flow/groupcreate', 'FlowController@groupcreate');
Route::get('flow/detail/{id}', 'FlowController@detail');
Route::post('flow/detail/{id}', 'FlowController@detail');
Route::get('flow/checkjoiningrequest/{id}', 'FlowController@checkjoiningrequest');
Route::get('flow/myrequeststatus', 'FlowController@myrequeststatus');
Route::get('flow/joiningrequest_activedeactive/{id}', 'FlowController@joiningrequest_activedeactive');
Route::get('flow/joiningrequest_fired/{id}/{flow_id}', 'FlowController@joiningrequest_fired');
Route::any('flow/mygrouplist', 'FlowController@mygrouplist');
Route::post('flow/adduserintogroup_ajax', 'FlowController@adduserintogroup_ajax');
Route::get('flow/changejoiningstatus/{status}/{id}', 'FlowController@changejoiningstatus');
Route::get('flow/submitgroup/{id}', 'FlowController@submitgroup');
Route::get('flow/keyword_ajax', 'FlowController@keyword_ajax');


// route to show our edit form
Route::get('flow/edit/{id}', array('as' => 'flow.edit', function(Request $request,$id) {
		
	// return our view and Nerd information
	
		/*
		 * check whether current user is owner of this flow or not.
		*/
		$current_user_id =  Session::get('userid');
		$my_flow_or_not = DB::table('fo_flow')
		->where('user_created',$current_user_id)
		->where('id',$id)
		->count();  
		if($my_flow_or_not==0){
			$request->session()->flash('danger', 'You are not permitted to do this !');												
			return redirect('flow/mylist');
		}
	
	return View::make('flow/edit') // pulls app/views/nerd-edit.blade.php
		->with('flow', Flow::find($id));
}));

Route::post('message/sendmessage_ajax', 'MessageController@sendmessage_ajax');
Route::get('message/inbox', 'MessageController@inbox');
Route::get('message/outbox', 'MessageController@outbox');
Route::post('message/readunreadinbox_ajax', 'MessageController@readunreadinbox_ajax');
Route::post('message/deleteinbox_ajax', 'MessageController@deleteinbox_ajax');
Route::post('message/deleteoutbox_ajax', 'MessageController@deleteoutbox_ajax');
Route::post('message/sendmessagereply_ajax', 'MessageController@sendmessagereply_ajax');


Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);
