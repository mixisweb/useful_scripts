<?php 
$obj_fb = json_decode( file_get_contents('http://api.ak.facebook.com/restserver.php?v=1.0&method=links.getStats&urls='.get_the_permalink().'&format=json'));
$likes_fb = (empty($obj_fb[0]->like_count))? 0 : $obj_fb[0]->like_count;
$shares_fb = (empty($obj_fb[0]->share_count))? 0 : $obj_fb[0]->share_count;
?>
<span id="facebook_like" style="display:none;"><?php echo $likes_fb; ?></span>
<div class="fb-like" data-href="your post/page url" data-send="false" data-layout="button" data-width="55" data-show-faces="false" data-font="lucida grande">
</div>
<span id="facebook_share" style="display:none;"><?php echo $shares_fb; ?></span>
<div class="fb-share-button" data-href="your post/page url"  data-type="button" data-num-posts="num_posts" data-width="55"    data-font="lucida grande" >
</div> 
<script>
 window.fbAsyncInit = function() {
  FB.init({
    appId      : 'ID',// please place your app id here
    status     : true,
    xfbml      : true
  });
  FB.Event.subscribe('xfbml.render', function(response) {
     if(document.getElementById('facebook_like')) {
    document.getElementById('facebook_like').style.display = 'block';
     }
     if(document.getElementById('facebook_share')) {
    document.getElementById('facebook_share').style.display = 'block';
     }
  });
   };
  (function(){
   if (document.getElementById('facebook-jssdk')) {return;}
   var firstScriptElement = document.getElementsByTagName('script')[0];
   var facebookJS = document.createElement('script'); 
   facebookJS.id = 'facebook-jssdk';
   facebookJS.src = '//connect.facebook.net/ru_RU/all.js';
   firstScriptElement.parentNode.insertBefore(facebookJS, firstScriptElement);
    }());
</script>
