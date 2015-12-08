function uploadFile(url, id, upid, errstr, doing, done) {
    var jq = jQuery.noConflict();
    var fileItem = jq("#"+id);
    //var elementId = id+'file';
    var elementId = fileItem.data('id');
    var fileInput = fileItem.find('.input-file').attr({'id':elementId,'name':elementId});
    var valueInput = fileItem.find('.hidden').attr({'id':upid});
    var fileResult = fileItem.find('.file-result');
    fileResult.html(doing);
    console.log(arguments);
    console.log(elementId);
//    jq.ajaxFileUpload(
//        {
//            url: url,
//            secureuri: false,
//            fileElementId: elementId,
//            dataType: 'json',
//            timeout: 30000,
//            success: function (data, status) {
//                fileItem.addClass('done');
//                if (data.error_code == 0) {
//                    valueInput.val(data.data.imgurl);
//                    fileResult.html(done);
//                    //var img = '<div class="img-show"><img src="'+data.data.imgurl+'"></div>';
//                    //fileResult.append(img);
//                } else {
//                    if (typeof(data.error_msg) != 'undefined') {
//                        fileResult.html(errstr + ": " + data.error_msg);
//                    } else {
//                        fileResult.html(errstr + ': invalid returned format');
//                    }
//                }
//            },
//            error: function (data, status, e) {
//                fileResult.html('Error');
//                alert(errstr + ": " + e);
//            }
//        }
//    )
    //jsonp

    jq.ajaxFileUpload(
        {
            url: url,
            secureuri: false,
            async: false,
            fileElementId: elementId,
            type: 'get',
            dataType: 'json',
//            jsonp: 'callback',
//            jsonpCallback:'callback',
            timeout: 30000,
            success: function (data, status) {
//                var data = JSON.parse(jsonp.split('callback(')[1].substring(0,jsonp.split('callback(')[1].length-1));
                console.log(data);
                fileItem.addClass('done');
                if (data.error_code == 0) {
                    valueInput.val(data.data.imgurl);
                    fileResult.html(done);
                    //var img = '<div class="img-show"><img src="'+data.data.imgurl+'"></div>';
                    //fileResult.append(img);
                } else {
                    if (typeof(data.error_msg) != 'undefined') {
                        fileResult.html(errstr + ": " + data.error_msg);
                    } else {
                        fileResult.html(errstr + ': invalid returned format');
                    }
                }
            },
            error: function (data, status, e) {
                fileResult.html('Error');
                if(e){
                    alert(errstr + ": " + e);
                }else{
                    alert(errstr + ": Server not response");
                }
            }
        }
    )
} 
