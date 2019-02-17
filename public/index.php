<?php
    define('BASE_DIR', dirname(__FILE__, 2).'/');
    include_once(BASE_DIR.'config.php');
    define('PROBLEM_CNT', count(HACKMD_URL));
    if(!file_exists(BASE_DIR.'files')){
        mkdir(BASE_DIR.'files');
    }
    function qt(){
        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
        die();
    }
    function fix_chinese($s){
        return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
        },$s);
    }
    function return_pdf($name){
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename='.$name);
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        readfile(BASE_DIR."files/".$name);
        exit(0);
    }
    function db_connection_string(){
        $res = Array();
        foreach(DB as $k => $v){
            array_push($res, $k."=".$v);
        }
        return implode($res, " ");
    }
    function diff_local($md, $base){
        if(!file_exists($base.".md") || !file_exists($base.".pdf"))return 1;
        else if(filemtime($base.".md") > filemtime($base.".pdf"))return 1;
        else if($md != file_get_contents($base.".md"))return 1;
        return 0;
    }
    function diff_cms($md, $id, $short_name){
        $base = BASE_DIR."files/".$id;
        if(diff_local($md, $base))return 1;
        $conn = pg_connect(db_connection_string());
        $result = pg_query($conn,"SELECT tasks.id FROM tasks WHERE name = '".pg_escape_string($short_name)."';");
        $arr = pg_fetch_all($result);
        if(empty($arr)){
            pg_close($conn);
            qt();
        }
        $task_id = $arr[0]['id'];
        $result = pg_query($conn,"SELECT digest FROM statements WHERE task_id = ".strval($task_id).";");
        $arr = pg_fetch_all($result);
        if(empty($arr)){
            pg_close($conn);
            return 1;
        }
        $old_pdf_digest = $arr[0]['digest'];
        $new_pdf_digest = sha1(file_get_contents($base.".pdf"));
        if($old_pdf_digest != $new_pdf_digest)return 1;
        else return 0;
    }
    function compile_markdown($id){
        $base = BASE_DIR."files/".$id;
        $md = file_get_contents(HACKMD_URL[$id].'/download');
        if(diff_local($md, $base)){
            file_put_contents($base.".md",$md);
            exec("PATH=/usr/bin pandoc ".escapeshellarg($base.".md")." --template ".BASE_DIR."template.tex -o ".escapeshellarg($base.".pdf")." -f markdown -t latex -s --latex-engine=xelatex 2>&1",$out,$ret);
            if($ret != 0){
                var_dump($out);
                qt();
            }
        }
    }
    function replace_statement($id,$short_name){
        $conn = pg_connect(db_connection_string());
        $result = pg_query($conn,"SELECT tasks.id FROM tasks WHERE name = '".pg_escape_string($short_name)."';");
        $arr = pg_fetch_all($result);
        if(empty($arr)){
            pg_close($conn);
            qt();
        }
        $task_id = $arr[0]['id'];
        $result = pg_query($conn,"SELECT digest FROM statements WHERE task_id = ".strval($task_id).";");
        $arr = pg_fetch_all($result);
        if(empty($arr))
        {
            $loid = pg_lo_create($conn);
            $pdf_location = BASE_DIR."files/$id.pdf";
            $pdf = file_get_contents($pdf_location);
            $new_pdf_digest = sha1($pdf);
            pg_lo_unlink($conn,intval($loid));
            pg_query($conn,"SELECT LO_IMPORT('$pdf_location',".strval($loid).");");
            pg_query($conn,"INSERT INTO fsobjects (loid,digest,description) VALUES (".strval($loid).",'$new_pdf_digest','Statement for task ".pg_escape_string($short_name)." (lang: TWN)');");
            pg_query($conn,"INSERT INTO statements (task_id,language,digest) VALUES (".strval($task_id).",'TWN','$new_pdf_digest');");
        }
        else
        {
            $old_pdf_digest = $arr[0]['digest'];
            $result = pg_query($conn,"SELECT loid FROM fsobjects WHERE digest = '${old_pdf_digest}';");
            $loid = pg_fetch_all($result)[0]['loid'];
            pg_lo_unlink($conn,intval($loid));
            $pdf_location = BASE_DIR."files/$id.pdf";
            $new_pdf_digest = sha1(file_get_contents($pdf_location));
            pg_query($conn,"SELECT LO_IMPORT('$pdf_location',".strval($loid).");");
            pg_query($conn,"UPDATE fsobjects SET digest = '${new_pdf_digest}' WHERE digest = '${old_pdf_digest}';");
            pg_query($conn,"UPDATE statements SET digest = '${new_pdf_digest}' WHERE digest = '${old_pdf_digest}';");
        }
        pg_close($conn);
    }
    function get_cms_taskname($short_name){
        $conn = pg_connect(db_connection_string());
        $result = pg_query($conn,"SELECT tasks.title FROM tasks WHERE name = '".pg_escape_string($short_name)."';");
        $arr = pg_fetch_all($result);
        pg_close($conn);
        if(empty($arr))return "";
        else return $arr[0]['title'];
    }
    function query_task($id){
        $ret = Array("id" => $id,"short_name" => "","task_name" => "","task_statement" => "", "url" => "");
        $base = BASE_DIR."files/".$id;
        $ret["url"] = HACKMD_URL[$id];
        $md = file_get_contents(HACKMD_URL[$id].'/download');
        if(preg_match('/problemId: +([-a-zA-Z0-9_]+)/',$md,$match)){
            $short_name = $match[1];
            $ret["short_name"] = $short_name;
            $ret["task_name"] = get_cms_taskname($short_name);
            if($ret["task_name"] !== "")$ret["same"] = !diff_cms($md, $id, $short_name);
        }
        return $ret;
    }
    if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['problem_id'])){
        $id = $_GET['problem_id'];
        if(!is_numeric($id) || intval($id) < 1 || intval($id) > PROBLEM_CNT)qt();
        compile_markdown($id);
        return_pdf($id.".pdf");
    }
    else if($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if(!isset($_POST['type']) || !isset($_POST['id']))qt();
        $tp = $_POST['type'];
        $id = $_POST['id'];
        if(!is_numeric($id) || intval($id) < 1 || intval($id) > PROBLEM_CNT)qt();
        if($tp == 'query'){
            echo fix_chinese(json_encode(query_task($id)));
            die();
        }
        else if($tp == 'preview'){
            compile_markdown($id);
            echo json_encode(Array("id" => $id));
            die();
        }
        else if($tp == 'replace'){
            if(!isset($_POST['short_name']))qt();
            compile_markdown($id);
            replace_statement($id,$_POST['short_name']);
            echo fix_chinese(json_encode(query_task($id)));
            die();
        }
        else qt();
    }
