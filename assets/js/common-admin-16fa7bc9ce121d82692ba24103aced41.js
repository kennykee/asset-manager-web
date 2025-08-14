$(document).ready(function(){

$(".post-button").click(function(){
    event.preventDefault();
    event.stopPropagation();
    postRequest($(this).data("container-id"));
});

$(".get-button").click(function(){
    event.preventDefault();
    event.stopPropagation();
    getRequest($(this).data("container-id"));
});

$(".datepicker").datepicker({"format": "dd-MM-yyyy"});

$(".timepicker").timepicker();

$("body").on("click", ".record-delete", function(){
    $(this).parent().remove();
});

/* Top Header Search */
init_top_header_search();


/*####################################*/
/* Application Initialisation Section */
/*####################################*/
for(var i = 0; i < operation_queue.length; i++){
    switch(operation_queue[i]){
        case "init_department":
                init_department();
                break;
        case "init_category":
                init_category();
                break;
        case "init_alert_recipient":
                init_alert_recipient();
                break;
        case "init_user_view":
                init_user_view();  
                break;
        case "init_role_list":
                init_role_list();
                break;
        case "init_role_view":
                init_role_view();
                break;
        case "init_role_permission_view":
                init_role_permission_view();
                break;
        case "init_asset_list":
                init_asset_list();
                break;
        case "init_asset_view":
                init_asset_view();
                break;
        case "init_asset_update":
                init_asset_update();
                break;
        case "init_asset_add":
                init_asset_add();
                break;
        case "init_asset_import":
                init_asset_import();
                break;
        case "init_uploader":
                init_uploader();
                break;
        case "init_maintenance_view":
                init_maintenance_view();
                break;
        case "init_transfer_view":
                init_transfer_view();
                break;
        case "init_transfer_add":
                init_transfer_add();
                break;
        case "init_writeoff_view":
                init_writeoff_view();
                break;
        case "init_writeoff_request":
                init_writeoff_request();
                break;
        case "init_loan_view":
                init_loan_view();   
                break;
        case "init_loan_add":
                init_loan_add();
                break;
        case "init_loan_form_download":
                init_loan_form_download();
                break;                
        case "init_discrepancy_view":
                init_discrepancy_view();
                break;
        case "init_dashboard":
                init_dashboard();
                break;
    }
}
});

