<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: SWF SMS-OVH PASSERELLE SMS
Description: Le module fournit une nouvelle passerelle SMS pour envoyer des SMS par OVH.
Author: Stack Web Factory
Version: 2.1.1
Requires at least: 2.9.*
Author URI: https://stackwebfactory.fr
*/

define('SWF_OVHSMS_MODULE_NAME', 'swf_ovhsmshub');
define('SWF_OVHSMS_VALIDATION_URL','https://market.swf.ovh/api/validate_license');

hooks()->add_action('admin_init', 'swf_ovhsms_hook_admin_init');
hooks()->add_filter('module_'.SWF_OVHSMS_MODULE_NAME.'_action_links', 'module_swf_ovhsms_action_links');
hooks()->add_action('settings_tab_footer','swf_ovhsms_hook_settings_tab_footer');#pour perfex version < V2.4 
hooks()->add_action('settings_group_end','swf_ovhsms_hook_settings_tab_footer');#pour perfex version > V2.8.4

/**
* Ajoutez des paramètres supplémentaires pour ce module dans la zone de liste des modules
* @param  array $actions current actions
* @return array
*/

function module_swf_ovhsms__init_menu_items(){
}
	
function module_swf_ovhsms_action_links($actions)
{
	//if((get_option(SWF_OVHSMS_MODULE_NAME.'_activated') == 'true') && (get_option(SWF_OVHSMS_MODULE_NAME.'_activation_code')!='')){
		$actions[] = '<a href="' . admin_url('settings?group=sms') . '">' . _l('settings') . '</a>';
		//$actions[] = '<a href="' . admin_url('settings?group=swf_ovhsms_settings') . '">' . _l('swf_ovhsms_settings_license') . '</a>';
		
	//}
	//else
	//	$actions[] = '<a href="' . admin_url('settings?group=swf_ovhsms_settings') . '">' . _l('swf_ovhsms_settings_validate') . '</a>';
		
	return $actions;
}

function swf_ovhsms_hook_settings_tab_footer($tab)
{
	if($tab['slug']=='swf_ovhsms_settings' && (get_option(SWF_OVHSMS_MODULE_NAME.'_activated') != 'true')){
		echo '<script src="'.module_dir_url('swf_ovhsmshub','assets/js/swf_ovhsms_settings_footer.js').'"></script>';
	}
}


/**
* Enregistrer le hook du module d'activation
*/
register_activation_hook(SWF_OVHSMS_MODULE_NAME, 'swf_ovhsms_activation_hook');

function swf_ovhsms_activation_hook()
{
	$CI = &get_instance();
	require_once(__DIR__ . '/install.php');
}

/**
* Enregistrer les fichiers de langue, doit être enregistré si le module utilise des langues
*/
register_language_files(SWF_OVHSMS_MODULE_NAME, [SWF_OVHSMS_MODULE_NAME]);

/**
*	initialisation du module admin
*/
function swf_ovhsms_hook_admin_init()
{
	$CI = &get_instance();
	/**  Ajouter un onglet dans l'onglet Paramètres de la configuration **/
	if (is_admin() || has_permission('settings', '', 'view')) {
		$CI->app_tabs->add_settings_tab('swf_ovhsms_settings', [
			'name'     => _l('swf_ovhsms_settings'),
			'view'     => 'swf_ovhsmshub/swf_ovhsms_settings',
			'position' => 60,
		]);
	}
}
//if((get_option(SWF_OVHSMS_MODULE_NAME.'_activated') == 'true') && (get_option(SWF_OVHSMS_MODULE_NAME.'_activation_code')!=''))
	
$CI  =&get_instance();
$CI->load->library(SWF_OVHSMS_MODULE_NAME . '/sms_ovhsmshub');