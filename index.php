<?
session_start();
set_time_limit(300);

$secret = "?";

$ConexaoSQL = mysql_connect("?", "root", "?") or $erro="Nao foi possivel Conectar!";
if (!mysql_select_db("instagram", $ConexaoSQL)) {
	die("naoi conectou");
}

$result = mysql_query("SELECT * FROM instagram_api where id = '".$_GET["instaid"]."'");
print $result;
if( mysql_num_rows($result) ){
	$dados = mysql_fetch_array($result);	
}

function getTag( $tag, $urlP="" ){
	if( !empty($urlP)){
		$url = $urlP;
	}else{
		if( $_SESSION['lastUrlLike'] != "" ){
			$url = $_SESSION['lastUrlLike'];
		}else{
			$url = "https://api.instagram.com/v1/tags/".$tag."/media/recent";
		}
	}
	
	$_SESSION['lastUrlLike'] = $url;

	$postfields = array('access_token'=>$_SESSION['token']);
	
	$sig = generate_sig("/tags/".$tag."/media/recent", $postfields, $secret);
	$postfields["sig"] =  $sig;
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$server_output = curl_exec ($ch);

	curl_close ($ch);

	return json_decode( $server_output );

}

function generate_sig($endpoint, $params, $secret) {
	$sig = $endpoint;
	ksort($params);
	foreach ($params as $key => $val) {
		$sig .= "|$key=$val";
	}
	return hash_hmac('sha256', $sig, $secret, false);
}

function like( $id ){
	$postfields = array('access_token'=>$_SESSION['token']);

	$url = "https://api.instagram.com/v1/media/".$id."/likes";
	
	$sig = generate_sig("/media/".$id."/likes", $postfields, $secret);
	$postfields["sig"] =  $sig;
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);

	$server_output = curl_exec ($ch);

	curl_close ($ch);
	
	return json_decode( $server_output );

}

function follow( $id ){
	$postfields = array('access_token'=>$_SESSION['token'], 
			    		'action'      =>'follow');

	$url = "https://api.instagram.com/v1/users/".$id."/relationship";
	
	$sig = generate_sig("/users/".$id."/relationship", $postfields, $secret);
	$postfields["sig"] =  $sig;
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);

	$server_output = curl_exec ($ch);

	curl_close ($ch);
	
	return json_decode( $server_output );

}

$c = 0;
$cT = $_GET["total"] > 0 ? $_GET["total"] : 1;
function likeTags( $tag, $url="", $pagination=false){
	global $c, $cT;
	$me = getTag($tag, $url);
	$next = $me->pagination->next_url;
	foreach( $me as $posts){
		if( is_array( $posts ) ){
			
			foreach( $posts as $ta ){
				$idUser = $ta->user->id;			
				if( $ta->user_has_liked != '1' ){

                                        print "Liked: 1, user_has_liked: ".$ta->user_has_liked." idUser:".$idUser." Idphoto: ".$ta->id."<br>";

                                        //follow
                                        //$followBack = follow($idUser);

                                        $lke = like($ta->id);
                                        break;
                                        if( $lke->meta->code > 0 ){
                                                print "         - Retorno - OK";
                                        }

                                        flush();
                                }else{
                                        //$followBack = follow($idUser);
                                        print "Liked: 0,  user_has_liked:: ".$ta->user_has_liked." idUser:".$idUser." IdPhoto: ".$ta->id."<br>";
                                        flush();
                                }
				flush();
			}
		}
	}

	if( $pagination && !empty($next) && $c < $cT ){
		$c++;	
		print "NEW - Getting new Wave: <br>";
		likeTags($tag, $next, $pagination);
	}
}

if( empty( $dados["token"] ) ){
	if( empty( $_GET["code"] ) ){
		$_SESSION['idInsta'] = "";
		$_SESSION['idInsta'] = $dados["id"];
		header("Location: https://instagram.com/oauth/authorize/?client_id=".$dados["client_id"]."&redirect_uri=".$dados["url"]."&response_type=code&scope=likes+comments+relationships");
	}else{
		$result = mysql_query("SELECT * FROM instagram_api where id = '".$_SESSION['idInsta']."'");
		if( mysql_num_rows($result) ){
			$dados = mysql_fetch_array($result);	
		}
		$ch = curl_init();
		$postfields = array('client_id'=>$dados["client_id"], 'redirect_uri'=>$dados["url"], 'code'=>$_GET["code"], 'grant_type'=>'authorization_code', 'client_secret'=>$dados["secret_id"]);

		curl_setopt($ch, CURLOPT_URL,"https://api.instagram.com/oauth/access_token");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);

		$server_output = curl_exec ($ch);

		curl_close ($ch);
		$j = json_decode( $server_output );
		$tken = $j->access_token;
		
		$_SESSION['token'] = $tken;
		mysql_query("update instagram_api SET token = '".$tken."' WHERE id = '".$dados["id"]."'");
	}
}else{	
	$_SESSION['token'] = $dados["token"];
	
	likeTags($_GET["tag"], "", true);
}
?>