var store,grid;

    function query() {
        store.baseParams = {
            "key": get_value("so-key")
        };
        grid.load();
    }

    function init_grid() {
        var doso = query;
        store = new MWT.Store({
            "url": v.ajaxapi+"&do=query"
        });
        grid = new MWT.Grid({
            render: "grid-div",
            store: store,
            pagebar: true,
            pageSize: 20,
            multiSelect:false, 
            bordered: true,
            cm: new MWT.Grid.ColumnModel([
              {head:"ID", dataIndex:"id", width:80, hide:true},
              {head:"发送时间", dataIndex:"sendtime", width:150,sort:true},
              {head:"手机号", dataIndex:"phone", width:100, sort:true},
              {head:"短信内容", dataIndex:"msg", sort:true, render:function(v){
                return v;
              }}
            ]),
            tbar: [
              {type:"search",label:"查询",id:"so-key",class:'mwt-search-primary',width:500,handler:query,placeholder:"搜索手机号和短信内容"}
            ]
        });
        grid.create();
        query();
    }

$(document).ready(function(){ 
    init_grid();
}); 
