<?php

// Connection to the database

define('DBNAME', '', true);
define('LOGIN', '', true);
define('PASSWORD', '', true);

// Available translations for homebrews
$available_translations = array('fr', 'de', 'es');

// Our custom php.ini options
ini_set('file_uploads', 'On');
ini_set('post_max_size', '1024M');
ini_set('upload_max_filesize', '1024M');

// Avoid to leak sensitive data, maybe we'll add a logger
//error_reporting(0);
ini_set('display_errors',1);


// Our available categories and sortby
$aCategory = array('game','emulator','utility','plugin','development', 'firmware', 'wallpaper', 'theme');
$aSortby = array('id','name','version','release','rating','votescount','dlcount');

function CheckCategory($category)
{
	// Our valid categories
	global $aCategory;
	
	if(in_array($category, $aCategory))
	{
		return true;
	}
	else
	{
		return false;
	}
	
}

function CheckSortby($sortby)
{
	// Our valid sortby
	global $aSortby;
	
	if(in_array($sortby, $aSortby))
	{
		return true;
	}
	else
	{
		return false;
	}
	
}

// Returns an array holding a list of the homebrew and their info

function get_hblist($category, $rstart, $rend, $sortby, $desc, $lang="en")
{

	// If the user tries to access a negative id
	if ($rstart < 0) {$rstart = 0;}
	if ($rend < 0) {$rend = 20;}
	if ($rstart > $rend) {$rstart = 0; $rend = 20;}
	
	
	// If we resquest too many items (could slow the server down)
	if ($rend - $rstart >= 100) {$rend = $rstart + 20;}
	
	// Avoids hacking and makes don't filter by default
	if (!(CheckCategory($category)))
	{
		$category = "";
	}

	if (!(CheckSortby($sortby)))
	{
		// Default Sortby
		$sortby = 'id';
	}
	$sortby = "`" . $sortby . "`";
	
	if ($category == "")
	{
		$opt_category = "";
	}
	else
	{
		$opt_category = 'WHERE category = :category ';
	}
	
	// Prevents SQL injection
	if ($desc == 1)
	{
		$desc = 'DESC';
	}
	else
	{
		$desc = "";
	}
	
	try
	{
		$hb_bdd = new PDO('mysql:host=localhost;dbname=' . DBNAME, LOGIN, PASSWORD);
	}
	catch (Exeption $e)
	{
		die('Unable to connect to the database');
	}
	// Locate the HBs
	$prep_hb_infos = $hb_bdd->prepare('SELECT * FROM hb_database ' . $opt_category . 'ORDER BY ' . $sortby . ' ' . $desc . ' LIMIT :rstart , :rend');
	
	if ($category != "") {$prep_hb_infos->bindParam(':category', $category, PDO::PARAM_STR);}
	$prep_hb_infos->bindParam(':rstart', $rstart, PDO::PARAM_INT);
	$prep_hb_infos->bindParam(':rend', $rend, PDO::PARAM_INT);
	
	$prep_hb_infos->execute();
	// Get the informations
	$hblist = $prep_hb_infos->fetchAll();
	
	if($lang != "en")
	{
		$aGetTranslatedHombrews = "";
		foreach($hblist as $currhb)
		{
			$aGetTranslatedHombrews .= "OR id = $currhb['id'] ";
		}
	}
	
	return $hblist;
}

// Get information about a homebrew

