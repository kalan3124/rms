<?php

namespace App\Http\Controllers\Web\Reports\Sales;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Exceptions\WebAPIException;
use Illuminate\Support\Facades\Schema;


class GpsReportController extends ReportController
{

    protected $title = "GPS Report";
    // protected $updateColumnsOnSearch = true;
    public function search(Request $request)
    {
        $values = $request->input('values');

        if (!isset($values['u_name'])) {
            throw new WebAPIException('SR field is required');
        }



        $start = new \DateTime(date('Y-m-01', strtotime($values['month'])));
        $end = new \DateTime(date('Y-m-t', strtotime($values['month'])));
        $end = $end->modify('1 day');

        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($start, $interval, $end);

        if (date('m', strtotime($values['month'])) != date('m')) {
            $table_name = 'gps_tracking_' . date('Y', strtotime($values['month'])) . '_' . date('m', strtotime($values['month']));
        } else {
            $table_name = 'gps_tracking';
        }

        if (Schema::hasTable($table_name)) {
            // throw new WebAPIException('Table has');
        } else {
            throw new WebAPIException('Data Not Found');
        }

        $query = DB::table($table_name . ' as gs');
        $query->join('users as u', 'u.id', 'gs.u_id')
            ->select([

                'u.name',
                'u.contact_no',
                'gs.created_at',
                'gs.gt_time',
                'gs.gt_lon',
                'gs.gt_lat',
                'gs.gt_accu'

            ]);
        $query->whereDate('gs.gt_time', '>=', date('Y-m-01', strtotime($values['month'])));
        $query->whereDate('gs.gt_time', '<=', date('Y-m-t', strtotime($values['month'])));

        if (isset($values['u_name'])) {
            $query->where('gs.u_id', $values['u_name']['value']);
        };

        // $count = $this->paginateAndCount($query, $request, 'gs.u_id');

        $results = $query->get();


        //group by 30min to 30min
        $last_time = 0;
        $results = $results->filter(function ($row) use (&$last_time) {
            $max = strtotime($row->gt_time) > $last_time + 30 * 60;

            if ($max) {
                $last_time = strtotime($row->gt_time);
            }
            return $max;
        });

        //pagination
        $count = $results->count();
        $results = $results->forpage($request->input('page',1), $request->input('perPage'));

        $results->transform(function ($result) {

            $check_in_lat = $result->gt_lat;
            $check_in_lon = $result->gt_lon;

            $map_in = $check_in_lat.','.$check_in_lon;

            return [
                'u_name' => $result->name,
                'u_phone' => $result->contact_no,
                'gt_time' => $result->gt_time,
                'gt_lon' => $result->gt_lon,
                'gt_lat' => $result->gt_lat,
                'gt_accu' => $result->gt_accu,
                'view_map_in' => [
                    'label' => 'Location In',
                    'link' => 'https://www.google.com/maps/search/?api=1&query='.$map_in,
                ],
            ];
        });
        return [
            'results' => $results->values(),
            'count' => $count
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $columnController->text('u_name')->setLabel('NAME');
        $columnController->text('u_phone')->setLabel('PHONE');
        $columnController->text('gt_time')->setLabel('TIME');
        $columnController->text('gt_lon')->setLabel('LONGTITUDE');
        $columnController->text('gt_lat')->setLabel('LATITUDE');
        $columnController->text('gt_accu')->setLabel('ACCURACY');
        $columnController->link("view_map_in")->setDisplayLabel("Checkin Location")->setLabel("Checkin Location");
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown('u_name')->setLabel('SR Name')->setLink('user')->setWhere(['u_tp_id' => config('shl.sales_rep_type')]);
        $inputController->date('month')->setLabel('Month');
        $inputController->setStructure([['u_name', 'month']]);
    }
}
