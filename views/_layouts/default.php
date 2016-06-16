<!DOCTYPE html>
<html>
<head>

<title><?php echo $title_for_layout; ?></title>

<?php echo $meta_for_layout; ?>

<?php echo $css_for_layout; ?>

<?php echo $js_for_layout; ?>

<script type="text/javascript">
<?php $this->layout->block('dynamic_js_block'); ?>
var base_url = '<?php echo base_url(); ?>';

<?php $this->layout->block(); ?>
</script>

<?php echo $this->layout->get_block('dynamic_head_block'); ?>
</head>

<body>
<!-- header -->
<?php $this->layout->element('header'); ?>
<!-- header end -->

<!-- content -->
<?php echo $content_for_layout; ?>
<!-- content end -->

<!-- footer -->
<?php $this->layout->element('footer'); ?>
<!-- footer end -->
<!-- page generated in {elapsed_time} seconds using {memory_usage} memory -->
</body>
</html>