(function(jQuery){

    jQuery.extend({
        createUploadIframe: function (id, uri) {
            //create frame
            var frameId = 'jUploadFrame' + id;
            var iframeHtml = '<iframe id="' + frameId + '" name="' + frameId + '" style="position:absolute; top:-9999px; left:-9999px"';
            if (window.ActiveXObject) {
                if (typeof uri == 'boolean') {
                    iframeHtml += ' src="' + 'javascript:false' + '"';

                }
                else if (typeof uri == 'string') {
                    iframeHtml += ' src="' + uri + '"';

                }
            }
            iframeHtml += ' />';
            jQuery(iframeHtml).appendTo(document.body);

            return jQuery('#' + frameId).get(0);
        },
        createUploadForm: function (id, fileElementId, data) {
            //create form
            var formId = 'jUploadForm' + id;
            var fileId = 'jUploadFile' + id;
            var form = jQuery('<form  action="" method="POST" name="' + formId + '" id="' + formId + '" enctype="multipart/form-data"></form>');
            if (data) {
                for (var i in data) {
                    jQuery('<input type="hidden" name="' + i + '" value="' + data[i] + '" />').appendTo(form);
                }
            }
            var oldElement = jQuery('#' + fileElementId);
            var newElement = jQuery(oldElement).clone();
            jQuery(oldElement).attr('id', fileId);
            jQuery(oldElement).before(newElement);
            jQuery(oldElement).appendTo(form);


            //set attributes
            jQuery(form).css('position', 'absolute');
            jQuery(form).css('top', '-1200px');
            jQuery(form).css('left', '-1200px');
            jQuery(form).appendTo('body');
            console.log(form.get(0));
            return form;
        },

        ajaxFileUpload: function (s) {
            // TODO introduce global settings, allowing the client to modify them for all requests, not only timeout
            s = jQuery.extend({}, jQuery.ajaxSettings, s);
            var id = new Date().getTime()
            var form = jQuery.createUploadForm(id, s.fileElementId, (typeof(s.data) == 'undefined' ? false : s.data));
            var io = jQuery.createUploadIframe(id, s.secureuri);
            var frameId = 'jUploadFrame' + id;
            var formId = 'jUploadForm' + id;
            // Watch for a new set of requests
            if (s.global && !jQuery.active++) {
                jQuery.event.trigger("ajaxStart");
            }
            var requestDone = false;
            // Create the request object
            var xml = {}
            if (s.global)
                jQuery.event.trigger("ajaxSend", [xml, s]);
            // Wait for a response to come back
            var uploadCallback = function (isTimeout) {
                var io = document.getElementById(frameId);
                try {
                    if (io.contentWindow) {
                        xml.responseText = io.contentWindow.document.body ? io.contentWindow.document.body.innerHTML : null;
                        xml.responseXML = io.contentWindow.document.XMLDocument ? io.contentWindow.document.XMLDocument : io.contentWindow.document;

                    } else if (io.contentDocument) {
                        xml.responseText = io.contentDocument.document.body ? io.contentDocument.document.body.innerHTML : null;
                        xml.responseXML = io.contentDocument.document.XMLDocument ? io.contentDocument.document.XMLDocument : io.contentDocument.document;
                    }
                    console.warn(xml);
                } catch (e) {
                    console.warn('error-5');
                    jQuery.handleError(s, xml, null, e);
                }
                if (xml || isTimeout == "timeout") {
                    requestDone = true;
                    var status;
                    try {
                        status = isTimeout != "timeout" ? "success" : "error";
                        // Make sure that the request was successful or notmodified
                        if (status != "error") {
                            // process the data (runs the xml through httpData regardless of callback)
                            var data = jQuery.uploadHttpData(xml, s.dataType);
                            // If a local callback was specified, fire it and pass it the data
                            if (s.success)
                                s.success(data, status);

                            // Fire the global callback
                            if (s.global)
                                jQuery.event.trigger("ajaxSuccess", [xml, s]);
                        } else{
                            console.log('error-4');
                            jQuery.handleError(s, xml, status);
                        }
                    } catch (e) {
                        console.log('error-3');
                        status = "error";
                        jQuery.handleError(s, xml, status, e);
                    }

                    // The request was completed
                    if (s.global)
                        jQuery.event.trigger("ajaxComplete", [xml, s]);

                    // Handle the global AJAX counter
                    if (s.global && !--jQuery.active)
                        jQuery.event.trigger("ajaxStop");

                    // Process result
                    if (s.complete)
                        s.complete(xml, status);
                    console.log("io-Info:");
                    console.log(io);
                    console.log("io-Info-end");
                    jQuery(io).unbind()

                    setTimeout(function () {
                        try {
                            jQuery(io).remove();
                            jQuery(form).remove();

                        } catch (e) {
                            console.log('error-2');
                            jQuery.handleError(s, xml, null, e);
                        }

                    }, 100);

                    xml = null

                }
            }
            // Timeout checker
            if (s.timeout > 0) {
                setTimeout(function () {
                    // Check to see if the request is still happening
                    if (!requestDone) uploadCallback("timeout");
                }, s.timeout);
            }
            try {

                var form = jQuery('#' + formId);
                jQuery(form).attr('action', s.url);
                jQuery(form).attr('method', 'POST');
                jQuery(form).attr('target', frameId);
                if (form.encoding) {
                    jQuery(form).attr('encoding', 'multipart/form-data');
                    console.log('encoding : multipart/form-data');
                }
                else {
                    jQuery(form).attr('enctype', 'multipart/form-data');
                    console.log('enctype : multipart/form-data');
                }
                jQuery(form).submit();

            } catch (e) {
                console.log('error-1');
                jQuery.handleError(s, xml, null, e);
            }

            jQuery('#' + frameId).load(uploadCallback);
            return {abort: function () {
            }};

        },

        uploadHttpData: function (r, type) {
            var data = !type;
            data = type == "xml" || data ? r.responseXML : r.responseText;
            // If the type is "script", eval it in global context
            if (type == "script")
                jQuery.globalEval(data);
            // Get the JavaScript object, if JSON is used.
            if (type == "json")
                eval("data = " + data);
            // evaluate scripts within html
            if (type == "html")
                jQuery("<div>").html(data).evalScripts();

            return data;
        },

        handleError: function (s, xhr, status, e) {
            console.warn('---error-list');
            console.warn(s);
            console.warn(xhr);
            console.warn(status);
            console.warn(e);
            console.warn('---error-list-end');
            if (s.error) {
                s.error.call(s.context || s, xhr, status, e);
            }
            if (s.global) {
                (s.context ? jQuery(s.context) : jQuery.event).trigger("ajaxError", [xhr, s, e]);
            }
        }
    })
})(jQuery);


/*
* url 接口底值
* id
* upid
* errstr
* doing
* done
* */
function uploadFile(url, id, upid, errstr, doing, done) {
    var $ = jQuery
    var fileItem = $("#"+id);
    //var elementId = id+'file';
    var elementId = fileItem.data('id');
    var fileInput = fileItem.find('.input-file').attr({'id':elementId,'name':elementId});
    var valueInput = fileItem.find('.hidden').attr({'id':upid});
    var fileResult = fileItem.find('.file-result');
    fileResult.html(doing);
    console.log(arguments);
    console.log(elementId);

    $.ajaxFileUpload(
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
                    console.log(data.data)
                    valueInput.val(data.data);
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
                alert(errstr + ": " + e);
            }
        }
    )
} 