function get_hbinfo($hbid, $lang = 'en')
{
	
	if ($hbid < 100000000 AND $hbid > 0)
	{
		try
		{
			$hb_bdd = new PDO('mysql:host=localhost;dbname=' . DBNAME, LOGIN, PASSWORD);
		}
		catch (Exeption $e)
		{
			die('Error : ' . $e->getMessage());
		}
		// Locate the HB
		$prep_hb_infos = $hb_bdd->prepare('SELECT * FROM `hb_database` WHERE id = :hbid');
		$prep_hb_infos->execute(array('hbid' => $hbid));

		// Get information
		$hbinfo = $prep_hb_infos->fetch();
		if (!$hbinfo)
		{
			return -2;
		}
		
		if ($lang != 'en')
		{
			$hb_translation = $hb_bdd->prepare('SELECT * FROM `hb_database.' . $lang . '` WHERE id = :hbid');
			$hb_translation->execute(array('hbid' => $hbid));			
			
			$translate_hbinfo = $hb_translation->fetch();
			if (!$translate_hbinfo)
			{
				return -3;
			}
			
			$hbinfo['sdescription'] = $translate_hbinfo['sdescription'];
			$hbinfo['ldescription'] = $translate_hbinfo['ldescription'];
			
		}
		return $hbinfo; 
	}
	else
	{
		return -1;
	}
}


// Returns the address of the screenshot or a value < 0 if there is an error

function get_screenshot($hbid, $scid)
{
	if ($hbid < 100000000 AND $hbid > 0 AND $scid < 100 AND $scid > 0)
	{
		$screenshot_address = __DIR__ . '/screenshot/' . $hbid . '_' . $scid . '.png';
	
		// If we've the requested screenshot for this homebrew
		if (file_exists($screenshot_address))
		{
			// We set it
			return WebPath($screenshot_address);
		}
		else
		{
			// We warn we don't have the requested screenshot
			return -2;
		}
	
	}
	else
	{
		// Error 1 = hbid or/and scid is not a number or an incorrect number
		return -1;
	}

}

function get_icon($hbid)
{
	if ($hbid < 100000000 AND $hbid > 0)
	{
		$icon_address = __DIR__ . '/icon/' . $hbid . '.png';
		
		// If we've an icon for this homebrew
		if (file_exists($icon_address))
		{
			// We return the address
			return WebPath($icon_address);
		}
		else
		{
			// We have no image
			return -1;
		}
		
	}
	
}

function get_content($hbid)
{
	
	if ($hbid < 100000000 AND $hbid > 0)
	{
	
		$content_address = __DIR__ . '/content/' . $hbid . '.zip';
		
		// If we've the content
		if (file_exists($content_address))
		{
			// We return it
			return WebPath($content_address);
		}
		else
		{
			// We don't have the file
			return -2;
		}
	}
	else
	{
		// Wrong hbid
		return -1;
	}
}

function del_icon($hbid)
{
	if ($hbid < 100000000 AND $hbid > 0)
	{
		$icon_address = __DIR__ . '/icon/' . $hbid . '.png';
		
		// We delete the icon, no matter if it really exists
		unlink($icon_address);
	}
}

function del_screenshot($hbid, $scid)
{
if ($hbid < 100000000 AND $hbid > 0 AND $scid < 100 AND $scid > 0)
	{
		
		$maxscid = 1;
		// Find the number of screenshot
		while (is_string(get_screenshot($hbid, $maxscid + 1)))
		{
			$maxscid++;
		}
		
		
		$screenshot_address = __DIR__ . '/screenshot/' . $hbid . '_' . $scid . '.png';
		// Delete the unwanted screenshot
		unlink($screenshot_address);
		// Next screenshot
		$scid++;
		
		if ($maxscid >= $scid)
		{
			for (;$scid <= $maxscid;$scid++)
			{
				rename(__DIR__ . '/screenshot/' . $hbid . '_' . $scid . '.png', __DIR__ . '/screenshot/' . $hbid . '_' . ($scid - 1) . '.png');
			}
		}
	
	}
	else
	{
		// Error 1 = hbid or/and scid is not a number or an incorrect number
		return -1;
	}
	
	
}

