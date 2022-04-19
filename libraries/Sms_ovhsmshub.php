<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sms_ovhsmshub extends App_sms
{
	private $OVH_MY_AK;
	private $OVH_MY_AS;
	private $OVH_MY_CK;
	private $sender_id;
	private $dlt_te_id;
	private $serviceName;
	private $sms_type;
	private $RequestUrl = '$ovh';

	public function __construct()
	{
		parent::__construct();
		$this->sender_id = $this->get_option('ovhsmshub', 'sender_id');
		$this->OVH_MY_AK  = $this->get_option('ovhsmshub', 'OVH_MY_AK');
		$this->OVH_MY_AS  = $this->get_option('ovhsmshub', 'OVH_MY_AS');
		$this->OVH_MY_CK  = $this->get_option('ovhsmshub', 'OVH_MY_CK');
		$this->serviceName  = $this->get_option('ovhsmshub', 'serviceName');
		$this->sms_type = $this->get_option('ovhsmshub', 'sms_type');
			$this->add_gateway('ovhsmshub', [
				'info' => "<p>OVH SMS GATEWAY module uses OVH API to send sms: <a href='https://eu.api.ovh.com/createToken/' target='_blank'>https://eu.api.ovh.com/createToken/</a> Help to create your credentials: <a href=' https://docs.ovh.com/gb/en/sms/send_sms_with_ovhcloud_api_in_php/#step-2-create-your-credentials' target='_blank'> https://docs.ovh.com/gb/en/sms/send_sms_with_ovhcloud_api_in_php/</a></p><hr class='hr-10'>",
				'name'    => 'OVH',
				'options' => [				
					[
						'name'  => 'OVH_MY_AK',
						'label' => 'Application key',
					],
					[
						'name'  => 'OVH_MY_AS',
						'label' => 'Application Secret (signiature)',
					],
					[
						'name'  => 'OVH_MY_CK',
						'label' => 'Consumer key (credential) ',
					],
					[
						'name'  => 'serviceName',
						'label' => 'Service Name',
					],
					[
						'name'  => 'sender_id',
						'label' => 'Sender ID',
					],
					[
						'name'          => 'sms_type',
						'field_type'    => 'radio',
						'default_value' => 'true',
						'label'         => 'SMS Type',
						'options'       => [
							['label' => 'Promotional', 'value' => 'false'],
							['label' => 'Transactional', 'value' => 'true'],
						],
					],
				],
			]);	
	}
	public function send($number, $message)
	{
		try {
			$swf_my_ak=$this->OVH_MY_AK;
			$swf_my_as=$this->OVH_MY_AS;
			$swf_my_ck=$this->OVH_MY_CK;
			require_once(__DIR__ . '/OvhApi.php');
			$ovh = new OvhApi(); 
			$smsserviceName=$this->serviceName;
			$sender_id=$this->sender_id;
			$smssend = "/sms/".$smsserviceName."/jobs";
			$SenderForReponse='false';
			if(!$sender_id!=''){$SenderForReponse='true';}
			
			$sms=$resp=$ovh->post($smssend, array(
				'charset' => 'UTF-8',
				'class' => 'phoneDisplay',
				'coding' => '7bit',
				'differedPeriod' => NULL,
				'message' => "$message",
				'noStopClause' => "$this->sms_type",
				'priority' => NULL,
				'receivers' => ["$number"],
				'receiversDocumentUrl' => NULL,
				'receiversSlotId' => NULL,
				'sender' => "$sender_id",
				'senderForResponse' => "$SenderForReponse",
				'tag' => NULL,
				'validityPeriod' => NULL,
				
			));

			
				if((get_option(SWF_OVHSMS_MODULE_NAME.'_activated')) == 'true'&& (get_option(SWF_OVHSMS_MODULE_NAME.'_activation_code')!='')){
				$Errormsg = ' - Nombre de crédits utilisé :';
				if($sms['totalCreditsRemoved'] >= 1 ){
					$this->logSuccess($number, $message);
					return true;
				}	
		    $this->set_error( $sms['message'] . $Errormsg . $sms['totalCreditsRemoved']);
			}
		} catch (\Exception $e) {
			$Errormsg = ' - Nombre de crédits utilisé :';
		    $this->set_error( $sms['message'] . $Errormsg . $sms['totalCreditsRemoved']);
		}
		return false;
	}
}