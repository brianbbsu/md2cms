// update a row with given data
function update_row(data){
    id = data['id'];
    content = "<td><a target=\"_blank\" href=\"" + data['url'] + "\">" + id + "</a></td>";
    if(data['short_name'] === "")content+='<td colspan="5">Hackmd Task Statement Metadata Not Found</td>';
    else
    {
        content+="<td>" + data['short_name'] + "</td>";
        content+='<td><a class="ui primary basic compact mini button" id="preview-btn-' + id + '" onclick="request_preview(' + id + ')">Preview</i></a></td>'
        if(data['task_name'] === "")content+='<td colspan="3">CMS Task Not Found</td>';
        else
        {
            content+="<td>" + data['task_name'] + "</td>";
            if(data['same'])content+='<td class="positive"><i class="check icon"></i></td>'
            else content+='<td class="negative"><i class="times icon"></i></td>'
            content += '<td><a class="ui positive basic compact mini button" onclick="request_replace(' + id + ',\'' + data['short_name'] + '\')">Replace</i></a></td>'

        }
    }
    $('#' + id).html(content);
};

// send request to view pdf of task with given task id
// id => numeric id of the task
function request_preview(id){
    btn_name = "preview-btn-" + String(id);
    if($('#'+btn_name).hasClass("loading"))return;
    $('#'+btn_name).addClass("loading");
    $.ajax({
            method: "POST",
            dataType: "json",
            url: window.location.href,
            data: {
                "id" : String(id),
                "type" : "preview"
            },
            success: function(d){
                btn_name = "preview-btn-" + d['id'];
                $('#'+btn_name).removeClass("loading");
                window.open(window.location.href + "?problem_id=" + d['id'],"_blank");
            }
        });
};

// send request to update CMS task with given task id
function request_replace(id,short_name){
    $('#' + String(id)).html('<td>' + String(id) + '</td><td colspan="5"><div class="ui inline active tiny loader"></div></td>');
    $.ajax({
            method: "POST",
            dataType: "json",
            url: window.location.href,
            data: {
                "id" : String(id),
                "type" : "replace",
                "short_name" : short_name
            },
            success: function(d){
                update_row(d);
            }
    });
};

// Init, get status of each task
$( document ).ready(function(){
    for(var i = 1;i <= task_cnt; i++){
        $.ajax({
            method: "POST",
            dataType: "json",
            url: window.location.href,
            data: {
                "id" : String(i),
                "type" : "query"
            },
            success: function(d){
                update_row(d);
            }
        });
    }
});
