<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Route URI List For Mobile
|--------------------------------------------------------------------------
| URI List Constants. List of accessible controllers.
| Pattern: All label and URI to be in singular form as all class names 
|          are in singular form as well.
*/
 
$config["routes_uri"]["mobile_login"]               = "services/mobile/auth/auth/login"; 
$config["routes_uri"]["mobile_asset_download"]      = "services/mobile/asset/asset/downloadAsset"; /* Allow login from mobile. Download asset details. Access matrix key usage only, no controller. */
$config["routes_uri"]["mobile_department_download"] = "services/mobile/asset/department/downloadDepartment";
$config["routes_uri"]["mobile_track_upload"]        = "services/mobile/track/track/uploadTrack"; /* Asset tracking */
$config["routes_uri"]["mobile_photo"]               = "services/mobile/attachment/get/local_image";

/*
|--------------------------------------------------------------------------
| List of available web service version
|--------------------------------------------------------------------------
| Version available for mobile. Latest version is the default version.
|
*/
$config["latest_version"]       = "v20150817";
$config["available_version"]    = array("v20150817");









    