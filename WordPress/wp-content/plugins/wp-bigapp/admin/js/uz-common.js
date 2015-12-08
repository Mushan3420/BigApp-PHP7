var Uz;
String.prototype.Trim = function() {
    return this.replace(/(^\s*)|(\s*$)/g, "");
};
//判断是否匹配手机
String.prototype.isMobile = function() {
    var val = this;
    if (!val.match(/^1[3|4|5|7|8][0-9]\d{4,8}$/) || val.length != 11  || val=="") {
        return false;
    } else {
        return true;
    }
};
//判断是否匹配邮箱
String.prototype.isEmail = function() {
    var val = this;
    if (!val.match(/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/) || val == "") {
        return false;
    } else {
        return true;
    }
};
//判断是否匹配字符a-z0-9A-Z
String.prototype.isWords = function() {
    var val = this;
    if (val.match(/[A-Za-z0-9]+$/)[0]!=val || val=="") {
        return false;
    } else {
        return true;
    }
};
//判断是否匹配数字
String.prototype.isNumber = function() {
    var val = this.toString();
    if (val.match(/\d+$/)!=val || val=="") {
        return false;
    } else {
        return true;
    }
};
//判断是否匹配URL
String.prototype.isURL = function() {
    var val = this.toString();
    var b = val.match(/((http|ftp|https|file):\/\/(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z_!~*'()-]+\.)*([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.[a-z]{2,6})(:[0-9]{1,4})?(\/[\w\u4e00-\u9fa5\-\.\/?\@\%\!\&=\+\~\:\#\;\,]*)?)/);

    if (val!='' && b != null && b[0] == val) {
        return true;
    } else {
        return false;
    }
};

//base64编码解码方法
var base64 = {
    base64EncodeChars: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
    base64DecodeChars: new Array(-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 62, -1, -1, -1, 63, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, -1, -1, -1, -1, -1, -1, -1, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, -1, -1, -1, -1, -1, -1, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, -1, -1, -1, -1, -1),
    /**
     * base64编码
     * @param {Object} str
     */
    encode: function(str) {
        var out, i, len;
        var c1, c2, c3;
        len = str.length;
        i = 0;
        out = "";
        while (i < len) {
            c1 = str.charCodeAt(i++) & 0xff;
            if (i == len) {
                out += this.base64EncodeChars.charAt(c1 >> 2);
                out += this.base64EncodeChars.charAt((c1 & 0x3) << 4);
                out += "==";
                break;
            }
            c2 = str.charCodeAt(i++);
            if (i == len) {
                out += this.base64EncodeChars.charAt(c1 >> 2);
                out += this.base64EncodeChars.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
                out += this.base64EncodeChars.charAt((c2 & 0xF) << 2);
                out += "=";
                break;
            }
            c3 = str.charCodeAt(i++);
            out += this.base64EncodeChars.charAt(c1 >> 2);
            out += this.base64EncodeChars.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
            out += this.base64EncodeChars.charAt(((c2 & 0xF) << 2) | ((c3 & 0xC0) >> 6));
            out += this.base64EncodeChars.charAt(c3 & 0x3F);
        }
        return out;
    },
    /**
     * base64解码
     * @param {Object} str
     */
    decode: function(str) {
        var c1, c2, c3, c4;
        var i, len, out;
        len = str.length;
        i = 0;
        out = "";
        while (i < len) {
            /* c1 */
            do {
                c1 = this.base64DecodeChars[str.charCodeAt(i++) & 0xff];
            }
            while (i < len && c1 == -1);
            if (c1 == -1)
                break;
            /* c2 */
            do {
                c2 = this.base64DecodeChars[str.charCodeAt(i++) & 0xff];
            }
            while (i < len && c2 == -1);
            if (c2 == -1)
                break;
            out += String.fromCharCode((c1 << 2) | ((c2 & 0x30) >> 4));
            /* c3 */
            do {
                c3 = str.charCodeAt(i++) & 0xff;
                if (c3 == 61)
                    return out;
                c3 = this.base64DecodeChars[c3];
            }
            while (i < len && c3 == -1);
            if (c3 == -1)
                break;
            out += String.fromCharCode(((c2 & 0XF) << 4) | ((c3 & 0x3C) >> 2));
            /* c4 */
            do {
                c4 = str.charCodeAt(i++) & 0xff;
                if (c4 == 61)
                    return out;
                c4 = this.base64DecodeChars[c4];
            }
            while (i < len && c4 == -1);
            if (c4 == -1)
                break;
            out += String.fromCharCode(((c3 & 0x03) << 6) | c4);
        }
        return out;
    },

    //for deal 中文乱码 @tyy
    utf16to8: function(str) {
        var out, i, len, c;

        out = "";
        len = str.length;
        for(i = 0; i < len; i++) {
            c = str.charCodeAt(i);
            if ((c >= 0x0001) && (c <= 0x007F)) {
                out += str.charAt(i);
            } else if (c > 0x07FF) {
                out += String.fromCharCode(0xE0 | ((c >> 12) & 0x0F));
                out += String.fromCharCode(0x80 | ((c >>  6) & 0x3F));
                out += String.fromCharCode(0x80 | ((c >>  0) & 0x3F));
            } else {
                out += String.fromCharCode(0xC0 | ((c >>  6) & 0x1F));
                out += String.fromCharCode(0x80 | ((c >>  0) & 0x3F));
            }
        }
        return out;
    },

    utf8to16: function(str) {
        var out, i, len, c;
        var char2, char3;

        out = "";
        len = str.length;
        i = 0;
        while(i < len) {
            c = str.charCodeAt(i++);
            switch(c >> 4)
            {
                case 0: case 1: case 2: case 3: case 4: case 5: case 6: case 7:
                // 0xxxxxxx
                out += str.charAt(i-1);
                break;
                case 12: case 13:
                // 110x xxxx   10xx xxxx
                char2 = str.charCodeAt(i++);
                out += String.fromCharCode(((c & 0x1F) << 6) | (char2 & 0x3F));
                break;
                case 14:
                    // 1110 xxxx  10xx xxxx  10xx xxxx
                    char2 = str.charCodeAt(i++);
                    char3 = str.charCodeAt(i++);
                    out += String.fromCharCode(((c & 0x0F) << 12) |
                        ((char2 & 0x3F) << 6) |
                        ((char3 & 0x3F) << 0));
                    break;
            }
        }

        return out;
    }
};
(function($){

    Uz = function () {
        var errorDialog = function(txt){
            alert(txt);
        }
        var checkAllTrue = function(name){
            var f = true;
            $(name).each(function(){
                if($(this).get(0).checked == false){
                    f = false;
                }
            })
            return f;
        };
        var getUrlParam = function(name) {
            var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); // 构造一个含有目标参数的正则表达式对象
            var r = window.location.search.substr(1).match(reg); // 匹配目标参数
            if (r != null)
                return decodeURIComponent(r[2]);
            return null; // 返回参数值
        };
        var setMenu = function(v,url){
            var menuEle = $('#menu-settings-column');
            var confsEle = $('#menu-management');

            var menuSelect = menuEle.find('.accordion-section-content select');
            var confsList = confsEle.find('#the-list');
            var menuData = v.menu_conf.nav_menus.list;
            var confsData = v.menu_conf.menu_confs || [];
            var setConfsData = confsData;

            var ns='.tpl-';
            var show_nav_info = function (v) {
                $(ns + 'version').html(v.plugin_info.version);
                $(ns + 'updatetime').html(v.plugin_info.updatetime);
                $(ns + 'innerversion').html(v.plugin_info.inner_version);
            };
            show_nav_info(v);


            var selectIndex = 0;
            menuSelect.change(function(e){
                selectIndex = $(this).val();
            });
            $('.submit-add-to-menu').click(function(){
                addMenu(menuData[selectIndex].item_list);
            });
            function addMenu(item){
                console.log(item);
                $.each(item,function(i,val){
                    var f = checkListId(val.ID);
                    if(f){
                        val.org_name = val.name;
                        setConfsData.push(val);
                        confsList.append(getConfItem(val));
                    }
                })

            }
            function checkListId(id){
                var f = true;
                $.each(setConfsData,function(i,val){
                    if(val.ID == id){
                        f = false;
                    }
                })
                return f;
            };
            $('.menu-save').click(function(){
                var error_txt ='';
                var postData = [];
                var f = true;
                $('.conf-item').removeClass('error').each(function(i){
                    var o = $(this);
                    if(!f){
                        return;
                    }
                    if(o.find('.check-column input').get(0).checked){
                        return;
                    }else{
                        setConfsData[i].rank = o.find('input[name="rank"]').val();
                        setConfsData[i].name = o.find('input[name="name"]').val();
                        for(var j in setConfsData[i]){
                            if(error_txt!=''){
                                break;
                            }
                            switch(j){
                                case 'name' :
                                    if(setConfsData[i][j] === undefined || setConfsData[i][j] === ''){
                                        error_txt = 'App菜单名称不能为空！'
                                    }else{
                                        setConfsData[i][j] = (setConfsData[i][j]);
                                    }
                                    break;
                                case 'rank' :
                                    if(!setConfsData[i][j].isNumber()){
                                        setConfsData[i][j]=0;
                                    }
                                    break;
                            }
                        }
                        if(error_txt!=''){
                            f = false;
                            errorDialog(error_txt);
                            o.addClass('error');
                        }else{
                            postData.push(setConfsData[i]);
                        }
                    }
                });
                console.log(postData);
                if(f){
                    $.ajax({
                        type: 'post',
                        url: url,
                        dataType: 'json',
                        data:{ menu_confs : postData },
                        success: function(data){
                            console.log(data);
                            if(data.data){
                                confsList.empty();
                                $.each(postData,function(i,val){
                                    confsList.append(getConfItem(val));
                                });
                                alert('修改成功');
                            }else{
                                alert('内部错误');
                            }
                        }
                    })
                }
            })

            $('#cb-select-all').click(function(){
                var cAll = $(this).get(0);
                $('.conf-item input').each(function(){
                    $(this).get(0).checked = cAll.checked;
                });
            });
            $.each(menuData,function(i,val){
                var str = '<option value="'+i+'" data-id="' + val.ID + '">'+val.name+'</option>'
                menuSelect.append(str);
            });
            if(confsData)
            $.each(confsData,function(i,val){
                confsList.append(getConfItem(val));
            });

            function getConfItem(val){
                var str = ''+
                    '<tr class="conf-item">'+
                    '    <th scope="row" class="check-column">'+
                    '        <input type="checkbox" value="'+val.ID+'">'+
                    '    </th>'+
                    '    <td>'+
                    '        <input type="text" name="rank" value="'+(val.rank ? val.rank:0)+'">'+
                    '    </td>'+
                    '    <td>'+ decodeURIComponent(val.org_name?val.org_name:val.name)+'</td>'+
                    '    <td>'+
                    '        <input type="text" name="name" value="'+val.name+'">'+
                    '    </td>'+
                    '    <td><a href="'+v.menu_conf.opt_url+'&menu_id='+val.ID+'&menu_name='+encodeURIComponent(val.name)+'" class="button">内容设置</a></td>'+
                    '</tr>';
                var newObj = $(str);
                return newObj;
            }

        };
        var setBanner = function(data){
            var v = data.data
            var url = data.ajax_url;
            var imgUrl = data.upload_url;
            var confsEle = $('#menu-management');
            var menu_id = this.getUrlParam('menu_id');
            var menu_name = this.getUrlParam('menu_name');
            $('.major-publishing-actions h3').html(menu_name+'-Banner设置');
            var banner_length = 0;
            var baseData = {
                "name": "",
                "ID": '',
                "type": "",
                "img_url": "",
                "link": "",
                "rank": 0,
                "show": true
            }

            var ns='.tpl-';
            var show_nav_info = function (v) {
                $(ns + 'version').html(v.plugin_info.version);
                $(ns + 'updatetime').html(v.plugin_info.updatetime);
                $(ns + 'innerversion').html(v.plugin_info.inner_version);
            };
            show_nav_info(v);
            var confsList = confsEle.find('#the-list');
            console.log(v);
            var confsData = v.banner_conf.banner_list || [];


            // Add new item
            $('.menu-add').click(function(){
                addMenu();
            });
            function addMenu(){
                var newObj = getConfItem(0);
                confsList.append(newObj);
            }

            // Save submit
            $('.menu-save').click(function(){
                var postData = [];
                var f = true;
                $('.conf-item').removeClass('error').each(function(){
                    var o = $(this);
                    if(!f){
                        return;
                    }
                    var error_txt ='';
                    var d = {
                        "name": "",
                        "ID": '',
                        "type": "",
                        "img_url": "",
                        "link": "",
                        "rank": 0,
                        "show": true
                    };
                    if(o.find('.check-column-1 input').get(0).checked){
                        return;
                    }

                    d.rank = o.find('input[name="rank"]').val();
                    d.name = o.find('input[name="name"]').val();
                    d.link = o.find('input[name="link"]').val();
                    d.type = o.find('select[name="type"]').val();
                    d.img_url = o.find('input[name="img_url"]').val();
                    d.show = o.find('input[name="show"]').get(0).checked;
                    for(var i in d){
                        if(error_txt!=''){
                            break;
                        }
                        switch(i){
                            case 'name' :
                                if(d[i] === undefined || d[i] === ''){
                                    error_txt = '标题不能为空！'
                                }else{
                                    d[i] = encodeURIComponent(d[i]);
                                }
                                break;
                            case 'img_url' :
                                if(!d[i].isURL()){
                                    console.log(i);
                                    error_txt = '图片不能为空，请上传图片！';
                                }else{
                                    d[i] = encodeURI(d[i]);
                                }
                                break;
                            case 'link' :
                                if(!d[i].isURL()){
                                    console.log(i);
                                    error_txt = '请填写正确格式的链接地址！';
                                }else{
                                    d[i] = d[i];
                                }
                                break;
                            case 'rank' :
                                if(!d[i].isNumber()){
                                    d.rank=0;
                                }
                                break;
                        }
                    }

                    if(error_txt!=''){
                        f = false;
                        errorDialog(error_txt);
                        o.addClass('error');
                    }else{
                        postData.push(d);
                    }
                });
                console.log(postData);
                if(f){
                    $.ajax({
                        type: 'post',
                        url: url,
                        dataType: 'json',
                        data:{ banner_conf : postData, menu_id:menu_id },
                        success: function(data){
                            console.log(data);
                            if(data.data){
                                init(postData);
                                alert('修改成功');
                            }else{
                                alert('内部错误');
                            }
                        }
                    })
                }
            });
            init(confsData)
            // Init
            function init(data){
                $("#the-list").html('');
                $.each(data,function(i,val){
                    confsList.append(getConfItem(val));
                    var s = true;
                    $('.check-column-2 input').each(function(){
                        if($(this).get(0).checked == false){
                            s = false;
                        }
                    })
                    $('#cb-select-all-2').get(0).checked = s;
                });
            }


            $('#cb-select-all-1').click(function(){
                var cAll = $(this).get(0);
                $('.check-column-1 input').each(function(){
                    $(this).get(0).checked = cAll.checked;
                });
            })
            $('#cb-select-all-2').click(function(){
                var cAll = $(this).get(0);
                $('.check-column-2 input').each(function(){
                    $(this).get(0).checked = cAll.checked;
                });
            })
            function getConfItem(value){
                var val;
                if(value == 0){
                    val = baseData;
                }else{
                    val = value;
                }
                var str = ''+
                    '<tr class="conf-item" data-id="'+banner_length+'">'+
                    '    <th scope="row" class="check-column-1">'+
                    '        <input type="checkbox" value="'+banner_length+'">'+
                    '    </th>'+
                    '    <th scope="row" class="check-column-2">'+
                    '        <input type="checkbox" name="show" '+(val.show == 'true' || val.show === true?'checked="checked"':'')+'>'+
                    '    </th>'+
                    '    <td>'+
                    '        <input type="text" name="name" value="'+decodeURIComponent(val.name)+'">'+
                    '    </td>'+
                    '    <td>'+
                    '        <div class="yzd-input-file" id="img_up_'+ banner_length+'" data-id="upload_img_'+ banner_length+'">'+
                    '            <button class="btn-file">选择图片</button>'+
                    '            <input type="file" onchange="uploadFile(\'' + imgUrl + '&key=upload_img_' + banner_length + '\' , \'img_up_'+ banner_length+'\' , \'img_url_'+ banner_length+'\' , \'文件上传失败\', \'uploading...\', \'OK\');" class="input-file">'+
                    '            <input type="text" name="img_url" value="' + val.img_url + '" class="hidden" hidden="">'+
                    '            <div class="file-result">' + ( val.img_url == '' ? '点击选择图片' :'点击更改图片') + '</div>'+
                    '        </div>'+
                    '    </td>'+
                    '    <td>'+
                    '        <input type="text" name="link" value="'+val.link+'">'+
                    '    </td>'+
                    '    <td>'+
                    '                <select name="type">'+
                    '                    <option  value="1" ' + (val.type == 1 ? 'selected="selected"' : '') + '>站外链接</option>'+
                    '                    <option  value="2" ' + (val.type == 2 ? 'selected="selected"' : '') + '>文章链接</option>'+
                    '                    <option  value="3" ' + (val.type == 3 ? 'selected="selected"' : '') + '>菜单链接</option>'+
                    '                </select>'+
                    '    </td>'+
                    '    <td>'+
                    '        <input type="text" name="rank" value="'+(val.rank ? val.rank:0)+'">'+
                    '    </td>'+
                    '</tr>';
                var newObj = $(str);


                newObj.find('.check-column-1 input').click(function(){
                    if($(this).get(0).checked == false){
                        $('#cb-select-all-1').get(0).checked = false;
                    }else{
                        if(checkAllTrue('.check-column-1 input')){
                            $('#cb-select-all-1').get(0).checked = true;
                        }
                    }
                });
                newObj.find('.check-column-2 input').click(function(){
                    if($(this).get(0).checked == false){
                        $('#cb-select-all-2').get(0).checked = false;
                    }else{
                        if(checkAllTrue('.check-column-2 input')){
                            $('#cb-select-all-2').get(0).checked = true;
                        }
                    }
                });

                banner_length++;

                return newObj;
            }
        };
        return{
            ns: '.tpl-',
            getUrlParam: getUrlParam,
            checkAllTrue: checkAllTrue,
            setBanner: setBanner,
            setMenu: setMenu,
            drawIndex: function(data,urlList) {
                var ns = this.ns;
                var info = data.plugin_info;
                var updateData = function (data) {
                    $(ns + 'version').html(info.version);
                    $(ns + 'updatetime').html(info.updatetime);
                    $(ns + 'innerversion').html(info.inner_version);
                    $(ns + 'notice').html(data.common_info.notice);

                    $.each(info.app_infos, function (i, val) {
                        var o = $('#uz .' + val.type);
                        if(o.length){
                            o.show();
                            o.find(ns + 'app-name').html(val.name);
                            o.find(ns + 'app-logo').attr('src', val.logo);
                            var qrcodeObj = o.find('.qrcode').get(0);
                            $(qrcodeObj).empty()
                            var qrcode = new QRCode(qrcodeObj, {
                                width: 120,
                                height: 120,
                                colorDark: "#000000",
                                colorLight: "#ffffff",
                                correctLevel: QRCode.CorrectLevel.H
                            });
                            qrcode.makeCode(val.dcode_url);
                        }
                    });

                    $('#uz #menu_info').each(function () {
                        var o = $(this);
                        var val = data.menu_info;
                        if (val.menu_switch == '1') {
                            o.removeClass('inactive').addClass('active');
                            o.find(ns + 'switch').attr('title', '停用该功能').html('停用');
                        } else {
                            o.removeClass('active').addClass('inactive');
                            o.find(ns + 'switch').attr('title', '启用该功能').html('启用');
                        }
                        o.find(ns + 'link').attr('href', val.opt_url);

                    });
					
					$('#uz #extend_info').each(function () {
                        var o = $(this);
                        var val = data.extend_info;
                        if (val.menu_switch == '1') {
                            o.removeClass('inactive').addClass('active');
                            o.find(ns + 'switch').attr('title', '停用该功能').html('停用');
                        } else {
                            o.removeClass('active').addClass('inactive');
                            o.find(ns + 'switch').attr('title', '启用该功能').html('启用');
                        }
                        o.find(ns + 'link').attr('href', val.opt_url);

                    });

                    //verify_info
                    var appKey = $('#appKey');
                    var appSecret = $('#appSecret');
                    var verify = data.verify_info;
                    appKey.val(verify.ak);
                    appSecret.val(verify.sk);
                    if ( verify.ak == '' ){
                        $('#submit').attr("value","获取appkey");
                        $('#submit').attr("onclick","location.href='http://bigapp.youzu.com'");
                    }

                };
                updateData(data);

                $('#uz #menu_info').find(ns + 'switch').click(function () {
                    var d = {
                        menu_switch: 0
                    }
                    var val = data.menu_info;
                    if (val.menu_switch != '1') {
                        d.menu_switch = 1;
                    }
                    $.ajax({
                        type: 'post',
                        url: urlList.opt_menu,
                        dataType: 'json',
                        data: d,
                        success: function (json) {
                            console.log(json);
                            if (json.data) {
                                val.menu_switch = d.menu_switch;
                                updateData(data);
                            }
                        }
                    })
                });

                //verify_info submit
                $("#submit").click(function(){
                    var s = $(this);
                    var v = {
                        ak : $('#appKey').val().trim(),
                        sk : $('#appSecret').val().trim()
                    };
                    console.log(urlList)
                    $.ajax({
                        type: 'post',
                        url:  urlList.opt_verify,
                        dataType: 'json',
                        data: v,
                        success: function (json) {
                            console.log(json);
                            if(json.error_code == 0){
                                s.val('保存成功!');
                                alert('修改成功');
                            }else{
                                alert(json.error_msg);
                            }
                        }
                    })
                });
            }
        }
    }
})(jQuery)
var uz = new Uz(jQuery);
//    demo json
