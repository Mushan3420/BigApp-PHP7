console.log(v);
function uploadFileCustom(id,fileid){
    var $ = jq;
    var o = $('#tr_'+id);
    var val = o.find('select').val()-1;
    uploadFile(v[pageType.imgSize[val]]+id+'1' , fileid , pageType.imgSize[val] , '文件上传失败', 'uploading...', 'OK');
}
(function($,window){

    $(function(){

        var sameOrigin = window.location.host == v[mType.set].split('http://')[1].split('/')[0] ? 1 : 0;
        getCustomData(v[mType.get]);

        function changeType(o,val){
            var i = '.tips_'+val;
            o.find('.tips_item').hide();
            o.find(i).show();
        }
        function getSuccess(data){
            if(data.error_code){
                alert(data.error_msg);
            }else{
                var str = '';
                var newId = 0;
                str+='        <thead class="table-head">';
                str+='        <tr class="header">';
                str+='            <th class="tac">删除</th>';
                str+='            <th class="tac">显示</th>';
                str+='            <th>名称(<font style="color:red;font-weight:bold">必填</font>)</th>';
                str+='            <th>图片(<font style="color:red;font-weight:bold">必填</font>)</th>';
                str+='            <th>链接URL(<font style="color:red;font-weight:bold">必填</font>)</th>';
                str+='            <th>类别(<font style="color:red;font-weight:bold">必填</font>)</th>';
                if(v.descVisible){
                    str+='            <th>描述信息(<font style="color:red;font-weight:bold">必填</font>)</th>';
                }
                str+='            <th>排序</th>';
                str+='        </tr>';
                str+='        </thead>';
                str+='<tbody class="base-list">';
                for(var i in data.data){
                    var o = data.data[i];
                    newId = parseInt(o.id) +1;
                    str+='<tr class="hover data-edit" data-id="' + o.id + '" id="tr_' + o.id + '">';
                    str+='    <td class="tac"><input class="checkbox" name="option" type="checkbox" name="" value=""></td>';
                    str+='   <td class="tac"><input type="checkbox" class="checkbox" name="status" '+ (o.status == 1?'checked="checked"':'')+'"></td>';
                    str+='    <td><input type="text" name="title" class="txt" value="' + o.title + '" placeholder=""></td>';
                    str+='    <td>';
                    str+='        <div class="yzd-input-file" id="' + pageType.value + o.id +'" data-id="' + pageType.value + o.id +'1">';
                    str+='            <button class="btn-file">选择图片</button>';
                    str+='            <input type="file" onchange="uploadFileCustom(\'' + o.id +'\' , \'' + pageType.value + o.id +'\')" class="input-file">';
                    str+='            <input type="text" name="pic" value="' + o.pic + '" class="hidden" hidden="">';
                    str+='            <div class="file-result">点击更改图片</div>';
                    str+='        </div>';
                    str+=picTips;
                    str+='    </td>';
                    str+='    <td><input type="text" class="txt input-url" name="url" value="' + o.url + '" placeholder=""></td>';
                    str+='    <td>';
                    str+='        <select name="type">';
                    str+='            <option data-value='+pageType.imgSize[0]+' value="1" ' + (o.type == 1 ? 'selected="selected"' : '') + '>站外链接</option>';
                    str+='            <option data-value='+pageType.imgSize[1]+' value="2" ' + (o.type == 2 ? 'selected="selected"' : '') + '>帖子链接</option>';
                    str+='            <option data-value='+pageType.imgSize[2]+' value="3" ' + (o.type == 3 ? 'selected="selected"' : '') + '>板块链接</option>';
                    str+='        </select>';
                    str+='    </td>';
                    if(v.descVisible){
                        str+='    <td>' +
                            '<input type="text" name="desc" class="txt" size="10"  value="' + o.desc + '" placeholder="">' +
                            '<div class="tips_item">10个字以内</div>'+
                            '</td>';
                    }
                    str+='    <td><input type="text" name="order" class="txt input-num"  value="' + o.order + '" placeholder=""></td>';
                    str+='</tr>';
                }
                str+='</tbody><tbody class="option-list">';
                str+='        <tr class="hover data-new" data-id="' + newId + '" id="tr_' + newId + '">';
                str+='            <td class="tac">新增</td>';
                str+='            <td class="tac"><input type="checkbox" class="checkbox" name="status" checked="checked"></td>';
                str+='            <td><input name="title" type="text" class="txt" size="15" value="" placeholder=""></td>';
                str+='            <td>';
                str+='                <div class="yzd-input-file" id="newImg" data-id="' + pageType.value + newId +'1">';
                str+='                    <button class="btn-file">选择图片</button>';
                str+='                      <input type="file" onchange="uploadFileCustom(\'' + newId +'\' ,\'newImg\')" class="input-file">';
//                str+='                    <input type="file" onchange="uploadFile(\''+ v[pageType.key] +'\', \'newImg\', \'' + pageType.value +'\', \'文件上传失败\', \'uploading...\', \'OK\')" class="input-file">';
                str+='                    <input type="text" name="pic" class="hidden" value="" hidden="">';
                str+='                    <div class="file-result">未选择任何文件</div>';
                str+='                </div>';
                str+=picTips;
                str+='            </td>';
                str+='            <td><input type="text" name="url" class="txt input-url" value="" placeholder=""></td>';
                str+='            <td>';
                str+='                <select name="type">';
                str+='                    <option data-value='+pageType.imgSize[0]+' selected="selected" value="1">站外链接</option>';
                str+='                    <option data-value='+pageType.imgSize[1]+' value="2">帖子链接</option>';
                str+='                    <option data-value='+pageType.imgSize[2]+' value="3">板块链接</option>';
                str+='                </select>';
                str+='            </td>';
                if(v.descVisible){
                    str+='    <td>' +
                        '<input type="text" name="desc" class="txt" size="10"  value="" placeholder="">' +
                        '<div class="tips_item">10个字以内</div>'+
                        '</td>';
                }
                str+='            <td><input type="text" class="txt input-num" name="order" value="0" placeholder=""></td>';
                str+='        </tr>';
                str+='        <tr class="data-submit">';
                str+='            <td class="td25"><label><input type="checkbox" name="chkall" id="option" class="checkbox"><span class="list-del">全选</span></label>';
                str+='            </td>';
                str+='            <td class="td25"><label><input type="checkbox" name="chkall" id="status" class="checkbox"><span class="list-del">全选</span></label>';
                str+='            </td>';
                str+='        </tr>';
                str+='        <tr class="data-submit">';
                str+='            <td colspan="15">';
                str+='                <div class="fixsel"><input type="submit" class="btn" name="editSubmit" title="按 Enter 键可随时提交您的修改" value="提交"></div>';
                str+='            </td>';
                str+='        </tr></tbody>';
            }
            $('#setList').append(str);

            // 事件描述
            $('#setList .data-edit,#setList .data-new').each(function(){
                var o = $(this);
                $(this).find('[name="option"]').click(function(){
                    if(!$(this).get(0).checked){
                        $('#setList .data-submit [name="chkall"]').get(0).checked = false;
                    }
                });
                changeType(o,$(this).find('option[selected="selected"]').data('value'));
                $(this).find('select').change(function(){
                    changeType(o,$(this).find('option').eq($(this).val()-1).data('value'));
                })
            });
            $('#setList .data-submit [name="chkall"]').click(function(){
                var cAll = $(this).get(0);
                var name = $(this).attr('id');
                $('#setList .data-edit input[name="'+name+'"]').each(function(){
                    $(this).get(0).checked = cAll.checked;
                });
            })
            $('#setList [name="editSubmit"]').click(function(){
                if(!$(this).hasClass('list-del')){
                    setSwitch($('[name="switchBlock"]:checked').val());
                }
                $('.data-edit').each(function(){
                    if($(this).find('[name="option"]').get(0).checked){
                        $(this).addClass('data-del');
                    }
                });
                setCustomData(v[mType.set]);
            });
        }
        function setSwitch(val){
            console.log(val);
            var ajaxJson = {
                type: "post",
                url: v.setSwitch+'&switch='+val,
                dataType: "json",
                success: function (data) {
                    console.log(data);
                },
                error: function (data) {
                    console.log(data);
                }
            };
            var ajaxJsonP = {
                dataType: "jsonp",
                jsonp: 'callback',
                jsonpCallback: 'jsonp'
            };
            if(!sameOrigin){
                $.extend(ajaxJson,ajaxJsonP);
            }
            $.ajax(ajaxJson);
        }

        function getError(data){
            console.log(data);
        }

        function setSuccess(data){
            if(data.error_code){
                alert(data.error_msg);
            }else{
                alert('设置成功');
                $('#setList').html('');
                getSuccess(data);
            }
        }

        function setError(data){
            console.log(data);
        }

        function getCustomData(url){
            var ajaxJson = {
                type: "get",
                url: url,
                dataType: "json",
                success: function (data) {
                    getSuccess(data);
//                getSuccess(a);
                },
                error: function (data) {
                    getError(data);
                }
            };
            var ajaxJsonP = {
                dataType: "jsonp",
                jsonp: 'callback',
                jsonpCallback: 'jsonp'
            };
            var switchAjax = {
                type: "get",
                url: v.getSwitch,
                dataType: "json",
                success: function (data) {
                    if(data.error_code == 0){
                        $('[name="switchBlock"]').eq(data.data.switch?0:1).trigger('click');
                    }
                },
                error: function (data) {
                    console.log(data);
                }
            }
            if(!sameOrigin){
                $.extend(ajaxJson,ajaxJsonP);
                $.extend(switchAjax,ajaxJsonP);
            }
            $.ajax(ajaxJson);
            $.ajax(switchAjax);
        }

        function setCustomData(url){
            // 验证表单
            var b = formValidate();
            if(b === false){
                return ;
            }
            console.log(JSON.stringify(b));
            var ajaxJson = {
                type: "post",
                url: url,
                data : {settings : base64.encode(JSON.stringify(b))},
                dataType: "json",
                success: function (data) {
                    setSuccess(data,b);
                },
                error: function (data) {
                    setError(data);
                }
            };
            var ajaxJsonP = {
                dataType: "jsonp",
                jsonp: 'callback',
                jsonpCallback: 'jsonp'
            };
            if(!sameOrigin){
                $.extend(ajaxJson,ajaxJsonP);
            }
            $.ajax(ajaxJson);
        }

        function formValidate(){
            // 收集页面数据
            var postData = [];
            var flag = true;
            var error_txt = '';

            $('#setList .data-edit ,#setList .data-new').each(function(){
                var o = $(this);
                if(o.hasClass('data-del')){
                    return ;
                }
                var data = {
                    id: o.data('id'),
                    title: o.find('[name="title"]').val(),
                    pic: o.find('[name="pic"]').val(),
                    url: o.find('[name="url"]').val(),
                    type: o.find('[name="type"]').val(),
                    pid: 0,
                    order: o.find('[name="order"]').val(),
                    status: o.find('[name="status"]').get(0).checked ? 1 : 0
                };

                if(v.descVisible){
                    data.desc = o.find('[name="desc"]').val();
                }
                data.id = o.data('id');
                console.log(data);
                if(o.hasClass('data-new')){
                    if(!(data.title && data.pic && data.url)){
                        console.log('none new');
                        return;
                    }
                }

                for(var i in data){
                    if(error_txt!=''){
                        return;
                    }
                    switch(i){
                        case 'title' :
                            if(data[i] === undefined || data[i] === ''){
                                error_txt = '请填写链接名称！'
                            }else{
                                data[i] = encodeURIComponent(data[i]);
                            }
                            break;
                        case 'pic' :
                            if(!data[i].isURL()){
                                console.log(i);
                                error_txt = '请上传图片！';
                            }else{
                                data[i] = encodeURI(data[i]);
                            }
                            break;
                        case 'url' :
                            if(!data[i].isURL()){
                                console.log(i);
                                error_txt = '请填写正确格式的链接地址！';
                            }else{
                                data[i] = encodeURI(data[i]);
                            }
                            break;
                        case 'desc' :
                            data[i] = encodeURIComponent(data[i]);
                            break;
                        case 'order' :
                            if(!data[i].isNumber()){
                                data.order=0;
                            }
                            break;
                    }
                }
                postData.push(data);
            });
            if(error_txt!=''){
                errorDialog(error_txt);
                return false;
            }else{
                return postData;
            }
        }

        function errorDialog(txt){
            alert(txt);
        }
    })
})(jq,window);
