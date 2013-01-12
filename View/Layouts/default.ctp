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
    <script type="text/javascript" src="/js/date-utils.min.js"></script>
	<script type="text/javascript" src="/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="/js/common.js"></script>
	<script type="text/javascript" src="/js/site.js"></script>        
	<?php
		echo $this->fetch('script');
	?>
    <style>
    .ui-header {
        min-height: 55px;
    }
    .ui-header .icon.logo {
        font-size: 50px;
        top: 18px;
        left: 10px;
        position: relative;
    }
    nav #actions li {
        display: inline-block;
        padding: 0 5px;
        border: 1px dotted grey;
    }
    nav #actions li.toggled {
        background-color: lightgrey;
    }
    .ui-header nav {
        text-align: center;
        margin-top: -10px;        
    }
    .ui-header .icon {
        font-size: 40px;
    }
    #search {
        height: 2em;
    }
    </style>
</head>
<body>
<div data-role="page">

    <div data-role="header" data-theme="a" data-position="fixed">
        <span class="icon logo">
            &#9884;
        </span>
        <nav>
            <ul id="actions">
                <li><span class="icon map">M</span></li>
                <li><span class="icon star">&#8902;</span></li>
                <li data-toggletarget="#search-area"><span class="icon search">?</span></li>
            </ul>
        </nav>
        <div id="search-area" style="display: none">
            <input type="search" id="search" data-theme="c" placeholder="Search Act, Venue, Category" value="" />            
        </div>
    </div>

    <div data-role="content"  data-theme="c">   
        <?php echo $this->fetch('content'); ?>          
        <?php echo $this->Flash->show(); ?>
        <?php echo $this->Session->flash(); ?>
        <?php echo $this->element('sql_dump'); ?>   
    </div>
    <div data-role="footer" data-theme="a" class="ui-bar">
    </div>
</div>
<?= $this->Facebook->init() ?>

<script>
$.widget('codemkrs.toggleAreaTab', {
    options: {
        target: null
    }
    ,_create: function() {
        this.element.toggle(this.onOff(true), this.onOff(false));
    }
    ,onOff: function(swtch) {return _.bind(function(){
        this.element.toggleClass('toggled', swtch);
        $(this.options.target)[swtch?'slideDown':'slideUp']();
    }, this) }
});
$(document).on('pageinit', function(){
    $('[data-toggletarget]').each(function() {
        $(this).toggleAreaTab({
            target: $(this).data('toggletarget')
        })
    });
});
</script>
</body>
</html>
