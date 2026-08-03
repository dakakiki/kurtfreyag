<?php
$snItems = getSNOptions();
?>


<?php if (!empty($snItems['link'])): ?>
<div class="sn-items">

	<?php foreach ($snItems['link'] as $sn => $sn_item): ?>
		
		<?php if (!empty($sn_item['icon'])): ?>

			<?php if (!empty($sn_item['link'])): ?>

				<a class="sn-items__item" href="<?= esc_url($sn_item['link']['url']); ?>" title="<?= esc_html($sn_item['link']['title']); ?>" target="<?= esc_html($sn_item['link']['target']); ?>">

			<?php endif; ?>

			<?= inline_svg_with_unique_ids($sn_item['icon']['url'], $sn_item['icon']['name'].'_'.$sn_item['icon']['ID'].'_'.rand(100, 999)); ?>

			<?php if (!empty($sn_item['link'])): ?>
				</a>
			<?php endif; ?>

		<?php endif; ?>

	<?php endforeach; ?>

</div>
<?php endif; ?>