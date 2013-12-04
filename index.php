<?php
session_start();
error_reporting(0);
require_once('twitteroauth/twitteroauth.php');
require_once('config.php');

$CONSUMER_KEY = CONSUMER_KEY;
$CONSUMER_SECRET = CONSUMER_SECRET;
$OAUTH_CALLBACK = OAUTH_CALLBACK;
$HASHTAG = HASHTAG;
$RPP = 10;
//
//if(!isset($_SESSION['oauth_token']) && !isset($_SESSION['oauth_token_secret'])){
//	$connection = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET);
//	$request_token = $connection->getRequestToken($OAUTH_CALLBACK);
//	$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
//	$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
//	
//	//$token = $request_token['oauth_token'];
//	//setcookie("oauth_token",$token,time()+60*60*24);
//	//setcookie("oauth_token_secret",$request_token['oauth_token_secret'],time()+60*60*24);
//	switch ($connection->http_code) {
//		case 200:
//			/* Build authorize URL and redirect user to Twitter. */
//			$url = $connection->getAuthorizeURL($token);
//			header('Location: ' . $url);
//			exit;
//		  break;
//		default:
//			/* Show notification if something went wrong. */
//			clearSession();
//			$error=true;
//			wp_redirect(add_query_arg( 'n', wp_create_nonce ('fail_twitter'), get_permalink($_POST['asop_postid']) ));
//			exit;
//	}
//	
//}



/* Get user access tokens out of the session. */
$access_token = $_SESSION['access_token'];

/* Create a TwitterOauth object with consumer/user tokens. */
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);

/* If method is set change API call made. Test is called by default. */
//$content = $connection->get('account/verify_credentials');

/* Some example calls */
//$connection->get('users/show', array('screen_name' => 'abraham'));
//$connection->post('statuses/update', array('status' => date(DATE_RFC822)));
//$connection->post('statuses/destroy', array('id' => 5437877770));
//$connection->post('friendships/create', array('id' => 9436992));
//$connection->post('friendships/destroy', array('id' => 9436992));
//var_dump($connection->get('users/show', array('screen_name' => 'theoalsie')));
//$content = ($connection->get('https://api.twitter.com/1.1/search/tweets.json?q=%23jsconfasia'));
//print_r($content);
if(!isset($_SESSION['oauth_token']) && !isset($_SESSION['oauth_token_secret'])){
	$CONSUMER_KEY = CONSUMER_KEY;
	$CONSUMER_SECRET = CONSUMER_SECRET;
	$OAUTH_CALLBACK = OAUTH_CALLBACK;
	
	$connection = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET);
	$request_token = $connection->getRequestToken($OAUTH_CALLBACK);
	$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
	$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
	
	$_SESSION['access_token'] = $access_token;
	//$token = $request_token['oauth_token'];
	//setcookie("oauth_token",$token,time()+60*60*24);
	//setcookie("oauth_token_secret",$request_token['oauth_token_secret'],time()+60*60*24);
	switch ($connection->http_code) {
		case 200:
			/* Build authorize URL and redirect user to Twitter. */
			$url = $connection->getAuthorizeURL($token);
			$_SESSION['status'] = 'verified';
			header('Location: ' . $url);
			exit;
		  break;
		default:
			/* Show notification if something went wrong. */
			die('error');
			exit;
	}
}elseif (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] === $_REQUEST['oauth_token']) {
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

	/* Request access tokens from twitter */
	$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
	
	/* Save the access tokens. Normally these would be saved in a database for future use. */
	$_SESSION['access_token'] = $access_token;
}
if (!empty($_SESSION['access_token']) && !empty($_GET['ajax'])) {
	$CONSUMER_KEY = CONSUMER_KEY;
	$CONSUMER_SECRET = CONSUMER_SECRET;
	$rpp = ($_GET['rpp'] * 1)?$_GET['rpp']:$RPP;
	$access_token = $_SESSION['access_token'];
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
	//$content =$connection->get('https://api.twitter.com/1.1/search/tweets.json?q=%23'.HASHTAG.'&include_entities=1&count='.$rpp);
	
	$content =$connection->get('https://api.twitter.com/1.1/search/tweets.json?q=%23'.HASHTAG.'&include_entities=1&count='.$rpp.'&max_id='.$_GET['max_id']);
	//var_dump($content);
	die(json_encode($content));
	//print_r($connection);
	//echo $content->name;
	if (200 == $connection->http_code) {
		//var_dump($connection->get('/search/tweets.json?q=%23susnavgala')  );
		$_SESSION['status'] = 'verified';
	}
}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="utf-8" http-equiv="encoding">
    <title>SUS/NAV GALA</title>
	<link rel="stylesheet" href="css/styles.css">
	<link rel="stylesheet" href="js/superslides/stylesheets/superslides.css">
