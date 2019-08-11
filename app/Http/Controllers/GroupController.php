<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Admin;
use App\Customer;
use App\Cleaner;
use App\Company;
use App\User;
use App\Group;
use App\GroupMember;
use App\History;
use Carbon\Carbon;
use RongCloud\RongCloud;

class GroupController extends Controller
{

    private $appKey = '8luwapkv8j8hl';
    private $appSecret = '0e48uLkCiVxW';

    public function index(Request $request)
    {
        if (isset($request->group_id)) {
            return array(
                "code" => 200,
                "info" => "",
                "data" => Group::find($request->group_id)
            );
        }
        if (isset($request->user_id)) {
            $groups = GroupMember::where('user_id', $request->user_id)->select("group_id")->get();
            $groupIds = [];
            foreach ($groups as $group) {
                array_push($groupIds, $group->group_id);
            }
            return array(
                "code" => 200,
                "info" => "",
                "data" => Group::whereIn('id', $groupIds)->get()
            );
        }
        return array(
            "code" => 200,
            "info" => "",
            "data" => Group::all()
        );
    }

    public function show(Request $request, $id)
    {
        try {
            $group = Group::where('id', $id)->first();
            $groupMembers = GroupMember::where('group_id', $id)->select('user_id', 'type')->get();
			$data = array();
            foreach ($groupMembers as $groupMember) {
				$user = User::where('id', $groupMember->user_id)->where('is_deleted', 0)->first();
                // $groupMember->user_info = User::where('id', $groupMember->user_id)->first();
				if($user != null) {
					$groupMember->user_info = $user;
					array_push($data, $groupMember);
				}
            }
            return array(
                "code" => 200,
                "data" => $data
            );
        } catch (\Exception $e) {
            return array(
                "code" => 1000
            );
        }
    }