function getHost()
{
	$pageURL = 'http';
	 
	// Doesn't work on WAMP
	// if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	$pageURL .= $_SERVER["SERVER_NAME"];
	
	if ($_SERVER["SERVER_PORT"] != "80")
	{
		$pageURL .= ":".$_SERVER["SERVER_PORT"];
	}
	
	$pageURL .= '/';
	
	return $pageURL;
}
// Get the URL of the current page
function curPageURL()
{
	$pageURL = 'http';
	 
	// Doesn't work on WAMP
	// if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80")
	{
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	}
	else
	{
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}

	return $pageURL;
}

// Convert a link to a fake form: NEEDS the css style
// Needs the name of the link, its location, and a 2D array ($post_info) holding post_name and post_value on each row
function LinkToForm($name, $link, $post_info)
{
	$myform = '<form class="formlink" action="' . $link . '" method="post">';
	foreach ($post_info as $currfield)
	{
		$myform .= '<input type="hidden" name="' . $currfield['post_name'] . '" value="' . $currfield['post_value'] . '" />';
	}
	$myform .= '<input type="submit" class="sublink" value="' . $name .'" /></form>';
	
	return $myform;
}

// Parameter: username
// Returns: array or -1
function GetUserInfo($login)
{
	
	try
	{
		$admin_bdd = new PDO('mysql:host=localhost;dbname=' . DBNAME, LOGIN, PASSWORD);
	}
	catch (Exeption $e)
	{
		die('Error : ' . $e->getMessage());
	}
	// Locate the account
	$account_info = $admin_bdd->prepare('SELECT * FROM hb_admin WHERE login = :login');
	$account_info->execute(array('login' => $_SESSION['login']));
	
	if ($data = $account_info->fetch())
	{
		return $data;
	}
	else
	{
		// Can't find the user
		return -1;
	}
	
}

function action_modified_entry($userid, $lastcount)
{
	
	try
	{
		$admin_bdd = new PDO('mysql:host=localhost;dbname=' . DBNAME, LOGIN, PASSWORD);
	}
	catch (Exeption $e)
	{
		die('Error : ' . $e->getMessage());
	}
	// Locate the account
	$account_info = $admin_bdd->prepare('UPDATE hb_admin SET modified_entries = :modified_entries WHERE id = :id');
	$account_info->execute(array('modified_entries' => ++$lastcount, 'id' => $userid));	

}

function action_added_entry($userid, $lastcount)
{
	try
	{
		$admin_bdd = new PDO('mysql:host=localhost;dbname=' . DBNAME, LOGIN, PASSWORD);
	}
	catch (Exeption $e)
	{
		die('Error : ' . $e->getMessage());
	}
	// Locate the account
	$account_info = $admin_bdd->prepare('UPDATE hb_admin SET added_entries = :added_entries WHERE id = :id');
	$account_info->execute(array('added_entries' => ++$lastcount, 'id' => $userid));	
	
}


function CheckLang($lang)
{
	global $available_translations;
	if(!in_array($lang, $available_translations))
	{
		return "en";
	}
	else
	{
		return $lang;
	}
}


//Client ID of the application.
define("CLIENTID", "66268521-96dd-4a42-bfb2-9d76db90776f");

//Client Secret key of the application.
define("CLIENTSECRET", "eoG7hZolbekppBSOX+Pu9JmwjBDKje+rxMFpfHmVoY0=");


