
<div class="users form">
<?php echo $this->Form->create('User'); ?>
    <fieldset>
        <legend><?php echo __('Login'); ?></legend>
        <?php echo $this->Form->input('username');
        echo $this->Form->input('password');
    ?>
    </fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>

<?php	
echo $this->Facebook->login(array(
	'redirect' => '/',
	'label' => 'Login with Facebook',
	'id' => 'loginWithFacebook',
	'size' => 'large'									
));
?>