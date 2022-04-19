<?php

/********************************************************************
**
** CREATION David Arneau - AD-WAIBE - WWW.AD-WAIBE.FR
** Adaptation du Copyright initial : https://github.com/ovh/php-ovh
** Utilisation pour module perfex CRM OVH SMS GATEWAY by Stack web factory 
**
*********************************************************************/
define("MY_AK", "$swf_my_ak"); 	// Clef application 
define("MY_AS", "$swf_my_as"); 	// Secret key 
define("MY_CK", "$swf_my_ck"); 	// Consumer key : à récupérer avec get-key3.php

define ("MODE_TEST", "1");		// Mettre à 1 pour executer ce script avec easyPhp, mettre à 0 quand c'est déployé sur l'hébergement OVH

define('OVH_API_EU', 'https://api.ovh.com/1.0');

class OvhApi
{
    //FROM : https://github.com/ovh/php-ovh
    public static $roots = array(
       'ovh-eu' => 'https://eu.api.ovh.com/1.0',
       'ovh-ca' => 'https://ca.api.ovh.com/1.0',
       'kimsufi-eu' => 'https://eu.api.kimsufi.com/1.0',
       'kimsufi-ca' => 'https://ca.api.kimsufi.com/1.0',
       'soyoustart-eu' => 'https://eu.api.soyoustart.com/1.0',
       'soyoustart-ca' => 'https://ca.api.soyoustart.com/1.0',
       'runabove-ca' => 'https://api.runabove.com/1.0');
    protected $AK;
    protected $AS;
    protected $CK;
    protected $serviceName;
    protected $timeDrift = 0;
    protected $cliVersion;
    /**
     * constructor
     * @param $_root API service URL (give in $roots array)
     * @param $_ak Your app key
     * @param $_as Your app secret key
     * @param $_cliVersion Set it to false if you're in a web page
     */
    public function __construct(
			$_root = OVH_API_EU, 
			$_ak = MY_AK, 					// Clef application pour waibe
			$_as = MY_AS, 	// Secret key pour waibe
			$_cliVersion = true)
    {
        // INIT vars
        $this->AK = $_ak;
        $this->AS = $_as;
		$this->CK = MY_CK;
        $this->ROOT = $_root;
        $this->cliVersion = $_cliVersion;
        // Compute time drift
        $serverTimeRequest = file_get_contents($this->ROOT . '/auth/time');
        if($serverTimeRequest !== FALSE)
            $this->timeDrift = time() - (int)$serverTimeRequest;
        else
            die('ERROR (#0000) : Compute time drift fail !\n');
    }
    /**
     * Call the api
     * @param $_method GET POST DELETE or UPDATE
     * @param $_url The call url wanted
     * @param $_body Parameters
     */
    public function call($_method, $_url, $_body = "")
    {
		if ($_method == "GET" && $_body != "")
		{
			// pour la signature, dans le cas de GET, les parametres doivent être dans l'URL, et non pas dans le body
			$l_params = "";
			foreach ($_body as $l_typ => $l_val)
			{
				if ($l_params != "")
					$l_params .= "&";
				$l_params .= $l_typ."=".$l_val;
			}
			// J'ajoute les parametres dans l'URL
			$_url .= "?".$l_params;
			// Je les retire du body
			$_body = "";
		}
		else
		{
			if($_body != "")
				$_body = json_encode($_body);
		}
		
        $myUrl = $this->ROOT . $_url;
        // Compute signature
        $time = time() - $this->timeDrift;
        $toSign = $this->AS.'+'.$this->CK.'+'.$_method.'+'.$myUrl.'+'.$_body.'+'.$time;
        
        $signature = '$1$' . sha1($toSign);
        // Call
        $curl = curl_init($myUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $_method);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'X-Ovh-Application:' . $this->AK,
            'X-Ovh-Consumer:' . $this->CK,
            'X-Ovh-Signature:' . $signature,
            'X-Ovh-Timestamp:' . $time,
        ));
		
		// 1 = utilisé en local avec easyPhp		
		if (MODE_TEST == 1)
		{
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		}

        if($_body)
        {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $_body);
        }
        $result = curl_exec($curl);
        if($result === FALSE)
        {
            echo curl_error($curl);
            return NULL;
        }
        return json_decode($result, true);
    }
    public function get($url, $body="")
    {
        return $this->call("GET", $url, $body);
    }
    public function put($url, $body)
    {
        return $this->call("PUT", $url, $body);
    }
    public function post($url, $body)
    {
        return $this->call("POST", $url, $body);
    }
    public function delete($url)
    {
        return $this->call("DELETE", $url);
    }

}
?>