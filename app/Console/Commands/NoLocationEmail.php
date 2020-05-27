<?php

namespace App\Console\Commands;

use App\Models\EmailSelectedType;
use App\Models\GPSTracking;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class NoLocationEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:no_location';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncronizing data from their tables';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::whereIn('u_tp_id',[
            config('shl.medical_rep_type'),
            config('shl.field_manager_type'),
            config('shl.product_specialist_type')
        ])->get();

        $gpsNoUsers = [];

        foreach ($users as $key => $user) {

            $lastLocation = GPSTracking::where('u_id',$user->getKey())
                ->latest()
                ->first();

            $time = time();

            if( isset($lastLocation) && $time - strtotime($lastLocation->gt_time) > 60*60 ){
                $gpsNoUsers[] = [
                    'name'=> $user->name,
                    'code'=> $user->code,
                    'mobile'=> $user->contact_no,
                    'last_location'=> [
                        'lat'=> $lastLocation->gt_lat,
                        'lng'=> $lastLocation->gt_lon,
                        'time'=> $lastLocation->gt_time,
                        'batry'=> $lastLocation->gt_btry
                    ]
                ];
            }else if(!isset($lastLocation)){
                $gpsNoUsers[] = [
                    'name'=> $user->name,
                    'code'=> $user->code,
                    'mobile'=> $user->contact_no,
                ];
            }
        }

        if(!empty($gpsNoUsers)){

            $emails = EmailSelectedType::where('et_id',3)->with('email')->get();

            Mail::send('emails.no-gps', [
                'users'=> $gpsNoUsers,
                'loggedUser'=> Auth::user(),
            ],function(Message $mail) use($emails) {

                $mail->subject("[GPS Not Received]");

                $mail->to(config('shl.system_email'),"Onefore CRM");

                foreach ($emails as $email){
                    if($email->email){
                        $mail->cc($email->email->e_email, $email->email->e_name);
                    }
                }
            });
        }



    }

}