class AccessTokenAuthentication {
    /*
     * Get the access token.
     *
     * @param string $grantType    Grant type.
     * @param string $scopeUrl     Application Scope URL.
     * @param string $clientID     Application client ID.
     * @param string $clientSecret Application client ID.
     * @param string $authUrl      Oauth Url.
     *
     * @return string.
     */
    function getTokens($grantType, $scopeUrl, $clientID, $clientSecret, $authUrl){
        try {
            //Initialize the Curl Session.
            $ch = curl_init();
            //Create the request Array.
            $paramArr = array (
                 'grant_type'    => $grantType,
                 'scope'         => $scopeUrl,
                 'client_id'     => $clientID,
                 'client_secret' => $clientSecret
            );
            //Create an Http Query.//
            $paramArr = http_build_query($paramArr);
            //Set the Curl URL.
            curl_setopt($ch, CURLOPT_URL, $authUrl);
            //Set HTTP POST Request.
            curl_setopt($ch, CURLOPT_POST, TRUE);
            //Set data to POST in HTTP "POST" Operation.
            curl_setopt($ch, CURLOPT_POSTFIELDS, $paramArr);
            //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
            //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //Execute the  cURL session.
            $strResponse = curl_exec($ch);
            //Get the Error Code returned by Curl.
            $curlErrno = curl_errno($ch);
            if($curlErrno){
                $curlError = curl_error($ch);
                throw new Exception($curlError);
            }
            //Close the Curl Session.
            curl_close($ch);
            //Decode the returned JSON string.
            $objResponse = json_decode($strResponse);
            if ($objResponse->error){
                throw new Exception($objResponse->error_description);
            }
            return $objResponse->access_token;
        } catch (Exception $e) {
            echo "Exception-".$e->getMessage();
        }
    }
}


Class HTTPTranslator {
    /*
     * Create and execute the HTTP CURL request.
     *
     * @param string $url        HTTP Url.
     * @param string $authHeader Authorization Header string.
     * @param string $postData   Data to post.
     *
     * @return string.
     *
     */
    function curlRequest($url, $authHeader, $postData=''){
        //Initialize the Curl Session.
        $ch = curl_init();
        //Set the Curl url.
        curl_setopt ($ch, CURLOPT_URL, $url);
        //Set the HTTP HEADER Fields.
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array($authHeader,"Content-Type: text/xml"));
        //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, False);
        if($postData) {
            //Set HTTP POST Request.
            curl_setopt($ch, CURLOPT_POST, TRUE);
            //Set data to POST in HTTP "POST" Operation.
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        //Execute the  cURL session.
        $curlResponse = curl_exec($ch);
        //Get the Error Code returned by Curl.
        $curlErrno = curl_errno($ch);
        if ($curlErrno) {
            $curlError = curl_error($ch);
            throw new Exception($curlError);
        }
        //Close a cURL session.
        curl_close($ch);
        return $curlResponse;
    }


    /*
     * Create Request XML Format.
     *
     * @param string $fromLanguage   Source language Code.
     * @param string $toLanguage     Target language Code.
     * @param string $contentType    Content Type.
     * @param string $inputStrArr    Input String Array.
     *
     * @return string.
     */
    function createReqXML($fromLanguage,$toLanguage,$contentType,$inputStrArr) {
        //Create the XML string for passing the values.
        $requestXml = "<TranslateArrayRequest>".
            "<AppId/>".
            "<From>$fromLanguage</From>". 
            "<Options>" .
             "<Category xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
              "<ContentType xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\">$contentType</ContentType>" .
              "<ReservedFlags xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
              "<State xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
              "<Uri xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
              "<User xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
            "</Options>" .
            "<Texts>";
        foreach ($inputStrArr as $inputStr)
        $requestXml .=  "<string xmlns=\"http://schemas.microsoft.com/2003/10/Serialization/Arrays\">$inputStr</string>" ;
        $requestXml .= "</Texts>".
            "<To>$toLanguage</To>" .
          "</TranslateArrayRequest>";
        return $requestXml;
    }
}

