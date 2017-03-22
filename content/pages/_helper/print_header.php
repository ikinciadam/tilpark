<?php if(!isset($print['size'])) { $print['size'] = 'A4'; } ?>
<div class="print-page" size="<?php echo $print['size']; ?>">

	<div class="print-tilpark-logo">
		<img src="<?php site_url('content/themes/default/img/logo_blank.png'); ?>" class="img-responsive">
	</div>

	<?php if(@$print['title']): ?>
		<div class="text-center absolute full-width">
			<h3 class="content-title ff-2 text-center"><?php echo $print['title']; ?></h3>
		</div> <!-- /.text-center -->
	<?php endif; ?>


	<div class="pull-right">
		<div class="text-right">
			<?php if(@$print['date']): ?><h4 class="content-title"><small class="text-muted"><?php echo til_get_date_lang(date('d F Y - H:i', strtotime($print['date']))); ?></small></h4><?php endif; ?>
			<?php if(@$print['barcode']): ?><div><img src="<?php barcode_url($print['barcode'], array('position'=>'right') ); ?>" /></div><?php endif; ?>
			<?php if(@$print['barcode']): ?><div class="text-right"><?php echo $print['barcode']; ?></div><?php endif; ?>
		</div>
	</div> <!-- /.text-right -->