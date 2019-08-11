<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use RongCloud\RongCloud;
use App\Customer;
use App\User;
use App\Admin;
use App\Cleaner;
use App\AppVariable;
use App\Company;
use App\CleaningCompany;
use Carbon\Carbon;

class UserController extends Controller
{
    private $appKey = '8luwapkv8j8hl';
    private $appSecret = '0e48uLkCiVxW';
    public function index(Request $request) {
        if(isset($request->id)) {
            return array(
                "code" => 200,
                "info" => "",
                "data" => User::where('id', '<>', $request->id)->get(),
            );
        }
        if(isset($request->is_deleted)) {
            if($request->is_deleted == 1) {
                $users = User::where('is_deleted', 1)->whereIn('type', ['customer', 'supervisor'])->orderby('updated_at', 'desc')->get();
                $data = [];
				foreach($users as $user) {
                    $user->operator = User::find($user->operator_id);
                    $user->type = $user->type == 'customer' ? 'manager' : $user->type;
                    $user->name = $user->name . ' (' . $user->type . ')';
					array_push($data, $user);
                }
				
				$companies = Company::where('is_deleted', '<>', 0)->where('delete_reason', '<>', 'lost_job')->orderby('updated_at', 'desc')->get();
				foreach($companies as $company) {
					$company->operator = User::find($company->operator_id);
					$company->type = 'company';
					array_push($data, $company);
				}
                
                return array(
                    "info" => "",
                    "code" => 200,
                    "data" => $data
                );
            }
        }

        return array(
            "code" => 200,
            "info" => "",
            "data" => User::all(),
        );
    }
    
    public function show(Request $request, $id) {
        $userInfo = User::where('id', $id)->where('is_deleted', 0)->first();
        if($userInfo == null){
            return array(
                "code" => 404,
                "info" => "No found"
            );
        }
		
		if($userInfo->type == 'customer') {
			$companyId = Customer::where('customer_id', $id)->first()->company_id;
			$cleaningCompanyId = Company::find($companyId)->cleaning_company_id;
			$userInfo->cleaning_company = CleaningCompany::find($cleaningCompanyId);
		}
		
        return array(
            "code" => 200,
            "info" => "",
            "data" => $userInfo
        );
    }
    
