<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Route URI List
|--------------------------------------------------------------------------
| URI List Constants. List of accessible controllers.
| Pattern: All label and URI to be in singular form as all class names 
|          are in singular form as well.
*/

$config["routes_uri"]["login"]                      = "home/login";
$config["routes_uri"]["logout"]                     = "home/logout";
$config["routes_uri"]["photo"]                      = "admin/attachment/get/local_image";
$config["routes_uri"]["photo_upload"]               = "admin/attachment/set";
$config["routes_uri"]["dashboard"]                  = "admin/user/dashboard";
$config["routes_uri"]["dashboard_change_password"]  = "admin/user/dashboard/changePassword";
$config["routes_uri"]["dashboard_regenerate_api"]   = "admin/user/dashboard/regenerateAPI";
$config["routes_uri"]["asset"]                      = "admin/asset/asset";
$config["routes_uri"]["asset_view"]                 = "admin/asset/asset/viewAsset";                /* Dependency */
$config["routes_uri"]["asset_individual_view"]      = "admin/asset/asset/viewIndividualAsset";
$config["routes_uri"]["asset_search"]               = "admin/asset/asset/searchAsset";  
$config["routes_uri"]["asset_add"]                  = "admin/asset/asset/addAsset";
$config["routes_uri"]["asset_update"]               = "admin/asset/asset/updateAsset";      
$config["routes_uri"]["asset_maintenance_add"]      = "admin/asset/maintenance/addMaintenance";      
$config["routes_uri"]["asset_maintenance_update"]   = "admin/asset/maintenance/updateMaintenance";
$config["routes_uri"]["asset_maintenance_delete"]   = "admin/asset/maintenance/deleteMaintenance";      
$config["routes_uri"]["asset_update_access"]        = "admin/asset/asset/updateAssetAccess";        /* Dependency - no controller implementation */
$config["routes_uri"]["asset_tracking_option"]      = "admin/asset/asset/updateAssetTrackingOption";    
$config["routes_uri"]["asset_tracking_option_access"] = "admin/asset/asset/updateAssetTrackingOptionAccess";        /* Dependency */
$config["routes_uri"]["asset_delete"]               = "admin/asset/asset/deleteAsset";
$config["routes_uri"]["asset_import_preview"]       = "admin/asset/asset/importAssetPreview";
$config["routes_uri"]["asset_import"]               = "admin/asset/asset/importAsset";
$config["routes_uri"]["asset_print_label"]          = "admin/asset/asset/printLabel";
$config["routes_uri"]["asset_report"]               = "admin/asset/asset/downloadReport";
$config["routes_uri"]["asset_detail_report"]        = "admin/asset/asset/downloadDetailReport";  
$config["routes_uri"]["discrepancy"]                = "admin/asset/discrepancy";
$config["routes_uri"]["discrepancy_view"]           = "admin/asset/discrepancy/viewDiscrepancy";    /* Dependency */
$config["routes_uri"]["discrepancy_individual_view"]= "admin/asset/discrepancy/viewIndividualDiscrepancy";
$config["routes_uri"]["discrepancy_report"]         = "admin/asset/discrepancy/downloadReport";
$config["routes_uri"]["loan"]                       = "admin/asset/loan";
$config["routes_uri"]["loan_view"]                  = "admin/asset/loan/viewLoan";                  /* Dependency */
$config["routes_uri"]["loan_individual_view"]       = "admin/asset/loan/viewIndividualLoan";
$config["routes_uri"]["loan_add"]                   = "admin/asset/loan/addLoan";
$config["routes_uri"]["loan_add_post"]              = "admin/asset/loan/addLoanPost";
$config["routes_uri"]["loan_update"]                = "admin/asset/loan/updateLoan"; 
$config["routes_uri"]["loan_return"]                = "admin/asset/loan/returnLoan";
$config["routes_uri"]["loan_update_access"]         = "admin/asset/loan/updateLoanAccess";          /* Dependency */
$config["routes_uri"]["loan_report"]                = "admin/asset/loan/downloadReport";
$config["routes_uri"]["loan_form"]                  = "admin/asset/loan/loanForm";
$config["routes_uri"]["writeoff"]                   = "admin/asset/writeoff"; 
$config["routes_uri"]["writeoff_view"]              = "admin/asset/writeoff/viewWriteoff";          /* Dependency */
$config["routes_uri"]["writeoff_add"]               = "admin/asset/writeoff/addWriteoff";
$config["routes_uri"]["writeoff_update"]            = "admin/asset/writeoff/updateWriteoff"; 
$config["routes_uri"]["writeoff_request"]           = "admin/asset/writeoff/requestWriteoff";
$config["routes_uri"]["writeoff_request_post"]      = "admin/asset/writeoff/requestWriteoffPost";  
$config["routes_uri"]["writeoff_approve"]           = "admin/asset/writeoff/approveWriteoff";
$config["routes_uri"]["writeoff_update_access"]     = "admin/asset/writeoff/updateWriteoffAccess";  /* Dependency */
$config["routes_uri"]["writeoff_report"]            = "admin/asset/writeoff/downloadReport";
$config["routes_uri"]["transfer"]                   = "admin/asset/transfer";
$config["routes_uri"]["transfer_view"]              = "admin/asset/transfer/viewTransfer";          /* Dependency - Able to view department out and in involved */
$config["routes_uri"]["transfer_add"]               = "admin/asset/transfer/addTransfer";           /* Make transfer of department and location */
$config["routes_uri"]["transfer_add_post"]          = "admin/asset/transfer/addTransferPost";
$config["routes_uri"]["transfer_update"]            = "admin/asset/transfer/updateTransfer";        /* Update remarks and not affecting assets info only */
$config["routes_uri"]["transfer_update_access"]     = "admin/asset/transfer/updateTransferAccess";  /* Dependency */  
$config["routes_uri"]["transfer_report"]            = "admin/asset/transfer/downloadReport";  
$config["routes_uri"]["department"]                 = "admin/asset/department";                     
$config["routes_uri"]["department_add"]             = "admin/asset/department/addDepartment";
$config["routes_uri"]["department_update"]          = "admin/asset/department/updateDepartment";    /* No dependency required. Only asterisk* access */
$config["routes_uri"]["department_delete"]          = "admin/asset/department/deleteDepartment";
$config["routes_uri"]["category"]                   = "admin/asset/category";
$config["routes_uri"]["category_add"]               = "admin/asset/category/addCategory";
$config["routes_uri"]["category_update"]            = "admin/asset/category/updateCategory";
$config["routes_uri"]["category_delete"]            = "admin/asset/category/deleteCategory";
$config["routes_uri"]["user"]                       = "admin/user/user";
$config["routes_uri"]["user_view"]                  = "admin/user/user/viewUser"; /* View particular user. Sub-controller of [user] */ 
$config["routes_uri"]["user_add"]                   = "admin/user/user/addUser";
$config["routes_uri"]["user_role_assign"]           = "admin/user/user/assignRoleToUser";
$config["routes_uri"]["user_role_remove"]           = "admin/user/user/removeRoleFromUser";
$config["routes_uri"]["user_update"]                = "admin/user/user/updateUser";
$config["routes_uri"]["role"]                       = "admin/user/role";
$config["routes_uri"]["role_view"]                  = "admin/user/role/viewRole"; /* View particular role. Sub-controller of [role] */
$config["routes_uri"]["role_add"]                   = "admin/user/role/addRole";
$config["routes_uri"]["role_update"]                = "admin/user/role/updateRole";
$config["routes_uri"]["role_permission_view"]       = "admin/user/role/viewPermission";
$config["routes_uri"]["role_permission_update"]     = "admin/user/role/updatePermission";
$config["routes_uri"]["role_user_assign"]           = "admin/user/role/assignRoleToUser";
$config["routes_uri"]["role_user_remove"]           = "admin/user/role/removeRoleFromUser";
$config["routes_uri"]["role_delete"]                = "admin/user/role/deleteRole";
$config["routes_uri"]["alert_recipient"]            = "admin/notification/alertRecipient"; 
$config["routes_uri"]["alert_recipient_view"]       = "admin/notification/alertRecipient/viewAlertRecipient"; /* Dependency - Per department */
$config["routes_uri"]["alert_recipient_add"]        = "admin/notification/alertRecipient/addAlertRecipient";
$config["routes_uri"]["alert_recipient_delete"]     = "admin/notification/alertRecipient/deleteAlertRecipient";
$config["routes_uri"]["alert_recipient_update_access"] = "admin/notification/alertRecipient/updateAlertRecipientAccess"; /* Dependency */ 
$config["routes_uri"]["config"]                     = "admin/config/config";
$config["routes_uri"]["config_update"]              = "admin/config/config/updateConfig";
$config["routes_uri"]["mobile"]                     = "admin/mobile"; /* Allow login from mobile. Download asset details. Access matrix key usage only, no controller. */
$config["routes_uri"]["mobile_track"]               = "admin/mobile/track"; /* Asset tracking */


