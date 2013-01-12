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
    <link href="http://code.jquery.com/mobile/1.2.0/jquery.mobile-1.2.0.min.css" rel="stylesheet">
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
    <script src="http://code.jquery.com/jquery-1.8.2.min.js"></script>
    <script type="text/javascript" src="http://code.jquery.com/mobile/1.2.0/jquery.mobile-1.2.0.js"></script>
    <script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.3/underscore-min.js"></script>
	<script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/handlebars.js/1.0.rc.1/handlebars.min.js"></script>
	<script type="text/javascript" src="/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="/js/common.js"></script>
	<script type="text/javascript" src="/js/site.js"></script>        
	<?php
		echo $this->fetch('script');
	?>
</head>
<body>
<div data-role="page">

    <div data-role="header" data-theme="c">
        <h1>My Title</h1>
        <?php echo $this->Flash->show(); ?>
        <?php echo $this->Session->flash(); ?>
        <?php echo $this->fetch('content'); ?>          
        <?php echo $this->element('sql_dump'); ?>   
    </div>

    <div data-role="content"  data-theme="c">   
        <p>Hello world</p>      
    </div>

</div>
<?= $this->Facebook->init() ?>
</body>
</html>