    public function getComplaintGroup(Request $request)
    {
        try {
            $userId = $request->user_id;
            $members = [];
            if(isset($request->members)){
                $members = $request->members;
            }
            
            if(isset($request->members_string)) {
                $members = explode(",", $request->members_string);
            }
            $adminIds = Admin::where('accountant', 0)->get();
            $admins = [];
            
            foreach ($adminIds as $adminId) {
                $user = User::find($adminId->admin_id);
                if($user != null) {
                    if($user->is_deleted == 0) {
                        array_push($admins, $user);
                    }
                }
            }
            array_push($admins, User::where('type', 'boss')->first());
            // Add supervisor and cleaner
            $userInfo = User::find($userId);
            $groupName = 'Complaint Group';
            if($userInfo->type == 'customer') {
                $companyId = Customer::where('customer_id', $userInfo->id)->first()->company_id;
                $company = Company::find($companyId);
                array_push($members, $company->supervisor_id);
                array_push($members, $company->cleaner_id);
                $groupName = $company->name;
            }
            else if($userInfo->type == 'admin' || $userInfo->type == 'boss'){
                $firstGroupMember = User::find($members[0]);
                $firstGroupMemberCompanyId = Customer::where('customer_id', $firstGroupMember->id)->first()->company_id;
                $company = Company::find($firstGroupMemberCompanyId);
                $groupName = $company->name;
                array_push($members, $company->supervisor_id);
                array_push($members, $company->cleaner_id);
            }
            $groupId = Group::insertGetId([
                'name' => $groupName,
                'portrait' => isset($request->portrait) ? $request->portrait : 'https://aoxuewang.com.au/clean_coms/public/images/group_icon.png',
                'type' => 'complaint',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
    
            foreach ($admins as $admin) {
                array_push($members, $admin->id);
            }
            
            array_push($members, $userId);
            $members = array_unique($members);
            foreach ($members as $member) {
                if($member == 1) {
                    GroupMember::insert([
                        'group_id' => $groupId,
                        'user_id' => $member,
                        'type' => 1,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }
                else {
                    GroupMember::insert([
                        'group_id' => $groupId,
                        'user_id' => $member,
                        'type' => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }
            }
            $members = array_unique($members);
            $rongCloud = new RongCloud($this->appKey, $this->appSecret);
            $result = $rongCloud->group()->create($members, $groupId, $groupName);
        } catch(\Exception $e) {
            return array(
                "code" => 200,
                "info" => "",
                "data" => []
            );
        }

        return array(
            "code" => 200,
            "info" => "",
            "data" => Group::where('id', $groupId)->first()
        );
    }

    public function getBillingGroup(Request $request)
    {
        try {
            $userId = $request->user_id;
            $members = [];
            if(isset($request->members)){
                $members = $request->members;
            }
            
            if(isset($request->members_string)) {
                $members = explode(",", $request->members_string);
            }
            
            $companyId = Customer::where('customer_id', $userId)->first()->company_id;
            $company = Company::find($companyId);
            $groupName = $company->name . ' (Billing)';
            $accountantId = $company->accountant_id;
            $groupId = Group::insertGetId([
                'name' => $groupName,
                'portrait' => isset($request->portrait) ? $request->portrait : 'https://aoxuewang.com.au/clean_coms/public/images/group_icon.png',
                'type' => 'billing',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            array_push($members, $accountantId);
			array_push($members, Admin::where('accountant', 1)->first()->admin_id;
            array_push($members, $userId);
            foreach($members as $member) {
                GroupMember::insert([
                    'group_id' => $groupId,
                    'user_id' => $member,
                    'type' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
    
            $rongCloud = new RongCloud($this->appKey, $this->appSecret);
            $result = $rongCloud->group()->create($members, $groupId, $groupName);
        } catch(\Exception $e) {
            return array(
                "code" => 200,
                "info" => "",
                "data" => []
            );
        }

        return array(
            "code" => 200,
            "info" => "",
            "data" => Group::find($groupId)
        );
    }

    public function destroy($id)
    {
        $rongCloud = new RongCloud($this->appKey, $this->appSecret);
        $result = $rongCloud->group()->dismiss('1', $id);
    }

    public function getComplainCandidate(Request $request)
    {
        try {
            $userInfo = User::find($request->user_id);
            if ($userInfo->type == 'customer') {
                $companyId = Customer::where('customer_id', $userInfo->id)->first()->company_id;
                $company = Company::find($companyId);
                $selections = [];
                
                $customers = Customer::where('company_id', $company->id)->where('customer_id', '<>', $userInfo->id)->get();
                foreach ($customers as $customer) {
                    if(User::find($customer->customer_id) != null) {
                        array_push($selections, User::find($customer->customer_id));
                    }
                }
                return array(
                    "code" => 200,
                    "info" => "",
                    "data" => $selections
                );
            } 
            if ($userInfo->type == 'admin' || $userInfo->type == 'boss') {
                    $companies = Company::where('is_deleted', 0)->get();;
                    $manegers = [];
                    foreach($companies as $company) {
                        $customerIds = Customer::where('company_id', $company->id)->get();
                        $managers = [];
                        foreach($customerIds as $customerId) {
                            $user = User::find($customerId->customer_id);
                            if($user != null) {
                                if($user->is_deleted == 0) {
                                    array_push($managers, $user);
                                }
                            }
                            
                        }
                        $company->managers = $managers;
                    }
                    return array(
                        "code" => 200,
                        "info" => "",
                        "data" => $companies
                    );
                } 
                else 
                    if ($userInfo->type == 'supervisor') {
                        $selections = [];
                        $companies = Company::where('supervisor_id', $userInfo->id)->where('is_deleted', 0)->get();
                        foreach ($companies as $company) {
                            $customerIds = Customer::where('company_id', $company->id)->get();
                            $managers = [];
                            foreach($customerIds as $customerId) {
                                $user = User::find($customerId->customer_id);
                                if($user != null) {
                                    if($user->is_deleted == 0) {
                                        array_push($managers, $user);
                                    }
                                    
                                }
                            }
                            $company->managers = $managers;
                        }
                        return array(
                            "code" => 200,
                            "info" => "",
                            "data" => $companies
                        );
                    }
                else 
                    if ($userInfo->type == 'cleaner') {
                        $selections = [];
                        $companies = Company::where('cleaner_id', $userInfo->id)->where('is_deleted', 0)->get();
                        foreach ($companies as $company) {
                            $customers = Customer::where('company_id', $company->id)->get();
                            $managers = [];
                            foreach ($customers as $customer) {
                                $user = User::find($customer->customer_id);
                                if($user != null) {
                                    if($user->is_deleted == 0) {
                                        array_push($managers, $user);
                                    }
                                }

                            }
                            $company->managers = $managers;
                        }
                        return array(
                            "code" => 200,
                            "info" => "",
                            "data" => $companies
                        );
                    }
        } catch(\Exception $e) {
            return array(
                "code" => 200,
                "info" => "",
                "data" => []
            );
            
        }
        
    }
    
    public function forecast(Request $request) {
        try {
            $userId = $request->user_id;
            $message = $request->message;
    
            $members = [];
            if(isset($request->types)) {
                foreach($request->types as $type) {
                    $users = User::where('type', $type)->where('is_deleted', 0)->get();
                    foreach($users as $user) {
                        array_push($members, $user->id);
                    }
                    
                }
            }
            
            if(isset($request->types_string)) {
                $types = explode(",", $request->types_string);
                foreach($types as $type) {
                    if($type == 'Manager') {
                        $type = 'customer';
                    }
                    $users = User::where('type', $type)->where('is_deleted', 0)->get();
                    foreach($users as $user) {
                        array_push($members, $user->id);
                    }
                }
                History::insert([
                    'sender_id' => $userId,
                    'content' => $message,
                    'receiver' => $request->types_string,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
            
            $rongCloud = new RongCloud($this->appKey, $this->appSecret);
    	    $result = $rongCloud->message()->publishPrivate($userId, $members, 'RC:TxtMsg',"{\"content\":\"$message\",\"extra\":\"helloExtra\",\"duration\":20}", $message, '{\"pushData\":\"hello\"}', '4', '0', '1', '1', '1');
            return array(
                "code" => 200,
                "info" => ""
            );
        } catch(\Exception $e) {
            return array(
                "code" => 200,
                "info" => "",
                "data" => []
            );
        }
        
    }
    
    public function getBillingCandidate(Request $request)
    {
        try {
            $userInfo = User::find($request->user_id);
            if ($userInfo->type == 'customer') {
                $companyId = Customer::where('customer_id', $userInfo->id)->first()->company_id;
                $company = Company::find($companyId);
                $selections = [];
                $customers = Customer::where('company_id', $company->id)->where('customer_id', '<>', $userInfo->id)->get();
                foreach ($customers as $customer) {
                    array_push($selections, User::find($customer->customer_id));
                }
                return array(
                    "code" => 200,
                    "info" => "",
                    "data" => $selections
                );
            }
            else {
                return array(
                    "code" => 500,
                    "info" => "You are not a customer",
                );
            }
        } catch(\Exception $e) {
            return array(
                "code" => 200,
                "info" => "",
                "data" => []
            );
        }
        
    }
}
