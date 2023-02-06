<?php
namespace DTApi\Repository;

interface BookingRepository
{
	public function getUsersJobs($user_id);
	public function getUsersJobsHistory($user_id, \Illuminate\Http\Request $request);
	public function store($user, $data);
	public function storeJobEmail($data);
	public function jobToData($job);
	public function jobEnd($post_data = array());
	public function getPotentialJobIdsWithUserId($user_id);
	public function sendNotificationTranslator($job, $data = [], $exclude_user_id);
	public function sendSMSNotificationToTranslator($job);
	public function isNeedToDelayPush($user_id);
	public function sendPushNotificationToSpecificUsers($users, $job_id, $data, $msg_text, $is_need_delay);
	public function getPotentialTranslators(\DTApi\Models\Job $job);
	public function updateJob($id, $data, $cuser);
	public function sendSessionStartRemindNotification($user, $job, $language, $due, $duration);
	public function acceptJob($data, $user);
	public function acceptJobWithId($job_id, $cuser);
	public function cancelJobAjax($data, $user);
	public function getPotentialJobs($cuser);
	public function endJob($post_data);
	public function customerNotCall($post_data);
	public function getAll(Request $request, $limit = null);
	public function alerts();
	public function userLoginFailed();
	public function bookingExpireNoAccepted();
	public function ignoreExpiring($id);
	public function ignoreExpired($id);
	public function ignoreThrottle($id);
	public function reopen($request);
}