$.fn.serializeObject = function(){
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

function postRequest(container_id){
    
    var uri = $("#" + container_id + " .routes_uri").val();
    var inputData = $("#" + container_id + " .form-data").serializeObject();
    
    inputData[token_name] = token_value;
    
    $("#" + container_id + " .post-message").html("<i class='fa fa-spinner fa-pulse'></i> Please wait...processing request");
    
    $.ajax({
        type: "POST",
        url: uri,
        data: inputData,
        dataType: 'json', 
    }).done(function( data ) {
        if(data.success == 1){
            location.reload();
        }else{
            $("#" + container_id + " .error-message").html(combineMessage(data.message));
            $("#" + container_id + " .alert-danger").removeClass("hide");
        }
    }).fail(function(jqXHR, textStatus){
        alert("Error. Cannot connect to server");
    }).always(function(){
        $("#" + container_id + " .post-message").html("");
    });
}

function getRequest(container_id){
    
    var uri = $("#" + container_id + " .routes_uri").val();
    var get_list = "";
    
    $("#" + container_id + " .form-data").each(function(){
        /* Check if checkbox */
        if($(this).attr("type") == "checkbox" && !$(this).is(":checked")){
            return;
        }
       
        if(get_list.length != 0){
            get_list += "&";
        }
        get_list +=  ($(this).attr("name") + "=" + encodeURIComponent($(this).val()));
    });
    
    window.open(uri + "/?" + get_list, "_blank");
}

function combineMessage(messageArray){
    var combined_message = "";
    jQuery.each(messageArray, function(i, val){
        combined_message += val + "<br />";
    });
    return combined_message;
}

function init_dashboard(){
    
}

function init_top_header_search(){
    
    $("#top-search-dropdown").select2({
        ajax: {
            url: site_url + "/admin/asset/asset/searchAsset",
            type: "POST",
            dataType: 'json',
            delay: 250,
            data: function (params){
                
                params.page = (params.page !== undefined)? params.page : 1;
                
                var inputData = {};
                inputData[token_name] = token_value;
                inputData["page_no"] = params.page;
                inputData["count_per_page"] = 20;
                inputData["sort"] = "asc";
                inputData["sort_field"] = "assets_name";
                inputData["term"] = params.term;
                
                return inputData;
            },
            processResults: function (data, params) {
                
                params.page = params.page || 1;
                
                if(data.success == 1){
                    
                    return {
                        results: data.data,
                        pagination: {
                            more: (data.data.length >= 20)
                        }
                    };
                }else{
                    return {
                        results: {},
                        pagination: {
                            more: false 
                        }
                    };   
                }
            },
            cache: true
          },
          dropdownCssClass : 'larger-dropdown',
          placeholder: "<i class='fa fa-search'></i> <i>Search Asset Name. Barcode. Invoice Number.</i>",
          allowClear: true,
          escapeMarkup: function (markup) { return markup; }, 
          minimumInputLength: 1,
          templateResult: assetHeaderSearchResultTemplate, 
          templateSelection: formatAssetSelection 
    });
}

function init_department(){
    $(".delete-button").click(function(){
        var uri = $(this).data("uri");
        var departmentName = $(this).data("department-name");
        
        $("#delete-popup .delete-message").html("Are you sure to delete department <b>" + departmentName + 
          "</b>? All assets in this department will be set to 'No Department'. All transfer history will be discarded." +
          "Consider renaming the department into '<b>Department Name (Decommissioned)</b>'. This operation is not reversible. Continue?");
           
        $("#delete-popup .routes_uri").val(uri);
        $('#delete-popup').modal('show');
    });
    
    $(".update-button").click(function(){
        var uri = $(this).data("uri");
        var departmentName = $(this).data("department-name");
        
        $("#update-popup [name=departments_name]").val(departmentName);
        $("#update-popup .routes_uri").val(uri);
        $('#update-popup').modal('show');
    });
}

function init_category(){
    $(".delete-button").click(function(){
        var uri = $(this).data("uri");
        var categoryName = $(this).data("category-name");
        
        $("#delete-popup .delete-message").html("Are you sure to delete category <b>" + categoryName + 
          "</b>? All assets in this category will be unlinked. This operation is not reversible. Continue?");
           
        $("#delete-popup .routes_uri").val(uri);
        $('#delete-popup').modal('show');
    });
    
    $(".update-button").click(function(){
        var uri = $(this).data("uri");
        var categoryName = $(this).data("category-name");
        var lifespan = $(this).data("lifespan");
        var tracking = $(this).data("tracking");
        
        $("#update-popup [name=categories_name]").val(categoryName);
        $("#update-popup [name=lifespan_default]").val(lifespan);
        $("#update-popup [name=tracking_default]").val(tracking);
        $("#update-popup .routes_uri").val(uri);
        $('#update-popup').modal('show');
    });
}

function init_alert_recipient(){
    $(".delete-button").click(function(){
        var uri = $(this).data("uri");
        var email = $(this).data("email");
        
        $("#delete-popup .delete-message").html("Are you sure to delete email <b>" + email + 
          "</b>? This recipient will not be receiving write off request and maintenance notification anymore. Continue?");
           
        $("#delete-popup .routes_uri").val(uri);
        $('#delete-popup').modal('show');
    });
}

function init_user_view(){
    
    $(".delete-button").click(function(){
        var uri = $(this).data("uri");
        var role = $(this).data("role");
        
        $("#delete-popup .delete-message").html("Are you sure to unassign role <b>" + role + 
          "</b>? This user will not be able to access functions assigned to this role instantly. Continue?");
           
        $("#delete-popup .routes_uri").val(uri);
        $('#delete-popup').modal('show');
    });
}

function init_role_list(){
    
    $(".delete-button").click(function(){
        var uri = $(this).data("uri");
        var role = $(this).data("role");
        
        $("#delete-popup .delete-message").html("Are you sure to delete role <b>" + role + 
          "</b>? Any user with this role assigned will be unassigned instantly. Continue?");
           
        $("#delete-popup .routes_uri").val(uri);
        $('#delete-popup').modal('show');
    });
}

function init_role_view(){
    
    $(".delete-button").click(function(){
        var uri = $(this).data("uri");
        var user = $(this).data("user");
        var role = $(this).data("role");
        
        $("#delete-popup .delete-message").html("Are you sure to unassign user <b>" + user + 
          "</b>? This user will be unassigned from this role instantly. Continue?");
        
        if(role !== undefined){
            $("#delete-popup .delete-message").html("Are you sure to delete role <b>" + role + 
            "</b>? Any user with this role assigned will be unassigned instantly. Continue?");
        }
           
        $("#delete-popup .routes_uri").val(uri);
        $('#delete-popup').modal('show');
    });
}

function init_role_permission_view(){
    
    $(".change-permission-button").click(function(){
        
        var function_id = $(this).data("function-id");
        var function_name = $(this).data("function-name");
        var function_description = $(this).data("function-description");
        var dependencies = $(this).data("dependencies");
        var current_data = $("#function-input-" + function_id).val();
        current_data = jQuery.parseJSON(current_data);
        
        $("#add-popup .permission-title").html(function_name);
        $("#add-popup .permission-description").html(function_description);
        
        $("#add-popup .access-id").prop("checked", false);
        $("#add-popup .done-permission").data("function-id", function_id);
        
        if(dependencies == "*"){
            $("#add-popup .department-access-container").hide();
        }else{
            $("#add-popup .department-access-container").show();
        }
        
        if(jQuery.inArray("*", current_data) != -1){
           $("#add-popup .all-access").prop("checked", true); 
        }else{
            $("#add-popup .department-access").each(function(){
                if(jQuery.inArray($(this).val(), current_data) != -1){
                    $(this).prop("checked", true); 
                }
            });
        }
        
        $('#add-popup').modal('show');
    });
    
    $("#add-popup .done-permission").click(function(){
        
        var function_id = $(this).data("function-id");
        var access_id = new Array();
        var department_display = new Array(); 
        
        $("#add-popup .access-id:checked").each(function(){
            access_id.push($(this).val());
            department_display.push("<div class='green-text'><i class='fa fa-check-square-o'></i> <b>" + $(this).data("department-name") + "</b></div>");
        });
        
        if(jQuery.inArray("*", access_id) != -1){
            access_id = new Array("*");
            $("#function-apply-" + function_id).html("<div class='green-text'><i class='fa fa-check-square-o'></i> <b>All Access</b></div>");
        }else if(access_id.length == 0){
            $("#function-apply-" + function_id).html('<span class="red-text"><i class="fa fa-ban"></i> No Access</span>');
        }else{
            $("#function-apply-" + function_id).html(department_display.join(""));
        }
        
        var current_data = JSON.stringify(access_id);
        
        $("#function-input-" + function_id).val(current_data);
        
        $('#add-popup').modal('hide');
    });
}

function init_asset_list(){
    
    $("#download-popup .assets_dropdown").hide();
    
    $("#download-popup .add-location-dropdown").click(function(){
        
        var display = new Array();
        var j = -1;
        
        display[++j] = '<div class="dropdown-margin-padding">';
        display[++j] =  '<div class="col-md-4">';
        display[++j] =      '<select name="departments[]" class="form-control form-data">';
        
        $.each(operation_data["departments"], function(key, value){
            display[++j] = '<option value="' + key + '">' + value + '</option>';
        });
        
        display[++j] =      '</select>';   
        display[++j] =  '</div>';
        display[++j] =  '<i class="fa fa-times fa-2x record-delete"></i>';
        display[++j] =  '</div>';
        
        $("#download-popup .departments_list_dropdown").append(display.join(""));
    });
    
    $("#download-popup .add-all-departments").click(function(){
        
        /* Remove existing */
        $("#download-popup .departments_list_dropdown").empty();
        
        /* Insert all and select */
        $.each(operation_data["departments"], function(key, value){
            var current = key;
            var display = new Array();
            var j = -1;
            
            display[++j] = '<div class="dropdown-margin-padding">';
            display[++j] =  '<div class="col-md-4">';
            display[++j] =      '<select name="departments[]" class="form-control form-data">';
            
            $.each(operation_data["departments"], function(key, value){
                display[++j] = '<option ' + (current == key? "selected=selected":"") + ' value="' + key + '">' + value + '</option>';
            });
            
            display[++j] =      '</select>';   
            display[++j] =  '</div>';
            display[++j] =  '<i class="fa fa-times fa-2x record-delete"></i>';
            display[++j] =  '</div>';
            
            $("#download-popup .departments_list_dropdown").append(display.join(""));
        });
    });
    
    $("#download-popup .add-category-dropdown").click(function(){
        
        var display = new Array();
        var j = -1;
        
        display[++j] = '<div class="dropdown-margin-padding">';
        display[++j] =  '<div class="col-md-4">';
        display[++j] =      '<select name="categories[]" class="form-control form-data">';
        
        $.each(operation_data["categories"], function(key, value){
            display[++j] = '<option value="' + key + '">' + value.categories_name + '</option>';
        });
        
        display[++j] =      '</select>';   
        display[++j] =  '</div>';
        display[++j] =  '<i class="fa fa-times fa-2x record-delete"></i>';
        display[++j] =  '</div>';
        
        $("#download-popup .categories_list_dropdown").append(display.join(""));
    });
    
    $("#download-popup .add-all-categories").click(function(){
        
        /* Remove existing */
        $("#download-popup .categories_list_dropdown").empty();
        
        /* Insert all and select */
        $.each(operation_data["categories"], function(key, value){
            var current = key;
            var display = new Array();
            var j = -1;
            
            display[++j] = '<div class="dropdown-margin-padding">';
            display[++j] =  '<div class="col-md-4">';
            display[++j] =      '<select name="categories[]" class="form-control form-data">';
            
            $.each(operation_data["categories"], function(key, value){
                display[++j] = '<option ' + (current == key? "selected=selected":"") + ' value="' + key + '">' + value.categories_name + '</option>';
            });
            
            display[++j] =      '</select>';   
            display[++j] =  '</div>';
            display[++j] =  '<i class="fa fa-times fa-2x record-delete"></i>';
            display[++j] =  '</div>';
            
            $("#download-popup .categories_list_dropdown").append(display.join(""));
        });
    });
    
    $("#download-popup select[name=report_type]").change(function(){
        $("#download-popup .assets_dropdown").hide();
        $("#download-popup .include-department").hide();
        if($(this).val() == "asset_detail"){
            $("#download-popup .assets_dropdown").show();
            $("#download-popup .include-department").show();
        }
    });
    
    $("body").on("click", ".asset-search-result-row", function(event){
        
        event.preventDefault();
        event.stopPropagation();
        
        var assets_name = $(this).data("assets-name");
        var assets_id = $(this).data("assets-id");
        
        var display = new Array();
        var j = -1;
        
        display[++j] = "<div class='dropdown-margin-padding small-margin-left'>";
        
        display[++j] =      "<div class='col-md-5'><i class='fa fa-cubes'></i> " + assets_name + "</div>";
        
        display[++j] =      '<input type="hidden" name="assets[]" class="form-data" value="' + assets_id + '" />';
        
        display[++j] =      '<i class="fa fa-times fa-2x record-delete"></i>';
        
        display[++j] = "</div>";
       
        $("#download-popup .assets_list_container").append(display.join(""));
    });
    
    $("#assets-dropdown").select2({
        ajax: {
            url: site_url + "/admin/asset/asset/searchAsset",
            type: "POST",
            dataType: 'json',
            delay: 250,
            data: function (params){
                
                params.page = (params.page !== undefined)? params.page : 1;
                
                var inputData = {};
                inputData[token_name] = token_value;
                inputData["page_no"] = params.page;
                inputData["count_per_page"] = 20;
                inputData["sort"] = "asc";
                inputData["sort_field"] = "assets_name";
                inputData["term"] = params.term;
                
                return inputData;
            },
            processResults: function (data, params) {
                
                params.page = params.page || 1;
                
                if(data.success == 1){
                    
                    return {
                        results: data.data,
                        pagination: {
                            more: (data.data.length >= 20)
                        }
                    };
                }else{
                    return {
                        results: {},
                        pagination: {
                            more: false 
                        }
                    };   
                }
            },
            cache: true
          },
          closeOnSelect: true,
          escapeMarkup: function (markup) { return markup; }, 
          minimumInputLength: 1,
          templateResult: assetReportTemplate, 
          templateSelection: formatAssetSelection 
    });
    
    $(".print-button").click(function(){
        
        $("#print-popup .alert-danger").addClass("hide");
        $("#print-popup .error-message").html("");
        $(".stop-printing-button").addClass("hide");
        $(".stop-printing-button").data("continue", "1");
         
        //Verify Input
        var start = $("#start_number").val();
        var end = $("#end_number").val(); 
        if(!$.isNumeric(start) || !$.isNumeric(end)){
            $("#print-popup .error-message").html("Please enter valid numeric number only");
            $("#print-popup .alert-danger").removeClass("hide");    
            return false;
        } 
        
        start = Math.abs(parseInt(start));
        end = Math.abs(parseInt(end));
        
        $(".stop-printing-button").removeClass("hide");
        
        printEngine(start, end);
    });
    
    $(".stop-printing-button").click(function(){
        $(this).data("continue", "0");
    });
}

function printEngine(start, stop){
    var uri = $("#print-popup .routes_uri").val();
    var cont = $(".stop-printing-button").data("continue");
    
    if(start > stop){
        /* Complete */
        $("#print-popup .print-status").html("All printing commands have been sent to the printer.");
        $(".stop-printing-button").addClass("hide");
        $(this).data("continue", "0");
        return true;
    }
    
    if(cont == 0){
        /* Stop execution */
        $("#print-popup .print-status").html("Printing stopped.");
        $(".stop-printing-button").addClass("hide");
        $(this).data("continue", "0");
        return true;
    }
    
    var inputData = {};
    inputData[token_name] = token_value;
    inputData["assets_id"] = start;
    
    $.when(
        $.ajax({
            type: "POST",
            url: uri,
            data: inputData,
            dataType: 'json', 
        })
    ).done(function(data){
        
        if(data.success == 1){
            $("#print-popup .print-status").html("Printing asset ID: " + start + " of " + stop);
        }else{
            $("#print-popup .error-message").prepend(combineMessage(data.message));
            $("#print-popup .alert-danger").removeClass("hide");
        }
        
        /* Continue */
       printEngine(start + 1, stop);
        
    }).fail(function(jqXHR, textStatus){
        $("#print-popup .print-status").html("Error. Cannot connect to server. Printing stopped.");
        $(".stop-printing-button").addClass("hide");
        $(this).data("continue", "0");
    });
}

function init_asset_view(){
    
    $("a.photo").fancybox({
        fitToView   : true,
        width       : '80%',
        height      : '80%',
        autoSize    : true,
        closeClick  : true,
        openEffect  : 'elastic',
        closeEffect : 'elastic'
    });
    
    $("#departments-top-dropdown").select2({
        ajax: {
            url: site_url + "/admin/asset/asset/searchAsset",
            type: "POST",
            dataType: 'json',
            delay: 250,
            data: function (params){
                
                params.page = (params.page !== undefined)? params.page : 1;
                
                var inputData = {};
                inputData[token_name] = token_value;
                inputData["page_no"] = params.page;
                inputData["count_per_page"] = 20;
                inputData["sort"] = "asc";
                inputData["sort_field"] = "assets_name";
                inputData["term"] = params.term;
                
                return inputData;
            },
            processResults: function (data, params) {
                
                params.page = params.page || 1;
                
                if(data.success == 1){
                    
                    return {
                        results: data.data,
                        pagination: {
                            more: (data.data.length >= 20)
                        }
                    };
                }else{
                    return {
                        results: {},
                        pagination: {
                            more: false 
                        }
                    };   
                }
            },
            cache: true
          },
          escapeMarkup: function (markup) { return markup; }, 
          minimumInputLength: 1,
          templateResult: assetSearchResultTemplate, 
          templateSelection: formatAssetSelection 
    });
}

function init_asset_update(){
    $(".add-category-dropdown").click(function(){
        
        var display = new Array();
        var j = -1;
        
        display[++j] = '<div class="dropdown-margin-padding">';
        display[++j] =  '<div class="col-md-10">';
        display[++j] =      '<select name="categories[]" class="form-control">';
        
        $.each(operation_data["categories"], function(key, value){
            display[++j] = '<option value="' + key + '">' + value + '</option>';
        });
        
        display[++j] =      '</select>';        
        display[++j] =  '</div>';                                        
        display[++j] =  '<i class="fa fa-times fa-2x record-delete"></i>';
        display[++j] =  '</div>';
        
        $(this).parent().append(display.join(""));
        $(this).parent().append($(this));                                                  
    });
    
    Dropzone.forElement("div#drop-box").off("success");
    Dropzone.forElement("div#drop-box").on("success", function(file, data){
        
        if(data.success == 1){
            var attachments_id = data.data.attachment_id;
            
            $(".attachments-id-input").val(attachments_id);
            $(".photo").attr("href", site_url + "/admin/attachment/get/local_image/" + attachments_id + "/showall/1000/600/photo.png");
            $(".img-thumbnail").attr("src", site_url + "/admin/attachment/get/local_image/" + attachments_id + "/showall/500/150");
                
            return file.previewElement.classList.add("dz-success");
        }else{
            alert((combineMessage(data.message)));
            return file.previewElement.classList.add("dz-error");
        }    
    });
}

function init_asset_import(){
    
    Dropzone.forElement("div#drop-box").off("success");
    Dropzone.forElement("div#drop-box").on("success", function(file, data){
        
        if(data.success == "1"){
            var attachments_id = data.data.attachment_id;
            window.location.href = site_url + '/admin/asset/asset/importAssetPreview/' + attachments_id;
            return file.previewElement.classList.add("dz-success");
        }else{
            alert((combineMessage(data.message)));
            return file.previewElement.classList.add("dz-error");
        }    
    });
}

function init_asset_add(){
    
    $(".add-location-dropdown").click(function(){
        
        var display = new Array();
        var j = -1;
        
        display[++j] = '<div class="dropdown-margin-padding">';
        display[++j] =  '<div class="col-md-4">';
        display[++j] =      '<select name="departments[]" class="form-control">';
        
        $.each(operation_data["departments"], function(key, value){
            display[++j] = '<option value="' + key + '">' + value + '</option>';
        });
        
        display[++j] =      '</select>';   
        display[++j] =  '</div>';
        display[++j] =  '<div class="col-md-4">';          
        display[++j] =      '<input type="text" class="form-control" name="locations[]" value="" placeholder="Location" />';
        display[++j] =  '</div>';
        display[++j] =  '<div class="col-md-3">';          
        display[++j] =      '<input type="text" class="form-control" name="quantity[]" value="" placeholder="Quantity" />';
        display[++j] =  '</div>';                                              
        display[++j] =  '<i class="fa fa-times fa-2x record-delete"></i>';
        display[++j] =  '</div>';
        
        $(this).parent().append(display.join(""));
        $(this).parent().append($(this));                                                  
    });
    
    $("body").on('change', "select[name='categories[]']", function(){
        
        $("select[name='categories[]']").each(function(){
            var val = $(this).val();
             $.each(operation_data["categories_info"], function(key, value){
                if(val == key && value.tracking_default == 1){
                      $("select[name='enable_tracking']").val("1");
                } 
            });
        });
        
        var val = $(this).val();
        
        $.each(operation_data["categories_info"], function(key, value){
            if(val == key){
                $("input[name=assets_lifespan]").val(value.lifespan_default);
            } 
        });
    });
}

function init_maintenance_view(){
    
    $(".delete-button").click(function(){
        var uri = $(this).data("uri");
        var maintenanceDate = $(this).data("date");
        
        $("#delete-popup .delete-message").html("Are you sure to delete maintenance date <b>" + maintenanceDate + 
          "</b>? Notification email for this date will no longer be sent. Continue?");
           
        $("#delete-popup .routes_uri").val(uri);
        $('#delete-popup').modal('show');
    });
    
    $(".update-button").click(function(){
        var uri = $(this).data("uri");
        var maintenanceDate = $(this).data("date");
        
        $("#update-popup [name=maintenance_date]").val(maintenanceDate);
        $("#update-popup .routes_uri").val(uri);
        $('#update-popup').modal('show');
    });    
}

function init_transfer_view(){
    $("a.photo").fancybox({
        fitToView   : true,
        width       : '80%',
        height      : '80%',
        autoSize    : true,
        closeClick  : true,
        openEffect  : 'elastic',
        closeEffect : 'elastic'
    });    
    
    $(".update-button").click(function(){
        var uri = $(this).data("uri");
        var remark = $(this).data("remark");
        
        $("#update-popup [name=remark]").val(remark);
        $("#update-popup .routes_uri").val(uri);
        $('#update-popup').modal('show');
    });
    
    $("#download-popup .add-location-dropdown").click(function(){
        
        var display = new Array();
        var j = -1;
        
        display[++j] = '<div class="dropdown-margin-padding">';
        display[++j] =  '<div class="col-md-4">';
        display[++j] =      '<select name="departments[]" class="form-control form-data">';
        
        $.each(operation_data["departments"], function(key, value){
            display[++j] = '<option value="' + key + '">' + value + '</option>';
        });
        
        display[++j] =      '</select>';   
        display[++j] =  '</div>';
        display[++j] =  '<i class="fa fa-times fa-2x record-delete"></i>';
        display[++j] =  '</div>';
        
        $("#download-popup .departments_list_dropdown").append(display.join(""));
    });
    
    $("#download-popup .add-all-departments").click(function(){
        
        /* Remove existing */
        $("#download-popup .departments_list_dropdown").empty();
        
        /* Insert all and select */
        $.each(operation_data["departments"], function(key, value){
            var current = key;
            var display = new Array();
            var j = -1;
            
            display[++j] = '<div class="dropdown-margin-padding">';
            display[++j] =  '<div class="col-md-4">';
            display[++j] =      '<select name="departments[]" class="form-control form-data">';
            
            $.each(operation_data["departments"], function(key, value){
                display[++j] = '<option ' + (current == key? "selected=selected":"") + ' value="' + key + '">' + value + '</option>';
            });
            
            display[++j] =      '</select>';   
            display[++j] =  '</div>';
            display[++j] =  '<i class="fa fa-times fa-2x record-delete"></i>';
            display[++j] =  '</div>';
            
            $("#download-popup .departments_list_dropdown").append(display.join(""));
        });
    });
}

function init_writeoff_view(){
    $("a.photo").fancybox({
        fitToView   : true,
        width       : '80%',
        height      : '80%',
        autoSize    : true,
        closeClick  : true,
        openEffect  : 'elastic',
        closeEffect : 'elastic'
    });    
    
    $(".update-button").click(function(){
        var uri = $(this).data("uri");
        var remark = $(this).data("remark");
        var type = $(this).data("write-type");
        var quantity = $(this).data("quantity");
        var action = $(this).data("action");
        
        $("#update-popup [name=process_request]").val(action);
        $("#update-popup [name=remark]").val(remark);
        $("#update-popup [name=type]").val(type);
        $("#update-popup [name=quantity]").val(quantity);
        $("#update-popup .routes_uri").val(uri);
        $('#update-popup').modal('show');
    });
    
    $("#download-popup .add-location-dropdown").click(function(){
        
        var display = new Array();
        var j = -1;
        
        display[++j] = '<div class="dropdown-margin-padding">';
        display[++j] =  '<div class="col-md-4">';
        display[++j] =      '<select name="departments[]" class="form-control form-data">';
        
        $.each(operation_data["departments"], function(key, value){
            display[++j] = '<option value="' + key + '">' + value + '</option>';
        });
        
        display[++j] =      '</select>';   
        display[++j] =  '</div>';
        display[++j] =  '<i class="fa fa-times fa-2x record-delete"></i>';
        display[++j] =  '</div>';
        
        $("#download-popup .departments_list_dropdown").append(display.join(""));
    });
}

function init_transfer_add(){
    
    $(".update-button").click(function(){
        var uri = $(this).data("uri");
        var record_id = $(this).data("record-id");
        var department = $(this).data("department");
        var location = $(this).data("location");
        var avail_quantity = $(this).data("avail-quantity");
        
        var message = "<b>Transfer from " + department + "/" + location + " with available " + avail_quantity + " unit(s)</b>";
        
        $("#update-popup .update-message").html(message);
        $("#update-popup [name=assets_departments_id]").val(record_id);
        $("#update-popup .routes_uri").val(uri);
        $('#update-popup').modal('show');
    });
}

function init_writeoff_request(){
    
    $(".update-button").click(function(){
        
        /* Reset Form */
        $("#update-popup .form-data").val("");
        $("#update-popup .approval_workflow").empty();
        
        var uri = $(this).data("uri");
        var record_id = $(this).data("record-id");
        var department = $(this).data("department");
        var location = $(this).data("location");
        var avail_quantity = $(this).data("avail-quantity");
        var department_id = $(this).data("department-id");
        
        var message = "Write Off from " + department + "/" + location + " with available <b>" + avail_quantity + " unit(s)</b>";
        
        $("#update-popup .update-message").html(message);
        $("#update-popup [name=assets_departments_id]").val(record_id);
        $("#update-popup [name=departments_id]").val(department_id);
        $("#update-popup .routes_uri").val(uri);
        $('#update-popup').modal('show');
    });
    
    $("#update-popup .approval-workflow-add").click(function(){
        
        var department_id = $("#update-popup [name=departments_id]").val();
        
        var display = new Array();
        var j = -1;
        
        display[++j] = '<div class="dropdown-margin-padding">';
        display[++j] =  '<div class="col-md-4">';
        display[++j] =      '<select name="approvers[' + Date.now() + ']" class="form-control form-data">';
         
        $.each(operation_data["valid_users"], function(key, value){
            if(($.inArray("*", value.access_id) != -1) || ($.inArray(department_id, value.access_id) != -1)){
                display[++j] = '<option value="' + value.users_id + '">' + value.person_name + '</option>';
            }
        });
        
        display[++j] =      '</select>';   
        display[++j] =  '</div>';
        display[++j] =  '<i class="fa fa-times fa-2x record-delete"></i>';
        display[++j] =  '<div class="col-md-12" style="margin:10px; font-weight:bold; color:red"><i class="fa fa-chevron-down fa-fw"></i> Escalate To Next Approver</div>';
        display[++j] =  '</div>';
        
        $("#update-popup .approval_workflow").append(display.join(""));
    });
}

function init_discrepancy_view(){
    $("a.photo").fancybox({
        fitToView   : true,
        width       : '80%',
        height      : '80%',
        autoSize    : true,
        closeClick  : true,
        openEffect  : 'elastic',
        closeEffect : 'elastic'
    });    
    
    $('#discrepancy_compare_from').datepicker({
            "format": "dd-MM-yyyy"
    }).on("changeDate", function(e){
        var val = $(this).val();
        window.location.href = site_url + '/' + operation_data["uri"] + '/' + operation_data["target_id"] + "?from_date=" + val; 
    });
    
    $(".show-datepicker").click(function(){
        $('#discrepancy_compare_from').datepicker("show");
    });
    
    $("#download-popup .add-location-dropdown").click(function(){
        
        var display = new Array();
        var j = -1;
        
        display[++j] = '<div class="dropdown-margin-padding">';
        display[++j] =  '<div class="col-md-4">';
        display[++j] =      '<select name="departments[]" class="form-control form-data">';
        
        $.each(operation_data["departments"], function(key, value){
            display[++j] = '<option value="' + key + '">' + value + '</option>';
        });
        
        display[++j] =      '</select>';   
        display[++j] =  '</div>';
        display[++j] =  '<i class="fa fa-times fa-2x record-delete"></i>';
        display[++j] =  '</div>';
        
        $("#download-popup .departments_list_dropdown").append(display.join(""));
    });
}

function init_loan_view(){
    $("a.photo").fancybox({
        fitToView   : true,
        width       : '80%',
        height      : '80%',
        autoSize    : true,
        closeClick  : true,
        openEffect  : 'elastic',
        closeEffect : 'elastic'
    });    
    
    $(".update-button").click(function(){
        
        var uri = $(this).data("uri");
        var remark = $(this).data("remark");
        var quantity = $(this).data("quantity");
        var asset_name = $(this).data("asset-name");
        
        var message = "You are returning <b>" + asset_name + "</b> up to <b>" + quantity + "</b> unit(s).";
        
        $("#update-popup .update-message").html(message);
        $("#update-popup [name=remark]").val(remark);
        $("#update-popup [name=asset_name]").val(asset_name);
        $("#update-popup [name=quantity]").val(quantity);
        $("#update-popup .routes_uri").val(uri);
        $('#update-popup').modal('show');
    });
    
    $("#download-popup .add-location-dropdown").click(function(){
        
        var display = new Array();
        var j = -1;
        
        display[++j] = '<div class="dropdown-margin-padding">';
        display[++j] =  '<div class="col-md-4">';
        display[++j] =      '<select name="departments[]" class="form-control form-data">';
        
        $.each(operation_data["departments"], function(key, value){
            display[++j] = '<option value="' + key + '">' + value + '</option>';
        });
        
        display[++j] =      '</select>';   
        display[++j] =  '</div>';
        display[++j] =  '<i class="fa fa-times fa-2x record-delete"></i>';
        display[++j] =  '</div>';
        
        $("#download-popup .departments_list_dropdown").append(display.join(""));
    });
    
    $("#generate-form-popup .add-loaned-asset-dropdown").click(function(){
        
        var display = new Array();
        var j = -1;
        
        display[++j] = '<div class="dropdown-margin-padding">';
        display[++j] =  '<div class="col-md-11">';
        display[++j] =      '<select name="loaned_assets[]" class="form-control form-data">';
        
        $.each(operation_data["loan_data"], function(key, value){
            var display_str = value.departments_name + " " + value.formatted_date + " " + value.assets.assets_name + " [" + value.assets.barcode + "]" + " - " + value.loaned_quantity + " unit(s)";
            display[++j] = '<option value="' + key + '">' + display_str + '</option>';
        });
        
        display[++j] =      '</select>';   
        display[++j] =  '</div>';
        display[++j] =  '<i class="fa fa-times fa-2x record-delete"></i>';
        display[++j] =  '</div>';
        
        $("#generate-form-popup .loaned_list").append(display.join(""));
        
        /* Update fields if only one */
        if($("#generate-form-popup .loaned_list .form-data").length == 1){
            var record_id = $("#generate-form-popup .loaned_list .form-data").val();
            if(typeof operation_data.loan_data[record_id] != 'undefined'){
                var record = operation_data.loan_data[record_id];
                
                $("#generate-form-popup [name=form_department]").val(record.departments_id);
                $("#generate-form-popup [name=form_request_by]").val(record.borrower_entity);
                $("#generate-form-popup [name=form_purpose]").val(record.remark);
                $("#generate-form-popup [name=form_date_loan]").datepicker("setDate", new Date(record.datetime_created));
                $("#generate-form-popup [name=form_approver_name]").val(record.approver_name);
                $("#generate-form-popup [name=form_issued_by]").val(record.users.person_name);
                $("#generate-form-popup [name=form_borrower_name]").val(record.borrower_name);
                
            }
        }
    });
    
    /* List change */
    $("#generate-form-popup .loaned_list").on("change", "select[name='loaned_assets[]']", function(){
        var record_id = $(this).val();
        if(typeof operation_data.loan_data[record_id] != 'undefined'){
            var record = operation_data.loan_data[record_id];
            
            $("#generate-form-popup [name=form_department]").val(record.departments_id);
            $("#generate-form-popup [name=form_request_by]").val(record.borrower_entity);
            $("#generate-form-popup [name=form_purpose]").val(record.remark);
            $("#generate-form-popup [name=form_date_loan]").datepicker("setDate", new Date(record.datetime_created));
            $("#generate-form-popup [name=form_approver_name]").val(record.approver_name);
            $("#generate-form-popup [name=form_issued_by]").val(record.users.person_name);
            $("#generate-form-popup [name=form_borrower_name]").val(record.borrower_name);
        }
    });
    
    $("#generate-form-popup #form_date").datepicker("setDate", new Date());
}

function init_loan_add(){
    
     $(".update-button").click(function(){
        var uri = $(this).data("uri");
        var record_id = $(this).data("record-id");
        var department = $(this).data("department");
        var location = $(this).data("location");
        var avail_quantity = $(this).data("avail-quantity");
        
        var message = "You are loaning from <b>" + department + "/" + location + "</b> up to <b>" + avail_quantity + "</b> unit(s)";
        
        $("#update-popup .update-message").html(message);
        $("#update-popup [name=assets_departments_id]").val(record_id);
        $("#update-popup .routes_uri").val(uri);
        $('#update-popup').modal('show');
    });
}

function init_loan_form_download(){
    var form_url = operation_data["loan_form_url"];
    $("#download-form-popup .download-form").attr("href", form_url);
    $('#download-form-popup').modal('show');
}

function assetSearchResultTemplate(data){
    if (data.loading) return "Searching";
    
    var display = new Array(); 
    var j = -1;
    
    display[++j] = "<a href='" + site_url + "/" + operation_data["uri"] + "/" + data.assets_id + "?tab=" + operation_data["tab"] + "'>";
    display[++j] = "<div class='clearfix'>";
    display[++j] =      "<div class='pull-right clearfix search-right-box'>";
    display[++j] =          "<div class='search-department-container'>";
    display[++j] =              '<div class="search-department-display"><span class="fa fa-institution"></span>' + data.departments_name + '</div>';
    display[++j] =              '<i class="fa fa-angle-double-right"></i>';
    display[++j] =              '<div style="display: inline" class="font-14px">' + data.location + '</div>';
    display[++j] =              '<i class="fa fa-angle-double-right"></i>';
    display[++j] =              data.quantity + ' Unit(s)';
    display[++j] =          "</div>";
    display[++j] =          "<div style='text-align:right; font-weight: bold;'>";
    
    switch (data.status){
        case "available": 
                display[++j] = "<span class='green-text'><i class='fa fa-check'></i> Available</span>";
                break;
        case "write_off":
                display[++j] = "<span class='gray-text'><i class='fa fa-times'></i> Written Off</span>"; 
                break;
        case "loan_out":
                display[++j] = "<span class='red-text'><i class='fa fa-ban'></i> On Loan</span>"; 
                break;
        case "out_of_stock": 
                display[++j] = "<span class='red-text'><i class='fa fa-ban'></i> Out Of Stock</span>"; 
                break;
        case "maintenance":
                display[++j] = "<span class='red-text'><i class='fa fa-ban'></i> Maintenance</span>"; 
                break;
        case "unavailable":
                 display[++j] = "<span class='red-text'><i class='fa fa-ban'></i> Not Available</span>";
                break;
    }
    
    display[++j] =          "</div>";
    display[++j] =      "</div>";
    display[++j] =      "<div class='pull-left search-image-box'>";
    display[++j] =          '<img class="img-thumbnail" src="' + site_url + '/admin/attachment/get/local_image/' + data.attachments_id + '/crop/50/50"/>';
    display[++j] =      "</div>";
    display[++j] =      "<div class='search-asset-name'><b>" + data.assets_name + "</b></div>";
    display[++j] =      "<div class='search-asset-barcode'><i class='fa fa-key'></i> <b>Asset ID:</b> " + data.barcode + "</div>";
    display[++j] = "</div>";
    display[++j] = "</a>";
    
    return display.join("");
}

function assetHeaderSearchResultTemplate(data){
    if (data.loading) return "Searching";
    
    var display = new Array(); 
    var j = -1;
    
    display[++j] = "<a href='" + site_url + "/admin/asset/asset/viewIndividualAsset/" + data.assets_id + "'>";
    display[++j] = "<div class='clearfix'>";
    display[++j] =      "<div class='pull-right clearfix search-right-box'>";
    display[++j] =          "<div class='search-department-container'>";
    display[++j] =              '<div class="search-department-display"><span class="fa fa-institution"></span>' + data.departments_name + '</div>';
    display[++j] =              '<i class="fa fa-angle-double-right"></i>';
    display[++j] =              '<div style="display: inline" class="font-14px">' + data.location + '</div>';
    display[++j] =              '<i class="fa fa-angle-double-right"></i>';
    display[++j] =              data.quantity + ' Unit(s)';
    display[++j] =          "</div>";
    display[++j] =          "<div style='text-align:right; font-weight: bold;'>";
    
    switch (data.status){
        case "available": 
                display[++j] = "<span class='green-text'><i class='fa fa-check'></i> Available</span>";
                break;
        case "write_off":
                display[++j] = "<span class='gray-text'><i class='fa fa-times'></i> Written Off</span>"; 
                break;
        case "loan_out":
                display[++j] = "<span class='red-text'><i class='fa fa-ban'></i> On Loan</span>"; 
                break;
        case "out_of_stock": 
                display[++j] = "<span class='red-text'><i class='fa fa-ban'></i> Out Of Stock</span>"; 
                break;
        case "maintenance":
                display[++j] = "<span class='red-text'><i class='fa fa-ban'></i> Maintenance</span>"; 
                break;
        case "unavailable":
                 display[++j] = "<span class='red-text'><i class='fa fa-ban'></i> Not Available</span>";
                break;
    }
    
    display[++j] =          "</div>";
    display[++j] =      "</div>";
    display[++j] =      "<div class='pull-left search-image-box'>";
    display[++j] =          '<img class="img-thumbnail" src="' + site_url + '/admin/attachment/get/local_image/' + data.attachments_id + '/crop/50/50"/>';
    display[++j] =      "</div>";
    display[++j] =      "<div class='search-asset-name'><b>" + data.assets_name + "</b></div>";
    display[++j] =      "<div class='search-asset-barcode'><i class='fa fa-key'></i> <b>Asset ID:</b> " + data.barcode + "</div>";
    display[++j] = "</div>";
    display[++j] = "</a>";
    
    return display.join("");
}

function assetReportTemplate(data){
    if (data.loading) return "Searching";
    
    var display = new Array(); 
    var j = -1;
    
    display[++j] = "<a href='#' class='asset-search-result-row' data-assets-id='" + data.assets_id + "' data-assets-name='" +  data.assets_name + "'>";
    display[++j] = "<div class='clearfix'>";
    display[++j] =      "<div class='pull-right clearfix search-right-box'>";
    display[++j] =          "<div class='search-department-container'>";
    display[++j] =              '<div class="search-department-display"><span class="fa fa-institution"></span>' + data.departments_name + '</div>';
    display[++j] =              '<i class="fa fa-angle-double-right"></i>';
    display[++j] =              '<div style="display: inline" class="font-14px">' + data.location + '</div>';
    display[++j] =              '<i class="fa fa-angle-double-right"></i>';
    display[++j] =              data.quantity + ' Unit(s)';
    display[++j] =          "</div>";
    display[++j] =          "<div style='text-align:right; font-weight: bold;'>";
    
    switch (data.status){
        case "available": 
                display[++j] = "<span class='green-text'><i class='fa fa-check'></i> Available</span>";
                break;
        case "write_off":
                display[++j] = "<span class='gray-text'><i class='fa fa-times'></i> Written Off</span>"; 
                break;
        case "loan_out":
                display[++j] = "<span class='red-text'><i class='fa fa-ban'></i> On Loan</span>"; 
                break;
        case "out_of_stock": 
                display[++j] = "<span class='red-text'><i class='fa fa-ban'></i> Out Of Stock</span>"; 
                break;
        case "maintenance":
                display[++j] = "<span class='red-text'><i class='fa fa-ban'></i> Maintenance</span>"; 
                break;
        case "unavailable":
                 display[++j] = "<span class='red-text'><i class='fa fa-ban'></i> Not Available</span>";
                break;
    }
    
    display[++j] =          "</div>";
    display[++j] =      "</div>";
    display[++j] =      "<div class='pull-left search-image-box'>";
    display[++j] =          '<img class="img-thumbnail" src="' + site_url + '/admin/attachment/get/local_image/' + data.attachments_id + '/crop/50/50"/>';
    display[++j] =      "</div>";
    display[++j] =      "<div class='search-asset-name'><b>" + data.assets_name + "</b></div>";
    display[++j] =      "<div class='search-asset-barcode'><i class='fa fa-key'></i> <b>Asset ID:</b> " + data.barcode + "</div>";
    display[++j] = "</div>";
    display[++j] = "</a>";
    
    return display.join("");
}

function formatAssetSelection (data_row) {
    return data_row.full_name || data_row.text;
}

function init_uploader(){
    Dropzone.autoDiscover = false;
    $("div#drop-box").dropzone({ 
        url: site_url + "/admin/attachment/set",
        maxFilesize: 512,
        paramName: "attachment",
        acceptedFiles: "video/*,image/*,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,.xlsx",
        parallelUploads: 2,
        addRemoveLinks: true,
        autoProcessQueue: true,
        sending: function(file, xhr, formData) {    
            formData.append(token_name, token_value);
        },
        error: function(file, errorMessage, xhr){
            alert("Error: " + errorMessage);
            return file.previewElement.classList.add("dz-error");
        }
    });
}

function openDepartment(uri){
    var departments_id = $("#departments-top-dropdown").val();
    window.location.href = site_url + '/' + uri + '/' + departments_id + ((operation_data["department_tab"] !== undefined)? ("?tab=" + operation_data["department_tab"]) : ""); 
}