function ProTranslate($fromLanguage, $toLanguage, $inputStrArr) {
    //OAuth Url.
    $authUrl      = "https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/";
    //Application Scope Url
    $scopeUrl     = "http://api.microsofttranslator.com";
    //Application grant type
    $grantType    = "client_credentials";

    //Create the AccessTokenAuthentication object.
    $authObj      = new AccessTokenAuthentication();
    //Get the Access token.
    $accessToken  = $authObj->getTokens($grantType, $scopeUrl, CLIENTID, CLIENTSECRET, $authUrl);
    //Create the authorization Header string.
    $authHeader = "Authorization: Bearer ". $accessToken;

    $contentType  = 'text/plain';
    //Create the Translator Object.
    $translatorObj = new HTTPTranslator();

    //Get the Request XML Format.
    $requestXml = $translatorObj->createReqXML($fromLanguage,$toLanguage,$contentType,$inputStrArr);

    //HTTP TranslateMethod URL.
    $translateUrl = "http://api.microsofttranslator.com/v2/Http.svc/TranslateArray";

    //Call HTTP Curl Request.
    $curlResponse = $translatorObj->curlRequest($translateUrl, $authHeader, $requestXml);

    //Interprets a string of XML into an object.
    $xmlObj = simplexml_load_string($curlResponse);
	
	$answer = array();
	
    foreach($xmlObj->TranslateArrayResponse as $translatedArrObj){
        $answer[] = $translatedArrObj->TranslatedText;
    }
	
	return $answer;
}




// Translate the homebrew to the selected translation. Needs the homebrew description etc
// Language can be fr, de, es (defined by the array $available_translations
function TranslateEntry($hbid, $aInfo)
{
	// Required info not present?
	if (!is_array($aInfo) OR !isset($aInfo['sdescription']) OR !isset($aInfo['ldescription']))
	{ return -1; }
	
	$hb_bdd = new PDO('mysql:host=localhost;dbname=' . DBNAME, LOGIN, PASSWORD);
	
	global $available_translations;
	foreach($available_translations as $currlanguage)
	{
		$translated_hb = ProTranslate("en", $currlanguage, array($aInfo['sdescription'], $aInfo['ldescription']));
	
		// Translate
		$hb_entry = $hb_bdd->prepare('INSERT INTO `hb_database.' . $currlanguage . '`(id, sdescription, ldescription) VALUES(:id, :sdescription, :ldescription)');
		$hb_entry->execute(array(
			'id' => $hbid,
			'sdescription' => $translated_hb[0],
			'ldescription' => $translated_hb[1]
			));
	}
	
	return 0;
}


// Needs a 1D array with a name of an option per row | the default value
// Returns a string of <option>myoption</option> with the default that has the attribute selected="selected"
function GetOptions($aOptions, $default)
{
	$finaloptions = '';
	foreach($aOptions as $curroption)
	{
		$finaloptions .= '<option';
		// If we need to set it to the default value
		if($curroption == $default) {$finaloptions .= ' selected="selected"';}
		$finaloptions .= '>' . $curroption . '</option>';
	}
	
	return $finaloptions;
}

function GetContentUploadPath($hbid, $tmp)
{
	$address = __DIR__ . '/content/' . $hbid;
	
	if ($tmp)
	{
		$address .= '.tmp';
	}
	else
	{
		$address .= '.zip';
	}
	
	return $address;
}

function GetIconUploadPath($hbid)
{
	return __DIR__ . '/icon/' . $hbid . '.png';
}

function GetScreenshotUploadPath($hbid)
{
	$scid = 1;
	// Get the number of screenshots
	while (is_string(get_screenshot($hbid, $scid)))
	{
		$scid++;
	}	
	
	// Return an unused screenshot slot
	return __DIR__ . '/screenshot/' . $hbid . '_' . $scid . '.png';
}