    // 注册
    public function store(Request $request)
    {
        if(User::where('username', $request->username)->where('is_deleted', '0')->count() > 0) {
            return array(
                "info" => "Already exists",
                "code" => 500,
            );
        }
        try {
            $type = isset($request->type) ? $request->type : 'customer';
            $portrait = 'https://aoxuewang.com.au/clean_coms/public/images/admin.png';
            $title = isset($request->title) ? $request->title : '';
            if($type == 'customer') {
                $portrait = 'https://aoxuewang.com.au/clean_coms/public/images/customer.png';
            }
            if($type == 'admin') {
                $portrait = 'https://aoxuewang.com.au/clean_coms/public/images/admin.png';
            }
            if($type == 'supervisor') {
                $portrait = 'https://aoxuewang.com.au/clean_coms/public/images/supervisor.png';
            }
            if($type == 'cleaner') {
                $portrait = 'https://aoxuewang.com.au/clean_coms/public/images/cleaner.png';
            }
            if($type == 'boss') {
                $portrait = 'https://aoxuewang.com.au/clean_coms/public/images/boss.png';
            }
            $ret = User::insertGetId([
                'email' => isset($request->email) ? $request->email : '',
                'phone' => isset($request->phone) ? $request->phone : '',
                'username' => $request->username,
                'password' => $request->type != 'sales' ? Hash::make($request->password) : 'sales',
                'address' => isset($request->address) ? $request->address : '',
                'name' => isset($request->name) ? $request->name : 'No name',
                'portrait' => $portrait,
                'type' => $type,
                'title' => $title,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        } catch (\Exception $e) {
            return array(
                "info" => "Register failed",
                "code" => 1000,
            );
        }
        if ($request->type == 'customer') {
            if(isset($request->company_id)) {
                Customer::insert([
                    'company_id' => $request->company_id,
                    'customer_id' => $ret,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
            return array(
                "info" => "",
                "code" => 200,
            );
        }
        if ($request->type == 'admin') {
            Admin::insert([
                'admin_id' => $ret,
                'accountant' => isset($request->accountant) ? $request->accountant: '0',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            return array(
                "info" => "",
                "code" => 200,
            );
        }
        if ($request->type == 'cleaner') {
            if(isset($request->supervisor_id)) {
                Cleaner::insert([
                    'supervisor_id' => $request->supervisor_id,
                    'cleaner_id' => $ret,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
            
            else {
                Cleaner::insert([
                    'supervisor_id' => 0,
                    'cleaner_id' => $ret,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
            return array(
                "info" => "",
                "code" => 200,
            );
        }
        else {
            return array(
                "info" => "",
                "code" => 200,
            );
        }
        return array(
            "info" => "Register failed",
            "code" => 1000,
        );
    }
    
    public function update(Request $request, $id) {
        $user = User::find($id);
        $user->email = isset($request->email) ? $request->email : $user->email;
        // $user->username = isset($request->username) ? $request->username : $user->username;
        $user->phone = isset($request->phone) ? $request->phone : $user->phone;
        $user->name = isset($request->name) ? $request->name : $user->name;
        $user->address = isset($request->address) ? $request->address : $user->address;
        // $user->password = isset($request->password) ? Hash::make($request->password) : $user->password;
        $user->title = isset($request->title) ? $request->title : $user->title;
        $user->username = isset($request->username) ? $request->username : $user->username;
        $user->updated_at = Carbon::now();
        try {
            $user->save();
            // Update relationships
            if ($user->type == 'cleaner') {
                if(isset($request->supervisor_id)) {
                    $data = Cleaner::where('cleaner_id', $id)->first();
                    $data->supervisor_id = $request->supervisor_id;
                    $data->updated_at = Carbon::now();
                    $data->save();
                }
            }
            
            if ($user->type == 'customer') {
                if(isset($request->company_id)) {
                    $data = Customer::where('customer_id', $id)->first();
                    $data->company_id = $request->company_id;
                    $data->updated_at = Carbon::now();
                    $data->save();
                }
            }
            
            if ($user->type == 'admin') {
                $data = Admin::where('admin_id', $id)->first();
                $data->accountant = isset($request->accountant) ? $request->accountant : $accountantData->accountant;
                $data->updated_at = Carbon::now();
                $data->save();
            }

        } catch (\Exception $e) {
            return array(
                "info" => "修改失败",
                "code" => 500,
            );
        }
        return array(
            "info" => "",
            "code" => 200,
        );
    }
    
    public function destroy(Request $request, $id) {
        if(!isset($request->user_id)) {
            return array(
                "info" => "Unautorized deleting",
                "code" => 500,
            );
        }
        $operator_id = $request->user_id;
        $user = User::where('id', $id)->first();
        $user->is_deleted = 1;
        $user->operator_id = $operator_id;
        $user->updated_at = Carbon::now();
        $user->save();
        $rongCloud = new RongCloud($this->appKey, $this->appSecret);
        $userOperation = $rongCloud->user();
        $userOperation->block($id, '43200');
        // $userOperation->unblock($id);
        return array(
            "info" => "",
            "code" => 200,
        );
    }
    
    // 登录并返回用户token
    public function login(Request $request)
    {
        if (User::where('username', $request->username)->where('is_deleted', 0)->count() == 0) {
            return array(
                "code" => 1000,
                "info" => 'Invalid username or password',
            );
        } else {
            $userInfo = User::where('username', $request->username)->first();
                
            if (Hash::check($request->password, $userInfo->password)) {
                $rongCloud = new RongCloud($this->appKey, $this->appSecret);

                $user = $rongCloud->user();
                // $userInfo = User::where('username', $request->username)->first();
                $token1 = explode("token\":\"", $user->getToken($userInfo->id, $userInfo->name, $userInfo->portrait))[1];
                $token2 = explode("\"}", $token1)[0];
                $userInfo->token = $token2;
                $userInfo->identity_token = Hash::make($userInfo->type);
                if($userInfo->type == 'admin') {
                    if(Admin::where('admin_id', $userInfo->id)->first()->accountant == 1) {
                        $userInfo->is_accountant = 1;
                    }
                    else {
                        $userInfo->is_accountant = 0;
                    }
                }
                
                if($userInfo->type == 'customer') {
                    try {
                        $companyId = Customer::where('customer_id', $userInfo->id)->first()->company_id;
                        $cleaningCompanyId = Company::find($companyId)->cleaning_company_id;
                        $userInfo->cleaning_company_phone = CleaningCompany::find($cleaningCompanyId)->phone;
                    } catch (\Exception $e) {
                        $userInfo->cleaning_company_phone = 'No phone';
                    }

                }
                return array(
                    "code" => 200,
                    "info" => "",
                    "data" => $userInfo,
                );
            } else
                return array(
                    "code" => 1000,
                    "info" => 'Invalid username or password',
                );
        }
            return array(
                "code" => 1000,
                "info" => 'Connection failed',
            );
    }
    
    public function getUserCount() {
        $count = AppVariable::first()->multiplicative_count;
        return array(
            "code" => 200,
            "info" => "",
            "data" => User::where('type', 'customer')->where('is_deleted', 0)->count() * $count,
        );
    }
    
    public function changePassword(Request $request) {
        $user = User::where('id', $request->id)->first();
        $user->password = Hash::make($request->password);
        $user->updated_at = Carbon::now();
        try {
            $user->save();
        } catch (\Exception $e) {
            return array(
                "code" => 1000,
            );
        }
        return array(
            "code" => 200,
            "info" => "",
        );
    }
    
    public function getMainPageVariables(Request $request) {
        $data = AppVariable::first();
        $count = round(User::where('type', 'customer')->where('is_deleted', 0)->count() * $data->multiplicative_count);
        $banners = array();
        array_push($banners, $data->banner_one);
        array_push($banners, $data->banner_two);
        array_push($banners, $data->banner_three);
        $data = array(
                "banners" => $banners,
                "count" => $count
            );
        if(isset($request->user_id)) {
            if(User::find($request->user_id)->type == 'customer') {
                $companyId = Customer::where('customer_id', $request->user_id)->first()->company_id;
                $company = Company::find($companyId);
                $data = array(
                    "banners" => $banners,
                    "count" => $count,
                    "inspection" => $company->inspection
                );
            }
        }

        return array(
            "code" => 200,
            "info" => "",
            "data" => $data
        );
    }
    
    
    
}
