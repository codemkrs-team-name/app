<!DOCTYPE html>
<?
/*
<html lang="en">
*/
echo $this->Facebook->html();
?>

<head>
    <meta charset="utf-8">
    <title>nola event map: <?php echo $title_for_layout; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">  
    <meta property="fb:app_id" content="<?= Configure::read('facebookAppId') ?>" /> 
    <?php
		echo $this->Html->meta('icon');
		echo $this->fetch('meta');    
    ?>
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/bootstrap-responsive.min.css" rel="stylesheet">
    <link href="/css/screen.css" rel="stylesheet"> 
    <?php
		echo $this->fetch('css');    
    ?>   
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
	<script type="text/javascript" src="/js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="/js/ui/1.8.22/jquery-ui-1.8.22.custom.min.js"></script>
	<script type="text/javascript" src="/js/ui/1.8/jquery.ui.dialog.custom.js"></script>   
    <script type="text/javascript" src="/js/jquery.fixedCenter.js"></script>  
    <script type="text/javascript" src="/js/jquery.form.js"></script>  
	<script type="text/javascript" src="/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="/js/common.js"></script>
	<script type="text/javascript" src="/js/site.js"></script>        
	<?php
		echo $this->fetch('script');
	?>
</head>
<body>
<div id='base'>
	<div id='header'>
    	<h1>Nola event map</h1>
    </div>    
 	<div id='content' class='container-fluid'>
        <?php echo $this->Flash->show(); ?>
		<?php echo $this->Session->flash(); ?>
		<?php echo $this->fetch('content'); ?>        	
		<?php echo $this->element('sql_dump'); ?>   
	</div><!-- end content -->  
</div><!--end base -->
<?= $this->Facebook->init() ?>
</body>
</html>
