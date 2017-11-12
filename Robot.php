<?php
	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);
	error_reporting(E_ALL);
	
	@ini_set('zlib.output_compression',0);
    @ini_set('implicit_flush',1);
    @ob_end_clean();
    set_time_limit(0);
	ob_implicit_flush(1);
	
	$telegram_api = " T * O * K * E * N ";
	define('API',$telegram_api);
	// Robot Administrator
	$ADMINS = ['********','********'];
	// Attach Channel
  $attachChannel = "@*****";
	// Your Channel
	$MyChannel = "@*******";
	// Advertising - for example: @User | @Channel
	$ADV = "
	-------------------
	👨🏻‍💻 @******* | @********
	";
	
	function curl($url,$timeout=7){		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_SSLVERSION,3);
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
		//curl_setopt($ch, CURLOPT_USERAGENT, $_REQUEST['HTTP_USER_AGENT']);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt ($ch, CURLOPT_TIMEOUT, $timeout);
		$data = curl_exec ($ch);
		$error = curl_error($ch); 
		curl_close ($ch);
		return $data;
	}
	
	function curl_dl($url,$LocalFile,$timeout=120){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
		//curl_setopt($ch, CURLOPT_POST, count($parms));
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $parms);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt ($ch, CURLOPT_TIMEOUT, $timeout);
		$data = curl_exec($ch);
		curl_close($ch);
		file_put_contents($LocalFile,$data);
		//$file = fopen($LocalFile, "w+");
		//fputs($file, $data);
		//fclose($file);
	}
	
	function Bot($method,$fields){
		
		$url = "https://api.telegram.org/bot".API."/".$method;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	
	function SaveFile($file_id,$LocalFile){
		$fields=array(
		'file_id'=>$file_id
		);
		$res = Bot('getfile',$fields);
		$res = json_decode($res,true);
		if($res['ok']){
			$patch = $res['result']['file_path'];
			$url = 'https://api.telegram.org/file/bot'.API.'/'.$patch;
			curl_dl($url,$LocalFile,15);
			return true;
		}else{
			return false;
		}
	}
	
	function File2Link($file_id){
		$fields=array(
		'file_id'=>$file_id
		);
		$res = Bot('getfile',$fields);
		$res = json_decode($res,true);
		if($res['ok']){
			$patch = $res['result']['file_path'];
			$url = 'https://api.telegram.org/file/bot'.API.'/'.$patch;
			return $url;
		}else{
			return "";
		}
	}
	
	
	function FindPath($file_id){
		$fields=array(
		'file_id'=>$file_id
		);
		$res = Bot('getfile',$fields);
		$res = json_decode($res,true);
		if($res['ok']){
			$patch = $res['result']['file_path'];
			return $patch;
		}else{
			return "";
		}
	}
	
	function get_file_extension($filename)
	{
		$filename = explode(".",$filename);
		return strtolower($filename[sizeof($filename)-1]);
	}
	
	function SendFile($chat_id,$file_id,$caption="",$MainKeyboard=null,$file_name=null,$forceDocument=false){
		$disable_web_page_preview = null;
		$reply_to_message_id = null;
		$reply_markup = null;
		$ex = get_file_extension($file_name);
		$file_type='document';
		if(!$forceDocument){
			switch(strtolower($ex)){
				case "jpg":
				case "png":
					$file_type="photo";
				break;
				
				case "mov":
				case "mp4":
				case "3gp":
					$file_type="video";
				break;
				
				case "mp3":
				case "wav":
					$file_type="sound";
				break;
				
				case "ogg":
					$file_type="voice";
				break;
				
				default:
					$file_type='document';
				break;
			}
		}
		if(sizeof($MainKeyboard) > 0){
			$reply_markup=json_encode($MainKeyboard); 
		}
		
		$fields = array(
			'chat_id' => ($chat_id),
			strtolower($file_type) => ($file_id),
			'caption' => $caption,
			'file_name' => $file_name,
			'disable_web_page_preview' => ($disable_web_page_preview),
			'reply_to_message_id' => ($reply_to_message_id),
			'reply_markup' => ($reply_markup)
		);
		$res = Bot('send'.ucfirst($file_type),$fields);
		return $res;
	}	
	
	function SendPost($post){
		
		$text = " <a href='".$post['attach']."'> ‌ </a>
".$post['matn']."
";
		
		if(sizeof($post['keyboard']["inline_keyboard"]) >0 ){
			$reply_markup = json_encode($post['keyboard']);
		}else{
			$reply_markup = null;
		}
		
		$fields = array(
			'chat_id' => $post['channel'],
			'text' => ($text),
			'disable_web_page_preview' => false,
			'reply_to_message_id' => null,
			'reply_markup' => ($reply_markup),
			'parse_mode' => 'html'
		);
		$res = Bot('sendMessage',$fields);
		return $res;
	}
	
	function IsChannelAdmin($from_id,$channel){
		$fields = array(
			'chat_id' => $channel
		);
		$res = Bot('getChatAdministrators',$fields);
		$res = json_decode($res,true);
		if($res['ok']){
			foreach($res['result'] as $user){
				if($user['user']['id'] == $from_id && ($user['status']=='administrator' || $user['status']=='creator') ){
					return true;
				}
			}
		}
		return false;
	}
	
	
	
	// Proccess Message
	$message= file_get_contents("php://input");
	//file_put_contents("message.txt", $message);
	//$message= file_get_contents("message.txt");
	$up = json_decode($message);
	$update_id = $up->update_id;
	
	$message_id = $up->message->message_id;
	$message_date = $up->message->date;
	$message_date = date('Y-m-d H:i:s', $message_date);
	if(isset($up->message->text)){
		$message_text = $up->message->text;
	}
	if(isset($up->message->reply_to_message->from)){
		$my_usename = $up->message->reply_to_message->from->username;
	}
	$isBotCommond = false;
	if(isset($up->message->entities)){
		$message_entities = $up->message->entities;
		if(isset($up->message->entities[0]->type)){
			$bot_command_type = $up->message->entities[0]->type;
			if( $bot_command_type== 'bot_command'){
				$isBotCommond=true;
			}
		}
	} 
	
	
	
	$from_id = $up->message->from->id;
	$from_first_name = $up->message->from->first_name;
	$from_last_name = $up->message->from->last_name;
	$from_username = $up->message->from->username;
	$from_language_code = $up->message->from->language_code;
	
	$chat_id = $up->message->chat->id;
	$chat_first_name = $up->message->chat->first_name;
	$chat_last_name = $up->message->chat->last_name;
	$chat_username = $up->message->chat->username;
	$chat_type = $up->message->chat->type;
	
	if(isset($up->message->chat->title)){
		$chat_title = $up->message->chat->title;
	}
	
	if(isset($up->message->caption)){
		$caption = $up->message->caption;
	}
	
	if(isset($up->message->document)){
		$file_name = $up->message->document->file_name;
		$mime_type = $up->message->document->mime_type;
		$file_id = $up->message->document->file_id;
		$file_size = $up->message->document->file_size;
	}
	
	if(isset($up->message->video)){
		$file_name = time().".mp4";
		$mime_type = $up->message->video->mime_type;
		$file_id = $up->message->video->file_id;
		$file_size = $up->message->video->file_size;
	}
	
	if(isset($up->message->audio)){
		$file_title = $up->message->audio->title;
		$file_name = $up->message->audio->performer.".mp3";
		$mime_type = $up->message->audio->mime_type;
		$file_id = $up->message->audio->file_id;
		$file_size = $up->message->audio->file_size;
	}
	
	if(isset($up->message->voice)){
		$file_id = $up->message->voice->file_id;
		$file_name = "VOICE_".$file_id.".Ogg"; 
		$mime_type = $up->message->voice->mime_type;
		
		$file_size = $up->message->voice->file_size;
	}
	
	if(isset($up->message->photo)){
		$file_name = time().".jpg";
		$lphoto = $up->message->photo;
		$lphoto = $lphoto[sizeof($lphoto)-1];
		//$mime_type = $lphoto->mime_type;
		$mime_type = 'photo/jpg';
		$file_id = $lphoto->file_id;
		$file_size = $lphoto->file_size;
	}
	
	if(isset($up->message->forward_from)){
		$forward_from_id = $up->message->forward_from->id;
		$forward_from_first_name = $up->message->forward_from->first_name;
		if(isset($up->message->forward_from->last_name)){
			$forward_from_last_name = $up->message->forward_from->last_name;
		}
		if(isset($up->message->forward_from->username)){
			$forward_from_username = $up->message->forward_from->username;
		}
	}	
	
	if(isset($up->message->forward_from_chat)){
		$forward_from_chat_id = $up->message->forward_from_chat->id;
		$forward_from_chat_title = $up->message->forward_from_chat->title;
		if(isset($up->message->forward_from_chat->username)){
			$forward_from_chat_username = $up->message->forward_from_chat->username;
		}
		if(isset($up->message->forward_from_chat->type)){
			$forward_from_chat_type = $up->message->forward_from_chat->type;
		}
	}
	
	$IsCallBack=false;
	if(isset($up->callback_query)){
		$IsCallBack=true;
		$message_id = $up->callback_query->id;
		$from_id = $up->callback_query->from->id;
		$chat_id = $from_id;
		$is_bot = $up->callback_query->from->is_bot;
		$first_name = $up->callback_query->from->first_name;
		$last_name = $up->callback_query->from->last_name;
		$username = $up->callback_query->from->username;
		$language_code = $up->callback_query->from->language_code;
		$inline_message_id = $up->callback_query->inline_message_id;
		$chat_instance = $up->callback_query->chat_instance;
		$message_text = $up->callback_query->data;
	}
	
	$file = array('file_id'=>$file_id,'file_name'=>$file_name,'file_size'=>$file_size,'file_type'=>$mime_type, 'message_id' => $message_id);
	
	
	if(!file_exists('ups')){
		file_put_contents('ups',"");
	}
	mkdir('users');
	mkdir('temp');
	
	// Block Flood Request
	$uniq = $update_id."_".date('Y-m-d', $message_date);
	$ups = file_get_contents('ups');
	if(strpos($ups,$uniq) > 0 ){
	   exit(); 
	}else{
	   file_put_contents('ups',$ups."\n".$uniq); 
	}
	if(!in_array($from_id,$ADMINS) && 1==2){
		$text = "❌ شما دسترسی ندارید.
🇬🇧 [Not Access!]
";
		goto SendMSG;
	}
	
	if($chat_type == "group" || $chat_type == "supergroup"){
		exit();
	}
	
	$userFile = 'users/'.$chat_id;
	
	// Read User Information
	$userInfo = file_get_contents($userFile);
	$userInfo = json_decode($userInfo,true);
	$userInfo['chat_id'] = $chat_id;
	$userInfo['username'] = $from_username;
	$userInfo['fullname'] = $from_first_name." ".$from_last_name;
	if($file_id !=""){
		$userInfo['files'][] = $file;
	}
	if($message_text !=""){
		$userInfo['messages'][] = $message_text;
	}
	
	if(isset($userInfo['lastPostID'])){
		$lastPostID = intval($userInfo['lastPostID'])+1;
	}else{
		$lastPostID = 0;
	}
	
	if($IsCallBack){
		$postID = str_replace("/SendToChannel ","",$message_text);
		$post = $userInfo['posts'][$postID];
		if(IsChannelAdmin($from_id,$post['channel'])){
			$M2 = SendPost($post);
			$M2 = json_decode($M2,true);
			if($M2['ok']){
				$text = "✅ مطلب با کد ".$postID." به کانال ".$post['channel']." ارسال شد.
🇬🇧 [Content With ".$postID." Code, Sent to ".$post['channel']."]
";
			}else{
				$text = "❌ ".$M2['description'];
			}
		}else{
			$text = "❌ شما مدیر کانال ".$post['channel']." نیستید.
🇬🇧 [You Must Admin of Channel]
";
		}
		goto SendMSG;
	}
	
	// پردازش مرحله
	switch($message_text){
		case "/start":
		case "/cancel":
			$userInfo['step']="";
		break;
	}
	
	
	switch(trim($userInfo['step'])){
		case "":
			$text = "1️⃣ 
متنی که مخواهید به آن عکس/فایل پیوست کنید را ارسال فرمایید:
🇬🇧 [Enter Your Text]
";
			$userInfo['files']=array();
			$userInfo['messages']=array();
			$userInfo['posts'][$lastPostID]['status']='step1';
			$nextStep = "2";
		break;
		
		case "2":	
			if($message_text !=""){
				$userInfo['posts'][$lastPostID]['matn'] = $message_text;
				$userInfo['posts'][$lastPostID]['status']='step2';
				$text="2️⃣
📀 فایلی که میخواهید به متن ارسالی پیوست کنید را وراد نمایید: (برای رد کردن این مرحله عدد 0 را ارسال نمایید)
🇬🇧 [Sent Your File. <code>For Skip: Send 0 number</code>]
	";
				$nextStep = "3";
			}else{
				$text ="❌ لطفا متن وارد نمایید.
🇬🇧 [Please Enter Text]
";
				$nextStep = "2";
			}
		break;
		
		case "3":
			if($file_id !="" || $message_text =="0"){
				$userInfo['posts'][$lastPostID]['file'] = $file;
				$userInfo['posts'][$lastPostID]['status']='step3';
				$text="3️⃣ برای ایجاد دکمه ی شیشه ای به فرمت زیر عمل کنید یا برای رد کردن عدد 0 را وارد نمایید:
🇬🇧 [Sent Your Inline Buttons Same Below <code>For Skip: Send 0 number</code>]
<code>Text,Link</code>
<code>Text,Link^Text,Link</code>
<code>Text,Link^Text,Link^Text,Link</code>
";
				$nextStep="4";
			}else{
				$text ="❌ لطفا فایل ارسال کنید.
🇬🇧 [Please Send File.]
";
				$nextStep = "3";
			}
		break;
		
		case "4":
			$i=0;
			$keyboard = [];
			$keyboard["inline_keyboard"] = [];
			$rows = explode("\n",$message_text);
			foreach($rows as $row){
				$j=0;
				$keyboard["inline_keyboard"][$i]=[];
				$bottons = explode("^",$row);
				foreach($bottons as $botton){
					$data = explode(",",$botton.",");
					$Ibotton = ["text" => $data[0], "url" => $data[1]];
					$keyboard["inline_keyboard"][$i][$j] = $Ibotton;
					$j++;
				}
				$i++;
			}
			
			$haveKeyboard = false;
			if($message_text =="0" ){
				$keyboard = [];
			}else{
				$haveKeyboard = true;
			}
			
			if(!$haveKeyboard || ($haveKeyboard && sizeof($keyboard["inline_keyboard"]) > 0) ){
				$userInfo['posts'][$lastPostID]['keyboard'] = $keyboard;
				$userInfo['posts'][$lastPostID]['status']='step4';
				$text="4️⃣  آی دی کانال یا کد عددی گروه را وارد نمایید: ( برای رد کردن این مرحله عدد 0 را وراد نمایید )
مانند: 
🇬🇧 [Sent Your Channel ID or Group Code Same Below. <code>For Skip: Send 0 number</code>]
$MyChannel
-100123456789
";
				$nextStep="5";
			}else{
				$text ="❌ کیبورد شیشه ای صحیح وارد نشده است
🇬🇧 [Enter True Inline Buttons Format]
";
				$nextStep = "4";
			}
		break;
		
		case "5":
			
			if( $message_text == "0" || ($message_text != "" && IsChannelAdmin($from_id,$message_text))){
				$userInfo['posts'][$lastPostID]['channel'] = $message_text;
				$userInfo['posts'][$lastPostID]['status']='step5';
				
				$nextStep="";
				
				$fields=array(
				'chat_id'=>$chat_id,
				'text'=>"⏳ کمی شکیبا باشید...
🇬🇧 [Please Wait...]
"
				);
				$msg = Bot('sendMessage',$fields);
				$msg = json_decode($msg);
				
				$PostFile = $userInfo['posts'][$lastPostID]['file'];
				if($PostFile['file_id'] != ""){
					$fields = array(
						'chat_id' => $attachChannel,
						'from_chat_id' => $chat_id,
						'disable_notification' => true,
						'message_id' => $PostFile['message_id']
					);
					$M = Bot('forwardMessage',$fields);
					//$M = SendFile($attachChannel,$PostFile['file_id'],"",$keyboard,$PostFile['file_name'],true);
					$M = json_decode($M,true);
				}else{ 
					$M['ok'] = true;
				}
				
				if($M['ok']){
					if(is_numeric($attachChannel)){
						$fields = array(
							'chat_id' => $attachChannel
						);
						$MM = Bot('getChat',$fields);
						$MM = json_decode($MM,true);
						$attachChannel = $MM['result']['username'];
					}
					
					
					$userInfo['posts'][$lastPostID]['attach'] = "https://t.me/".str_replace("@","",$attachChannel)."/".$M['result']['message_id'];
					$userInfo['posts'][$lastPostID]['status'] = 'finished';
					$userInfo['lastPostID'] = $lastPostID;
					$txt = "✅ انجام شد.
🇬🇧 [Done.]
";
					$post=$userInfo['posts'][$lastPostID];
					$post['channel']=$chat_id;
					
					if($userInfo['posts'][$lastPostID]['channel'] != "" && $userInfo['posts'][$lastPostID]['channel'] != "0"){
						$post['keyboard']["inline_keyboard"][][]=["text" => "ارسال به کانال ".$userInfo['posts'][$lastPostID]['channel'], "callback_data" => "/SendToChannel ".$lastPostID];
						$post['keyboard']["inline_keyboard"][][]=["text" => "Send To ".$userInfo['posts'][$lastPostID]['channel']." Channel", "callback_data" => "/SendToChannel ".$lastPostID];
					}
					
					$M2 = SendPost($post);
					
				}else{
					$txt = "❌ ".$M['description'];
				}
				$fields=array(
					'chat_id'=>$chat_id,
					'message_id' => $msg->result->message_id,
					'text'=>$txt,
					'parse_mode'=>'html'
				);
				$res = Bot('editMessageText',$fields);
				$res = json_decode($res,true);
				if(!$res['ok']){
					$text = "❌ ".$res['description'];
				}
				$userInfo['files']=[];
				$userInfo['messages']=[];
			}else{
				$text ="❌ شما مدیرکانال نیستید!
🇬🇧 [You Must Admin of Channel]
";
				$nextStep = "4";
			}
		
		break;
		
		
		default:
		break;
	}
	
	
	// Save User Information
	$userInfo['step'] = $nextStep;
	
	file_put_contents($userFile,json_encode($userInfo));
	
	SendMSG:
	// Send Message
	if($text !=""){
		$fields=array(
		'chat_id'=>$chat_id,
		'text'=>$text.$ADV,
		'parse_mode'=>'html'
		);
		$msg = Bot('sendMessage',$fields);
	}
