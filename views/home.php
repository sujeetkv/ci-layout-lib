<?php $this->layout->block('dynamic_head_block'); ?>
<style type="text/css">
.css-image{
	background-image: url("app-media/app-images/100x100/media/images/forcite_helmets.jpg");
	width: 100px;
	height: 100px;
}
</style>
<script type="text/javascript">
var demo = function(){
	// democode js
}
</script>
<?php $this->layout->block(); ?>

<?php $this->layout->block('dynamic_js_block', true); ?>
var demo2 = function(){
	// democode
}
<?php $this->layout->block(); ?>

<h1>Hello <?php echo $name; ?></h1>
<p>This is page content.</p>