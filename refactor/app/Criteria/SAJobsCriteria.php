<?php

namespace DTApi\Criteria;

/**
 * 
 */
class SAJobsCriteria
{
	public $model;

	function __construct($model)
	{
		$this->model = $model;
	}

	public function getCriteria()
	{
		$requestdata   = request()->all();
		$cuser         = $request->__authenticatedUser;
		$consumer_type = $cuser->consumer_type;

		if (isset($requestdata['feedback']) && $requestdata['feedback'] != 'false') {
            $this->model->where('ignore_feedback', '0');
            $this->model->whereHas('feedback', function ($q) {
                $q->where('rating', '<=', '3');
            });
            if (isset($requestdata['count']) && $requestdata['count'] != 'false') return ['count' => $this->model->count()];
        }

        if (isset($requestdata['id']) && $requestdata['id'] != '') {
            if (is_array($requestdata['id']))
                $this->model->whereIn('id', $requestdata['id']);
            else
                $this->model->where('id', $requestdata['id']);
            $requestdata = array_only($requestdata, ['id']);
        }

        if (isset($requestdata['lang']) && $requestdata['lang'] != '') {
            $this->model->whereIn('from_language_id', $requestdata['lang']);
        }
        if (isset($requestdata['status']) && $requestdata['status'] != '') {
            $this->model->whereIn('status', $requestdata['status']);
        }
        if (isset($requestdata['expired_at']) && $requestdata['expired_at'] != '') {
            $this->model->where('expired_at', '>=', $requestdata['expired_at']);
        }
        if (isset($requestdata['will_expire_at']) && $requestdata['will_expire_at'] != '') {
            $this->model->where('will_expire_at', '>=', $requestdata['will_expire_at']);
        }
        if (isset($requestdata['customer_email']) && count($requestdata['customer_email']) && $requestdata['customer_email'] != '') {
            $users = DB::table('users')->whereIn('email', $requestdata['customer_email'])->get();
            if ($users) {
                $this->model->whereIn('user_id', collect($users)->pluck('id')->all());
            }
        }
        if (isset($requestdata['translator_email']) && count($requestdata['translator_email'])) {
            $users = DB::table('users')->whereIn('email', $requestdata['translator_email'])->get();
            if ($users) {
                $allJobIDs = DB::table('translator_job_rel')->whereNull('cancel_at')->whereIn('user_id', collect($users)->pluck('id')->all())->lists('job_id');
                $this->model->whereIn('id', $allJobIDs);
            }
        }
        if (isset($requestdata['filter_timetype']) && $requestdata['filter_timetype'] == "created") {
            if (isset($requestdata['from']) && $requestdata['from'] != "") {
                $this->model->where('created_at', '>=', $requestdata["from"]);
            }
            if (isset($requestdata['to']) && $requestdata['to'] != "") {
                $to = $requestdata["to"] . " 23:59:00";
                $this->model->where('created_at', '<=', $to);
            }
            $this->model->orderBy('created_at', 'desc');
        }
        if (isset($requestdata['filter_timetype']) && $requestdata['filter_timetype'] == "due") {
            if (isset($requestdata['from']) && $requestdata['from'] != "") {
                $this->model->where('due', '>=', $requestdata["from"]);
            }
            if (isset($requestdata['to']) && $requestdata['to'] != "") {
                $to = $requestdata["to"] . " 23:59:00";
                $this->model->where('due', '<=', $to);
            }
            $this->model->orderBy('due', 'desc');
        }

        if (isset($requestdata['job_type']) && $requestdata['job_type'] != '') {
            $this->model->whereIn('job_type', $requestdata['job_type']);
            /*$this->model->where('jobs.job_type', '=', $requestdata['job_type']);*/
        }

        if (isset($requestdata['physical'])) {
            $this->model->where('customer_physical_type', $requestdata['physical']);
            $this->model->where('ignore_physical', 0);
        }

        if (isset($requestdata['phone'])) {
            $this->model->where('customer_phone_type', $requestdata['phone']);
            if(isset($requestdata['physical']))
            $this->model->where('ignore_physical_phone', 0);
        }

        if (isset($requestdata['flagged'])) {
            $this->model->where('flagged', $requestdata['flagged']);
            $this->model->where('ignore_flagged', 0);
        }

        if (isset($requestdata['distance']) && $requestdata['distance'] == 'empty') {
            $this->model->whereDoesntHave('distance');
        }

        if(isset($requestdata['salary']) &&  $requestdata['salary'] == 'yes') {
            $this->model->whereDoesntHave('user.salaries');
        }

        if (isset($requestdata['count']) && $requestdata['count'] == 'true') {
            $this->model = $this->model->count();

            return ['count' => $this->model];
        }

        if (isset($requestdata['consumer_type']) && $requestdata['consumer_type'] != '') {
            $this->model->whereHas('user.userMeta', function($q) use ($requestdata) {
                $q->where('consumer_type', $requestdata['consumer_type']);
            });
        }

        if (isset($requestdata['booking_type'])) {
            if ($requestdata['booking_type'] == 'physical')
                $this->model->where('customer_physical_type', 'yes');
            if ($requestdata['booking_type'] == 'phone')
                $this->model->where('customer_phone_type', 'yes');
        }
        
        $this->model->orderBy('created_at', 'desc');
        $this->model->with('user', 'language', 'feedback.user', 'translatorJobRel.user', 'distance');
        if ($limit == 'all')
            $this->model = $this->model->get();
        else
            $this->model = $this->model->paginate(15);

        return $this->model;
	}
}