// Convert a local path to a web path
function WebPath($local_path)
{
	// Return host + relative directory of functions.php from the document root + the relative path to the directory of functions.php
	
	// realpath permits us to convert '/' to '\' if under windows
	$webAddress = getHost() . str_replace(realpath($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR, '', realpath($local_path));
	
	// Change the '\' to '/' if necessary (most likely windows OS)
	$webAddress = str_replace('\\', '/', $webAddress);
	
	// Finally return the valid web address
	return $webAddress;
}

// Thanks to DHKold
/*********************************************/
/* Function: ImageCreateFromBMP              */
/* Author:   DHKold                          */
/* Contact:  admin@dhkold.com                */
/* Date:     The 15th of June 2005           */
/* Version:  2.0B                            */
/*********************************************/

function ImageCreateFromBMP($filename)
{
 //We open the file in binary mode
   if (! $f1 = fopen($filename,"rb")) return FALSE;

 //1 : Load the FILE entries
   $FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1,14));
   if ($FILE['file_type'] != 19778) return FALSE;

 //2 : Load the BMP entries
   $BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'.
                 '/Vcompression/Vsize_bitmap/Vhoriz_resolution'.
                 '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));
   $BMP['colors'] = pow(2,$BMP['bits_per_pixel']);
   if ($BMP['size_bitmap'] == 0) $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
   $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8;
   $BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
   $BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4);
   $BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4);
   $BMP['decal'] = 4-(4*$BMP['decal']);
   if ($BMP['decal'] == 4) $BMP['decal'] = 0;

 //3 : Load the colors of the palette
   $PALETTE = array();
   if ($BMP['colors'] < 16777216)
   {
    $PALETTE = unpack('V'.$BMP['colors'], fread($f1,$BMP['colors']*4));
   }

 //4 : We create the image
   $IMG = fread($f1,$BMP['size_bitmap']);
   $VIDE = chr(0);

   $res = imagecreatetruecolor($BMP['width'],$BMP['height']);
   $P = 0;
   $Y = $BMP['height']-1;
   while ($Y >= 0)
   {
    $X=0;
    while ($X < $BMP['width'])
    {
     if ($BMP['bits_per_pixel'] == 24)
        $COLOR = unpack("V",substr($IMG,$P,3).$VIDE);
     elseif ($BMP['bits_per_pixel'] == 16)
     {  
        $COLOR = unpack("n",substr($IMG,$P,2));
        $COLOR[1] = $PALETTE[$COLOR[1]+1];
     }
     elseif ($BMP['bits_per_pixel'] == 8)
     {  
        $COLOR = unpack("n",$VIDE.substr($IMG,$P,1));
        $COLOR[1] = $PALETTE[$COLOR[1]+1];
     }
     elseif ($BMP['bits_per_pixel'] == 4)
     {
        $COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
        if (($P*2)%2 == 0) $COLOR[1] = ($COLOR[1] >> 4) ; else $COLOR[1] = ($COLOR[1] & 0x0F);
        $COLOR[1] = $PALETTE[$COLOR[1]+1];
     }
     elseif ($BMP['bits_per_pixel'] == 1)
     {
        $COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
        if     (($P*8)%8 == 0) $COLOR[1] =  $COLOR[1]        >>7;
        elseif (($P*8)%8 == 1) $COLOR[1] = ($COLOR[1] & 0x40)>>6;
        elseif (($P*8)%8 == 2) $COLOR[1] = ($COLOR[1] & 0x20)>>5;
        elseif (($P*8)%8 == 3) $COLOR[1] = ($COLOR[1] & 0x10)>>4;
        elseif (($P*8)%8 == 4) $COLOR[1] = ($COLOR[1] & 0x8)>>3;
        elseif (($P*8)%8 == 5) $COLOR[1] = ($COLOR[1] & 0x4)>>2;
        elseif (($P*8)%8 == 6) $COLOR[1] = ($COLOR[1] & 0x2)>>1;
        elseif (($P*8)%8 == 7) $COLOR[1] = ($COLOR[1] & 0x1);
        $COLOR[1] = $PALETTE[$COLOR[1]+1];
     }
     else
        return FALSE;
     imagesetpixel($res,$X,$Y,$COLOR[1]);
     $X++;
     $P += $BMP['bytes_per_pixel'];
    }
    $Y--;
    $P+=$BMP['decal'];
   }

 // We close the file
   fclose($f1);

 return $res;
}
?>
