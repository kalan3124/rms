<?php
namespace App\Http\Controllers\API\Medical\V1;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\Auth;
use App\Traits\Territory;
use App\Models\SubTown;

class CurrentItineraryController extends Controller{

     use Territory;

     public function ItineraryForDay(){

          $data = '';
          $mr = Auth::user(); 

          $begin = new \DateTime(date('Y-m-01'));
          $end = new \DateTime(date("Y-m-t"));
          $end = $end->modify('1 day'); 

          $interval = new \DateInterval('P1D');
          $daterange = new \DatePeriod($begin, $interval ,$end);

          $result = [];

               $subTowns = collect([]);
               $show = "";

                    try {
                         $subTownsToday = $this->getTerritoriesByItinerary($mr,strtotime(date('Y-m-d')));
                    } catch (\Throwable $exception) {
                         $subTownsToday = collect();
                    }

                    $itinerarySubTowns = [];
               
                    if($subTownsToday->isEmpty()){
                         $itinerarySubTowns = [];
                    }
                    else {
                         $itinerarySubTowns = $subTownsToday->pluck('sub_twn_id')->all();
                    }

                    $subTowns = SubTown::whereIn('sub_twn_id',$itinerarySubTowns)->select('*')->get();

                    $subTowns->transform(function($subTown){
                         if(isset($subTown)){
                              return $subTown->sub_twn_name;
                         }
                    });
                    $subTownNames = implode('/ ',$subTowns->all());

                    if($subTownNames){
                         $show =  $subTownNames;
                    } else {
                         $show = 'No Itinerary for Day';
                    }

                    // $result[] = $show;

                    // $data = '<html>
                    //           <tr>
                    //                <th>Towns</th>
                    //           </tr>
                    //           <tr>
                    //                <td>'.$show.'</td>
                    //           </tr>
                    //      </html>';

                    $data = '
                    <html lang="en" class="user_font_size_normal user_font_system" style="padding: 0px; margin: 0px;"><head><style type="text/css">.MsgHeaderTable .Object{cursor:pointer;color:#005A95;text-decoration:none;cursor:pointer;white-space:nowrap;}
                    .MsgHeaderTable .Object-hover{cursor:pointer;color:#005A95;text-decoration:underline;white-space:nowrap;}
                    .MsgBody{background-color:#fff;-moz-user-select:element;-ms-user-select:element;}
                    .MsgBody-text{color:#333;font-family:monospace;word-wrap:break-word;}
                    .MsgBody-text,.MsgBody-html{padding:10px;}
                    div.MsgBody,div.MsgBody *{font-size:1.18rem;}
                    body.MsgBody{font-size:1.18rem;}
                    .MsgBody .SignatureText{color:gray;}
                    .MsgBody .QuotedText0{color:purple;}
                    .MsgBody .QuotedText1{color:green;}
                    .MsgBody .QuotedText2{color:red;}
                    .user_font_modern{font-family:"Helvetica Neue",Helvetica,Arial,"Liberation Sans",sans-serif;}
                    .user_font_classic{font-family:Tahoma,Arial,sans-serif;}
                    .user_font_wide{font-family:Verdana,sans-serif;}
                    .user_font_system{font-family:"Segoe UI","Lucida Sans",sans-serif;}
                    .user_font_size_small{font-size:11px;}
                    .user_font_size_normal{font-size:12px;}
                    .user_font_size_large{font-size:14px;}
                    .user_font_size_larger{font-size:16px;}
                    .MsgBody .Object{color:#005A95;text-decoration:none;cursor:pointer;}
                    .MsgBody .Object-hover{color:#005A95;text-decoration:underline;}
                    .MsgBody .Object-active{color:darkgreen;text-decoration:underline;}
                    .MsgBody .FakeAnchor,.MsgBody a:link,.MsgBody a:visited{color:#005A95;text-decoration:none;cursor:pointer;}
                    .MsgBody a:hover{color:#005A95;text-decoration:underline;}
                    .MsgBody a:active{color:darkgreen;text-decoration:underline;}
                    .MsgBody .POObject{color:blue;}
                    .MsgBody .POObjectApproved{color:green;}
                    .MsgBody .POObjectRejected{color:red;}
                    .MsgBody .zimbraHide{display:none;}
                    .MsgBody-html pre,.MsgBody-html pre *{white-space:pre-wrap;word-wrap:break-word!important;text-wrap:suppress!important;}
                    .MsgBody-html tt,.MsgBody-html tt *{font-family:monospace;white-space:pre-wrap;word-wrap:break-word!important;text-wrap:suppress!important;}
                    .MsgBody .ZmSearchResult{background-color:#FFFEC4;}</style></head><body class="MsgBody MsgBody-html" style="margin: 0px; height: auto;"><div style=""><div><div style="font-family: roboto, sans-serif; border: 1px solid rgb(224, 224, 224); background-color: white; max-width: 600px; margin: 0px auto;"><div style="background-color: white; padding: 24px 0px;"><img style="margin: auto; display: block; height: 40px; max-height: 40px; min-height: 40px;" dfsrc="http://shl.salespad.lk/healthcare/images/logo.jpg" src="http://shl.salespad.lk/healthcare/images/logo.jpg" saveddisplaymode="block"></div><table style="width: 100%; background-color: rgb(3, 155, 229);" cellpadding="0" cellspacing="0"><tbody><tr><td style="padding: 24px;"><div style="font-size: 20px; line-height: 24px; color: white;"><div style="padding-top: 4px;">Today Itinerary</div></div></td><td style="padding: 24px; text-align: right;"></td></tr><tr></tr></tbody></table><div style="background: rgb(236, 239, 241); padding-top: 40px;"><table style="border-spacing: 0px; border-collapse: separate; width: 85%; min-width: 300px; max-width: 400px; margin: 0px auto; padding: 0px; border: 0px; border-radius: 8px; overflow: hidden;"><tbody><tr style="text-align: center; margin: 0px; padding: 0px; border: 0px;" align="center"><td style="border-top-left-radius: 8px; border-top-right-radius: 8px; font-family: &quot;helvetica neue&quot;, helvetica, arial, sans-serif; color: rgb(255, 255, 255); background: rgb(255, 179, 0); padding: 1.5em 0.75em; border-width: 1px 1px 0px; border-top-style: solid; border-right-style: solid; border-left-style: solid; border-top-color: rgb(215, 226, 233); border-right-color: rgb(215, 226, 233); border-left-color: rgb(215, 226, 233); border-image: initial; border-bottom-style: initial; border-bottom-color: initial;"><div style="text-align: center; margin-bottom: 0.75em;" align="center"></div><div style="font-size: 22px; font-weight: 400;">'.$show.'</div><span class="Object" role="link" id="OBJ_PREFIX_DWT80_com_zimbra_url"></span></td></tr><tr style="text-align: center; margin: 0px; padding: 0px; border: 0px;" align="center"><td style="border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; font-family: &quot;helvetica neue&quot;, helvetica, arial, sans-serif; text-align: left; background: white; padding: 20px 15px; border-left: 1px solid rgb(215, 226, 233); border-right: 1px solid rgb(215, 226, 233); border-bottom: 1px solid rgb(215, 226, 233);" align="left"></td></tr></tbody></table></div><div style="background-color: rgb(120, 144, 156); padding: 24px;"></div></div></div></div></body></html>';
               // }
          return [
               "result"=>true,
               "ar_name"=>$data,
               'count'=>0
          ];
     }
}
?>
