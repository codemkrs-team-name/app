<?
echo $this->element('breadcrumbs');
?>
<h1>Contact Josh</h1>	

<style>
input, textarea {width:380px;}
#send {margin-left:100px; border-radius:0px;}

label {width: 80px;}
#contact, #info {text-align:center;}
#info ul{width:220px; margin:0px; padding:0px; list-style:none;}
#info button {width: 205px;}
#contact {}
#contact form {width: 530px; text-align:left;}

.error-message {margin-left: 100px;}
</style>
<script type='text/javascript'>
$(document).ready(function(){
	$(".error-message").prepend("<span class='ico-warning'>&nbsp;</span>");
});
</script>
<div class='row-fluid'>
<div id='contact' class='span8'>

<?
echo $this->Form->create();

echo $this->Form->input('name', array('label' => 'name'));
echo $this->Form->input('from', array('label' => 'email'));
echo $this->Form->input('subject', array('label' => 'subject'));
echo $this->Form->input('message', array('type' => 'textarea', 'label' => 'message', 'rows' => 10));

?>
<button id='send' class='large' onClick="$("#contact form").submit();" ><span class='ico-arrow-right'>&nbsp;</span> Send it</button>
<?
echo $this->Form->end();
?>
</div>

<div id="info" class='span4'>
<ul>
	<li><button href='http://www.linkedin.com/pub/josh-anderson/a/b26/a14'><span class='ico-linkedin'>&nbsp;</span> view my linkedIn profile</button></li>
	<li><button href='http://www.facebook.com/people/Josh-Anderson/769129120'><span class='ico-facebook'>&nbsp;</span> view my facebook profile</button></li>
	<li><button href='https://github.com/janders4'><span class='ico-github'>&nbsp;</span> view my github profile</button></li>
    <li><button><span class='ico-phone'>&nbsp;</span> phone:  (228) 424-0380</button></li>
</ul>
</div>

</div>