<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Job;
use App\Sale;
use App\User;
use App\SalesProfit;
use Carbon\Carbon;

class JobController extends Controller
{
    public function index(Request $request) {
        $jobs = [];
        if(isset($request->filter)) {
            if(isset($request->pending)) {
                $suburbs = Job::where('pending', 1)->distinct('suburb')->select('suburb')->get();
                $suburbId = 0;
                foreach($suburbs as $suburb) {
                    $suburbJobs = Job::where('suburb', $suburb->suburb)->where('pending', 1)->orderby('type', 'asc')->get();
                    foreach($suburbJobs as $suburbJob) {
                        $saleId = Sale::where('job_id', $suburbJob->id)->first()->id;
                        $salesesIds = SalesProfit::where('sale_id', $saleId)->get();
                        $saleses = [];
                        foreach($salesesIds as $salesId) {
                            $sales = User::where('username', $salesId->sales_id)->first();
                            $sales->amount = $salesId->amount;
                            array_push($saleses, $sales);
                            
                        }
                        $suburbJob->saleses = $saleses;
                    }
                    $suburbList = array($suburb->suburb => $suburbJobs);
                    array_push($jobs, $suburbList);
                    $suburbId ++;
                }
                return array(
                    "code" => 200,
                    "info" => "",
                    "data" => $jobs
                );
            }
            $suburbs = Job::distinct('suburb')->select('suburb')->get();
            $suburbId = 0;
            foreach($suburbs as $suburb) {
                $suburbList = array($suburb->suburb => Job::where('suburb', $suburb->suburb)->where('pending', 0)->orderby('type', 'asc')->get());
                array_push($jobs, $suburbList);
                $suburbId ++;
            }

        }
        else {
            if(isset($request->pending)) {
                $jobs = Job::where('pending', 1)->orderby('suburb', 'asc')->orderby('type', 'asc')->get();
                $suburb = '';
                $suburbId = -1;
                foreach($jobs as $job) {
                    if($job->suburb != $suburb) {
                        $suburb = $job->suburb;
                        $suburbId += 1;
                        $job->suburb_id = $suburbId;
                    }
                    else {
                        $job->suburb_id = $suburbId;
                    }
                    $job->delete_date = $job->created_at;
                }
                return array(
                    "code" => 200,
                    "info" => "",
                    "data" => $jobs
                );
            }
            
            $jobs = Job::where('pending', 0)->orderby('suburb', 'asc')->orderby('type', 'asc')->get();
            $suburb = '';
            $suburbId = -1;
            foreach($jobs as $job) {
                if($job->suburb != $suburb) {
                    $suburb = $job->suburb;
                    $suburbId += 1;
                    $job->suburb_id = $suburbId;
                }
                else {
                    $job->suburb_id = $suburbId;
                }
            }

        }
        return array(
            "code" => 200,
            "info" => "",
            "data" => $jobs
        );
    }
    
    public function show(Request $request, $id) {
        return array(
            "code" => 200,
            "info" => "",
            "data" => Job::find($id)
        );
    }
    
    public function store(Request $request) {
        try{
            Job::insert([
                'suburb' => $request->suburb,
                'type' => $request->type,
                'price' => $request->price,
                'name' => $request->name,
                'address' => $request->address,
                'description' => $request->description,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            return array(
                "code" => 200,
                "info" => "",
            );
        }
        catch (\Exception $e) {
            return array(
                "code" => 500,
                "info" => "Add failed"
            );
        }
    }
    
    public function update(Request $request, $id) {
        try {
            $job = Job::find($id);
            $job->name = isset($request->name) ? $request->name : $job->name;
            $job->suburb = isset($request->suburb) ? $request->suburb : $job->suburb;
            $job->address = isset($request->address) ? $request->address : $job->address;
            $job->type = isset($request->type) ? $request->type : $job->type;
            $job->price = isset($request->price) ? $request->price : $job->price;
            $job->description = isset($request->description) ? $request->description : $job->description;
            
            $job->updated_at = Carbon::now();
            $job->save();
        } catch (\Exception $e) {
            return array(
                "info" => "Update failed",
                "code" => 500,
            );
        }
        return array(
            "info" => "",
            "code" => 200,
        );
    }
    
    public function destroy(Request $request, $id) {
        try {
            $job = Job::find($id);

            if($job->pending == 0) {
                $salesIdString = $request->sales_id_string;
                $salesIds = explode(",", $salesIdString);
                for($i = 0; $i < sizeof($salesIds); $i ++) {
                    if(User::where('username', $salesIds[$i])->count() == 0) {
                        return array(
                            "info" => 'Invalid sales included',
                            "code" => 500,
                        );
                    }
                }
                $job->pending = 1;
                $job->amount = $request->amount;
                $job->created_at = \DateTime::createFromFormat('d-M-Y', $request->delete_date);
                $job->save();
                $saleId = Sale::insertGetId([
                    'amount' => $request->amount,
                    'month' => Carbon::now()->format('M-Y'),
                    'job_id' => $id,
                    'approval' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
                
                $salesAmountString = $request->sales_amount_string;
                $salesAmounts = explode(",", $salesAmountString);
                for($i = 0; $i < sizeof($salesIds); $i ++) {
                    SalesProfit::insert([
                        'amount' => $salesAmounts[$i],
                        'sales_id' => $salesIds[$i],
                        'sale_id' => $saleId,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }
            }
            else {
                $job->pending = 2;
                $job->save();
                $sale = Sale::where('job_id', $id)->first();
                $sale->approval = 1;
                $sale->save();
                
                // $job->delete();
            }
            
            
        } catch (\Exception $e) {
            return array(
                "info" => $e,
                "code" => 500,
            );
        }
        return array(
            "info" => "",
            "code" => 200,
        );
    }
}
