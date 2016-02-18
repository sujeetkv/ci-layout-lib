<!DOCTYPE html>
<html>
<head>
<title><?php echo $title_for_layout; ?></title>

<?php echo $css_for_layout; ?>

<?php echo $js_for_layout; ?>

<?php echo $this->layout->get_block('dynamic_head_block'); ?>
</head>

<body>
<!-- header -->
<?php $this->load->view('_layouts/elements/header'); ?>
<!-- header end -->

<!-- content -->
<?php echo $content_for_layout; ?>
<!-- content end -->

<!-- footer -->
<?php $this->load->view('_layouts/elements/footer'); ?>
<!-- footer end -->
<!-- page generated in {elapsed_time} seconds using {memory_usage} memory -->
</body>
</html>