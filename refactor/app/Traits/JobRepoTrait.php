<?php
namespace DTApi\Traits;

trait JobRepoTrait
{
	public function getGender($data = [])
	{
		if (in_array('male', $data['job_for'])) {
           return 'male';
        }
        if (in_array('female', $data['job_for'])) {
            return 'female';
        }
	}

	public function getCertification($data = [])
	{
		$certified = '';

		if (in_array('normal', $data['job_for'])) {
            $certified = 'normal';
        }
        if (in_array('certified', $data['job_for'])) {
            $certified = 'yes';
        } 
        if (in_array('certified_in_law', $data['job_for'])) {
            $certified = 'law';
        }
        if (in_array('certified_in_helth', $data['job_for'])) {
            $certified = 'health';
        }
        if (in_array('normal', $data['job_for']) && in_array('certified', $data['job_for'])) {
           $certified = 'both';
        }
        if(in_array('normal', $data['job_for']) && in_array('certified_in_law', $data['job_for'])) {
            $certified = 'n_law';
        }
        if(in_array('normal', $data['job_for']) && in_array('certified_in_helth', $data['job_for'])){
            $certified = 'n_health';
        }

        return $certified;
	}

	public function getJobType($type)
	{
        switch ($type) {
        	case 'rwsconsumer':
        		$jobType = 'rws';
        		break;
        	case 'ngo':
        		$jobType = 'unpaid';
        		break;
        	case 'paid':
        		$jobType = 'paid';
        		break;
        	case 'professional':
        		$jobType = 'paid'; /*show all jobs for professionals.*/
        		break;
        	case 'rwstranslator':
        		$jobType = 'rws';/* for rwstranslator only show rws jobs. */
        		break;
        	case 'volunteer':
        		$jobType = 'unpaid';/* for volunteers only show unpaid jobs. */
        		break;
        	default:
        		$jobType = 'unpaid';
        		break;
        }

        return $jobType;
	}

	public function getJobFor($job)
	{
		$jobFor = [];

        if ($job->gender != null) {
            if ($job->gender == 'male') {
                $jobFor[] = 'Man';
            } else if ($job->gender == 'female') {
                $jobFor[] = 'Kvinna';
            }
        }

        if ($job->certified != null) {
            if ($job->certified == 'both') {
                $jobFor[] = 'Godkänd tolk';
                $jobFor[] = 'Auktoriserad';
            } else if ($job->certified == 'yes') {
                $jobFor[] = 'Auktoriserad';
            } else if ($job->certified == 'n_health') {
                $jobFor[] = 'Sjukvårdstolk';
            } else if ($job->certified == 'law' || $job->certified == 'n_law') {
                $jobFor[] = 'Rätttstolk';
            } else {
                 $jobFor[] = $job->certified;
            }
        }

        return $jobFor;
	}

	public function messageForCustomerPhysicalType($job)
	{
		$message  = '';
		// prepare message templates
		$date     = date('d.m.Y', strtotime($job->due));
		$time     = date('H:i', strtotime($job->due));
		$duration = $this->convertToHoursMins($job->duration);
		$jobId    = $job->id;
		$city     = $job->city ? $job->city : $jobPosterMeta->city;

        $phoneJobMessageTemplate = trans('sms.phone_job', ['date' => $date, 'time' => $time, 'duration' => $duration, 'jobId' => $jobId]);
        $physicalJobMessageTemplate = trans('sms.physical_job', ['date' => $date, 'time' => $time, 'town' => $city, 'duration' => $duration, 'jobId' => $jobId]);
		// analyse weather it's phone or physical; if both = default to phone
        if ($job->customer_physical_type == 'yes' && $job->customer_phone_type == 'no') {
            // It's a physical job
            $message = $physicalJobMessageTemplate;
        } 
        if ($job->customer_physical_type == 'no' && $job->customer_phone_type == 'yes') {
            // It's a phone job
            $message = $phoneJobMessageTemplate;
        } 
        if ($job->customer_physical_type == 'yes' && $job->customer_phone_type == 'yes') {
            // It's both, but should be handled as phone job
            $message = $phoneJobMessageTemplate;
        }

        return $message;
	}

	public function getTranslatorType($jobType)
	{
		$translatorType = '';

		if ($jobType == 'paid')
            $translatorType = 'professional';
        if ($jobType == 'rws')
            $translatorType = 'rwstranslator';
        if ($jobType == 'unpaid')
            $translatorType = 'volunteer';

        return $translatorType;
	}

	public function translatorLevels($job)
	{
		$translator_level = [];
        if (!empty($job->certified)) {
            if ($job->certified == 'yes' || $job->certified == 'both') {
                $translator_level[] = 'Certified';
                $translator_level[] = 'Certified with specialisation in law';
                $translator_level[] = 'Certified with specialisation in health care';
            }
            elseif($job->certified == 'law' || $job->certified == 'n_law')
            {
                $translator_level[] = 'Certified with specialisation in law';
            }
            elseif($job->certified == 'health' || $job->certified == 'n_health')
            {
                $translator_level[] = 'Certified with specialisation in health care';
            }
            else if ($job->certified == 'normal' || $job->certified == 'both') {
                $translator_level[] = 'Layman';
                $translator_level[] = 'Read Translation courses';
            }
            elseif ($job->certified == null) {
                $translator_level[] = 'Certified';
                $translator_level[] = 'Certified with specialisation in law';
                $translator_level[] = 'Certified with specialisation in health care';
                $translator_level[] = 'Layman';
                $translator_level[] = 'Read Translation courses';
            }
        }

        return $translator_level;
	}
}