<?php
//データベースへの接続
$dbfile = dirname(__FILE__)."/chat.db"; // データベースの保存先

try{
	$db = new PDO("sqlite:$dbfile");
}catch(PDOException $e){
	echo "データベースに接続できません".$e->getMessage();
	exit;
}

//データベースがなければつくる
$create_query = <<< SQL
	CREATE TABLE IF NOT EXISTS chat(
		no		INTEGER PRIMARY KEY,
		name 	TEXT,
		message	TEXT,
		time 	TEXT
	);
SQL;
$db->beginTransaction();
$r = $db->exec($create_query);
if ($r === false) {
            echo "[データベースエラー]";
            print_r($db->errorInfo());
            $db->rollback();
            exit;
}
$db->commit();


	//メッセージの読み込み
	function loadMessage($db){

		$stmt = $db->query("SELECT * FROM chat ORDER BY no DESC");
		$chat_data = $stmt->fetchAll();
		$list_h = "";
		$g = "";
		// チャット一覧を読み込む
        foreach ($chat_data as $g) {
            $no = intval($g["no"]);
            $name_h = htmlspecialchars($g["name"]);
            $message_h = htmlspecialchars($g["message"]);
			$time = htmlspecialchars($g["time"]);
		//チャットの表示
$list_h .= <<< __CHAT__
			<tr>
			<td>{$name_h}</td><td>{$message_h}</td><td>{$time}</td>
			</tr>

__CHAT__;
		}
		
echo <<< __DISPLAY__
		
		<hr color=blue>
		<table>
		<tr><td>名前  </td><td>メッセージ  </td><td>投稿時間  </td></tr>
		<tr><td><hr color=blue></td><td><hr color=blue></td><td><hr color=blue></td></tr>
		{$list_h}
		</center>
		
__DISPLAY__;
	
	}

	//メッセージの保存
	function saveMessage($db,$name,$message){
		$date = date("Y/m/d H:i:s",time());
		if($name==""){
			$name = "名無しさん";
		}
		if($message==""){
			//メッセージが空であれば受け付けない
			exit;
		}
	 
		// チャットを保存する
    $db->beginTransaction();
$insert_query = <<< __SQL__
	INSERT INTO chat (name, message, time)
    	        VALUES (?, ?, ?);
__SQL__;

	$stmt = $db->prepare($insert_query);
    if(!$stmt) {
    	 echo "データベースエラー:"; 
    	 print_r($db->errorInfo()); 
    	 exit; 
    }
    $r = $stmt->execute(array($name, $message, $date));
	$no = $db->lastInsertId();
    if ($r === false){
    	 s_error("データベースへの挿入エラー"); 
    	 exit;
    }
	$db->commit();
	

		//////////////////////////////////////
		$res = loadMessage($db);
		//////////////////////////////////////
		return $res;
		
	}

	//メッセージの消去
	function resetMessage($db){
		$db->beginTransaction();
		$stmt = "DELETE FROM chat";
		$r = $db->exec($stmt);
		if ($r === false) {
            echo "[データベースエラー]";
            print_r($db->errorInfo());
            $db->rollback();
            exit;
		}
		$db->commit();
		$res = loadMessage($db);
		return $res;
	}


	//メインの処理
	if(isset($_POST['mode'])){
		$req_mode = htmlspecialchars($_POST['mode']);
		if(isset($_POST['name'])){
			$req_name = htmlspecialchars($_POST['name']);
		}
		if(isset($_POST['message'])){
			$req_message = htmlspecialchars($_POST['message']);
		}
		if(strlen($req_mode) == 0){
			exit;
		}
		
		/////////////////////////////////////////////////////
		$hostname = "localhost";
		$username = "username";
		$password = "password";
		$dbname = "dbname";
		

		$con = $db;

		switch($req_mode){
			//modeがload(読み込み)だったら
			case "load":
				//////////////////////////////////////////////
				$str = loadMessage($con);
				///////////////////////////////////////////////
				break;
			//modeがsave(保存)だったら
			case "save":

				///////////////////////////////////////////////////////
				$str = saveMessage($con,$req_name,$req_message);
				/////////////////////////////////////////////////////
				break;	
			case "reset":
				$str = resetMessage($con);
				break;
		}

		print $str;
		exit;
	}
?>