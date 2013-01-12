<style>
#help-notice {display:none;}
#comments {margin-top: 20px; padding-top: 40px;  clear:both; }
#dsq-subscribe {display:none;}
</style>
 
<div id='comments' class='vine'></div><!-- end comments --> 
        <div id="disqus_thread"></div>
        <script type="text/javascript">

            var disqus_shortname = 'specialgoodstuff'; // required: replace example with your forum shortname
<?php
if ($_SERVER['SERVER_ADDR'] == '127.0.0.1'){
?>
			var disqus_developer = 1;
<?php
}
?>

            (function() {
                var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
                dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
                (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
            })();
        </script>
        <noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
     
 