?>
<!doctype html>

<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Task Statement Manager</title>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
     <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script> var task_cnt = <?php echo PROBLEM_CNT ?>; </script>
</head>
<body>
    <main class="ui center aligned centered grid container">
        <table class="ui single line definition celled center aligned structured unstackable table" style="margin-top: 50px;margin-bottom: 50px;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Short Name</th>
                    <th>Preview</th>
                    <th>CMS Task Name</th>
                    <th>Same as Hackmd</th>
                    <th>Replace</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    for($i = 1;$i <= PROBLEM_CNT;$i++)
                    {
                        echo '<tr id="'.strval($i).'" class="task-row"><td>'.strval($i).'</td><td colspan="5"><div class="ui inline active tiny loader"></div></td></tr>';
                    }
                ?>
            </tbody>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Short Name</th>
                    <th>Preview</th>
                    <th>CMS Task Name</th>
                    <th>Same as Hackmd</th>
                    <th>Replace</th>
                </tr>
            </thead>
        </table>
        <script src="./index.js"></script>
    </main>
</body>
</html>

<?php
/*
    in : {
        "type": (query,preview,replace),
        "id": id,
        "short_name": short_name (for replace only)
    }

    query & replace out : {
        "id" : id,
        "short_name" : (Short Name or ""),
        "task_name" : (Task Name or ""),
        "same" : true or false,
        "url" : hackmd_url
    }
    preview out : {
        "id" : id
    }
*/
?>
