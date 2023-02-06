<?php

namespace DTApi\Criteria;

/**
 * 
 */
class JobsCriteria
{
	public $model;

	function __construct($model)
	{
		$this->model = $model;
	}

	public function getCriteria()
	{
		if (isset($requestdata['id']) && $requestdata['id'] != '') {
            $this->model->where('id', $requestdata['id']);
            $requestdata = array_only($requestdata, ['id']);
        }

        if ($consumer_type == 'RWS') {
            $this->model->where('job_type', '=', 'rws');
        } else {
            $this->model->where('job_type', '=', 'unpaid');
        }
        if (isset($requestdata['feedback']) && $requestdata['feedback'] != 'false') {
            $this->model->where('ignore_feedback', '0');
            $this->model->whereHas('feedback', function($q) {
                $q->where('rating', '<=', '3');
            });
            if(isset($requestdata['count']) && $requestdata['count'] != 'false') return ['count' => $this->model->count()];
        }
        
        if (isset($requestdata['lang']) && $requestdata['lang'] != '') {
            $this->model->whereIn('from_language_id', $requestdata['lang']);
        }
        if (isset($requestdata['status']) && $requestdata['status'] != '') {
            $this->model->whereIn('status', $requestdata['status']);
        }
        if (isset($requestdata['job_type']) && $requestdata['job_type'] != '') {
            $this->model->whereIn('job_type', $requestdata['job_type']);
        }
        if (isset($requestdata['customer_email']) && $requestdata['customer_email'] != '') {
            $user = DB::table('users')->where('email', $requestdata['customer_email'])->first();
            if ($user) {
                $this->model->where('user_id', '=', $user->id);
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

        $this->model->orderBy('created_at', 'desc');
        $this->model->with('user', 'language', 'feedback.user', 'translatorJobRel.user', 'distance');
        if ($limit == 'all')
            $this->model = $this->model->get();
        else
            $this->model = $this->model->paginate(15);

        return $this->model;
	}
}