</head>
<body>
	<!--<h1 id="loadertext">Fetching Tweets...</h1>-->
    <div id="slides">
    <ul class="slides-container">
		
	</ul>
	<nav class="slides-navigation">
      <a href="#" class="next">Next</a>
      <a href="#" class="prev">Previous</a>
    </nav>
	</div>
	<div id="tweetLoader">
	<a id="nextTweets" href="#" rel="">Load Next Tweets</a>
	<img id="loader" src="images/loading.gif" width="16" height="16" />
	</div>
	<script src="js/jquery.1.9.1.js"></script>
	<!--<script src="js/jquery-ui.min.js"></script>-->
	<script src="js/jquery.easing.1.3.js"></script>
	<script src="js/jquery.animate-enhanced.min.js"></script>
	<script src="js/superslides/jquery.superslides.min.js"></script>
    <script type="text/javascript">
        $().ready(function () {
             var rpp = <?php echo $RPP;?>;
            //retreiveTweets(rpp);
			$('#nextTweets').attr('rel','?max_id=&count='+rpp);
			$('#nextTweets').click(function(){
				retreiveTweets(rpp);
				return false;
			});
			//$('#nextTweets').trigger('click');
			setInterval(function(){
				$('#nextTweets').trigger('click');
			},5000);
			var currentndex = 0;
            function retreiveTweets(rpp) {
				$('#loader').show();
                var nextPage = $('#nextTweets').attr('rel');
               if ($.trim(nextPage) == '') {
					$('#nextTweets').attr('rel',"");
					$('#loader').hide();
                    return false
                }else{
					$.ajax({
						url: 'index.php' + nextPage + '&ajax=1',
						//url:'test.json',
						dataType: 'json',
						crossDomain: true,
						success: function (data) {
							//console.log(data);return false;
							//$('#loadertext').remove();
							if(data.search_metadata.next_results==undefined){ //prevents bubbling up when query reached end
								$('#nextTweets').attr('rel', "");
								$('#loader').hide();
							}else{
								$('#nextTweets').attr('rel', data.search_metadata.next_results);
							}
							var r = data.statuses;
							var page = parseInt(data.search_metadata.max_id);
							var multiplier = currentndex * rpp;
							$.each(r, function (index, rObj) {
								//$('ul').append("<li><div class='container'>@" + rObj.from_user + " - " + rObj.text + "</div></li>");
								$li = $("<li><div class='container'>@" + rObj.user.screen_name + " - " + rObj.text + "</div><a href='#' class='btnremove'>remove</a> | <a href='#' class='togglecontent'>toggle</a></li>");
								var ent = rObj.entities;
								var urls = ent.urls[0];
								var img = "";
								if (typeof urls != 'undefined') {
									//fetch instagram image
									var eUrl = urls.expanded_url;
									index = parseInt(index) + parseInt(multiplier);
									if (eUrl.indexOf("instagram") != -1) {
										
										$.ajax({
											url:"http://api.instagram.com/oembed?url=" + eUrl+"&callback=?",
											dataType: 'jsonp',
											crossDomain: true,
											success: function (i) {
												var slideDiv = document.getElementById('slider');
												$('ul').find('li').eq(index).prepend("<img src='" + i.url + "' width='" + (i.width ) + "' height='" + (i.height) + "' class='preserve'  />");
												//$($('ul li')[index]).prepend("<img src='" + i.url + "' width='" + (i.width ) + "' height='" + (i.height) + "' class='preserve'  />");
											}
										});
										
										//var urlArray = eUrl.split("/");
										//$.ajax({
										//	url:"http://instagr.am/p/"+urlArray[4]+"/media/?size=m",
										//	crossDomain: true,
										//	success: function (data) {
										//		var slideDiv = document.getElementById('slider');
										//		$($('ul li')[index]).prepend("<img src='" + data + "'  />");
										//	}
										//});
										
										//$.getJSON("http://api.instagram.com/oembed?url=" + eUrl , function (i) {
										//	var slideDiv = document.getElementById('slider');
										//	//$($('ul li')[index]).prepend("<img src='" + i.url + "' width='" + (i.width * 0.5) + "' height='" + (i.height * 0.5) + "' />");
										//	$($('ul li')[index]).prepend("<img src='" + i.url + "' width='" + (i.width ) + "' height='" + (i.height) + "'  />");
										//	//$li.prepend("<img src='" + i.url + "' width='" + (i.width ) + "' height='" + (i.height) + "'  />");
										//})
										//.done(function(){ console.log('success', arguments); })
										//.fail(function(){ console.log('failure', arguments); });
									}
								}else{ //twitter photo
									if(typeof ent.media !='undefined'){
										var media = ent.media[0];
										if(typeof media['media_url']!='undefined'){
											index = parseInt(index) + parseInt(multiplier);
											var slideDiv = document.getElementById('slider');
											var sizes = media.sizes;
											var chosen_size = sizes.medium;
											$li.prepend("<img src='" +media['media_url'] + "' width='" + (chosen_size.w ) + "' height='" + (chosen_size.h) + "' class='preserve'  />");
										}
									}
									
								}
								if(page <= 0){
									$('ul').append($li);
								}else{
									//$('#slides').superslides('append', $li);
									$('ul').append($li);
									$('#slides').superslides('update');
								}
							});
							currentndex++;
							$('#nextTweets').unbind('click').bind('click',function () {
								retreiveTweets(rpp);
								return false;
							});
							$('.btnremove').unbind('click').bind('click',function () {
								$('#slides').superslides('next');
								$(this).parent().remove();
								return false;
							});
							$('.togglecontent').unbind('click').bind('click',function () {
								$(this).siblings('.container').toggle('slow');
							});
							if(page <= 0){
								$('#slides').superslides({
									play: true,
									/*slide_easing: 'easeInOutCubic', */
									animation_speed: 800,
									play: 8000,
									pagination: false,
									animation:'fade'
								  });
							}
							$('#loader').hide();
						}
					});
				}
			}
        });
		
    </script>
	<script type="text/javascript">
    window.onload = setupRefresh;

    function setupRefresh() {
      setTimeout("refreshPage();", 60000); // milliseconds
    }
    function refreshPage() {
       window.location = location.href;
    }
  </script>
</body>
</html>
