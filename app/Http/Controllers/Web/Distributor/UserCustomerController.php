<?php
namespace App\Http\Controllers\Web\Distributor;

use App\Form\DistributorCustomer;
use App\Http\Controllers\Controller;
use App\Models\DistributorSrCustomer;
use Illuminate\Http\Request;

class UserCustomerController extends Controller {
    public function loadCustomers(Request $request){
        $sr = $request->input('sr');

        $customers = DistributorSrCustomer::with('distributorCustomer')->where('u_id',$sr)->get();

        $customers->transform(function($distributorSrCustomer){
            if(!$distributorSrCustomer->distributorCustomer)
                return null;

            return [
                'value'=>$distributorSrCustomer->distributorCustomer->getKey(),
                'label'=>$distributorSrCustomer->distributorCustomer->dc_name,
            ];
        });

        $customers = $customers->filter(function($customer){
            return !!$customer;
        });

        return $customers;
    }

    public function save(Request $request){
        $customers = $request->input('customers');
        $srs = $request->input('srs');


        foreach ($srs as $key => $sr) {
            DistributorSrCustomer::where('u_id',$sr['value'])->delete();
            foreach ($customers as $key => $customer) {


                DistributorSrCustomer::create([
                    'u_id'=>$sr['value'],
                    'dc_id'=>$customer['value']
                ]);
            }
        }

        return [
            'success'=>true,
            'message'=>"You have successfully allocated customers."
        ];
    }
}