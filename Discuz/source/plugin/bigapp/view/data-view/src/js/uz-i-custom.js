(function($){
    $(function(){

        var sameOrigin = window.location.host == v[mType.set].split('http://')[1].split('/')[0] ? 1 : 0;
        getCustomData(v[mType.get]);

        function getSuccess(data){
            if(data.error_code){
                alert(data.error_msg);
            }else{
                var str = '';
                var newId = 0;
                str+='<tbody class="base-list">';
                for(var i in data.data){
                    var o = data.data[i];
                    newId = parseInt(o.id) +1;
                    str+='<tr class="hover data-edit" data-id="' + o.id + '">';
                    str+='    <td><input class="checkbox" name="option" type="checkbox" name="" value="' + o.title + '"></td>';
                    str+='    <td><input type="text" name="title" class="txt" value="' + o.title + '" placeholder=""></td>';
                    str+='    <td>';
                    str+='        <div class="yzd-input-file" id="' + pageType.value + o.id +'" data-id="' + pageType.value +'">';
                    str+='            <button class="btn-file">选择图片</button>';
                    str+='            <input type="file" onchange="uploadFile(\''+ v[pageType.key] +'\', \'' + pageType.value + o.id +'\', \'' + pageType.value +'\', \'文件上传失败\', \'uploading...\', \'OK\')" class="input-file">';
                    str+='            <input type="text" name="pic" value="' + o.pic + '" class="hidden" hidden="">';
                    str+='            <div class="file-result">点击更改图片</div>';
                    str+='        </div>';
                    str+='    </td>';
                    str+='    <td><input type="text" class="txt input-url" name="url" value="' + o.url + '" placeholder=""></td>';
                    str+='    <td>';
                    str+='        <select name="type">';
                    str+='            <option value="1" ' + (o.type == 1 ? 'selected="selected"' : '') + '>站外链接</option>';
                    str+='            <option value="2" ' + (o.type == 2 ? 'selected="selected"' : '') + '>帖子链接</option>';
                    str+='            <option value="3" ' + (o.type == 3 ? 'selected="selected"' : '') + '>板块链接</option>';
                    str+='        </select>';
                    str+='    </td>';
                    str+='    <td><input type="text" name="desc" class="txt input-num"  value="' + o.desc + '" placeholder=""></td>';
                    str+='    <td><input type="text" name="order" class="txt input-num"  value="' + o.order + '" placeholder=""></td>';
                    str+='</tr>';
                }
                str+='</tbody><tbody class="option-list">';
                str+='        <tr class="hover data-new" data-id="' + newId + '">';
                str+='            <td>新增</td>';
                str+='            <td><input name="title" type="text" class="txt" size="15" value="" placeholder=""></td>';
                str+='            <td>';
                str+='                <div class="yzd-input-file" id="newImg" data-id="' + pageType.value +'">';
                str+='                    <button class="btn-file">选择图片</button>';
                str+='                    <input type="file" onchange="uploadFile('+ v[pageType.key] +', \'newImg\', ' + pageType.value +', \'文件上传失败\', \'uploading...\', \'OK\')" class="input-file">';
                str+='                    <input type="text" name="pic" class="hidden" value="http://www.youzu.com" hidden="">';
                str+='                    <div class="file-result">未选择任何文件</div>';
                str+='                </div>';
                str+='            </td>';
                str+='            <td><input type="text" name="url" class="txt input-url" value="123" placeholder=""></td>';
                str+='            <td>';
                str+='                <select name="type">';
                str+='                    <option value="1">站外链接</option>';
                str+='                    <option value="2">帖子链接</option>';
                str+='                    <option value="3">板块链接</option>';
                str+='                </select>';
                str+='            </td>';
                str+='            <td><input type="text" class="txt input-num" name="desc" value="0" placeholder=""></td>';
                str+='            <td><input type="text" class="txt input-num" name="order" value="0" placeholder=""></td>';
                str+='        </tr>';
                str+='        <tr class="data-submit">';
                str+='            <td class="td25"><input type="checkbox" name="chkall" id="chkallMp3E" class="checkbox"><span name="editSubmit" class="list-del">删?</span>';
                str+='            </td>';
                str+='            <td colspan="15">';
                str+='                <div class="fixsel"><input type="submit" class="btn" name="editSubmit" title="按 Enter 键可随时提交您的修改" value="提交"></div>';
                str+='            </td>';
                str+='        </tr></tbody>';
            }
            $('#setList').append(str);

            // 事件描述
            $('#setList .data-edit').each(function(){
                $(this).find('[name="option"]').click(function(){
                    if(!$(this).get(0).checked){
                        $('#setList .data-submit [name="chkall"]').get(0).checked = false;
                    }
                });
            });
            $('#setList .data-new').each(function(){

            });
            $('#setList .data-submit [name="chkall"]').click(function(){
                var cAll = $(this).get(0);
                $('#setList .data-edit input[name="option"]').each(function(){
                    $(this).get(0).checked = cAll.checked;
                });
            })
            $('#setList [name="editSubmit"]').click(function(){
                if($(this).hasClass('list-del')){
                    $('.data-edit').each(function(){
                        if($(this).find('[name="option"]').get(0).checked){
                            $(this).addClass('data-del');
                        }
                    });
                }
                setCustomData(v[mType.set]);
            });
        }

        function getError(data){
            console.log(data);
        }

        function setSuccess(data){
            if(data.error_code){
                alert(data.error_msg);
            }else{
                $('.base-list,.option-list').remove();
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
            if(!sameOrigin){
                $.extend(ajaxJson,ajaxJsonP);
            }
            $.ajax(ajaxJson);
        }

        function setCustomData(url){
            // 验证表单
            var b = formValidate();
            if(b === false){
                return ;
            }
            console.log(b);
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
            console.log(ajaxJson);
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
                    desc: o.find('[name="desc"]').val(),
                    pid: 0,
                    order: o.find('[name="order"]').val()
                };
                data.id = o.data('id');
                if(o.hasClass('data-new')){
                    if(!(data.title && data.pic && data.url)){
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
                            console.log(i);
                            if(!data[i].isURL()){
                                error_txt = '请上传图片！';
                            }else{
                                data[i] = encodeURIComponent(data[i]);
                            }
                            break;
                        case 'url' :
                            console.log(i);
                            if(!data[i].isURL()){
                                error_txt = '请填写链接地址！';
                            }else{
                                data[i] = encodeURI(data[i]);
                            }
                            break;
                        case 'desc' :
                            if(data[i] === undefined || data[i] === ''){
                                error_txt = '请填写描述！'
                            }else{
                                data[i] = encodeURIComponent(data[i]);
                            }
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
})(jq);
