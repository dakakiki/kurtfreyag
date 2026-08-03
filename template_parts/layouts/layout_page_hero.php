<?php

    $r_no = get_row_index();

    $ph_style[$r_no] = get_sub_field('styles');

    $ph_hero_height[$r_no] = (!empty($ph_style[$r_no]['style_layout_height']) && $ph_style[$r_no]['style_layout_height'] == 'h-full')
        ? 'h-full'
        : 'h-page';

    $ph_content[$r_no] = get_sub_field('content');
    $ph_image[$r_no] = get_sub_field('image');
?>

<div id="<?php echo the_sub_field('anchor'); ?>" class="layout-page-hero <?= $ph_hero_height[$r_no]; ?>">

    <div id="lyt-<?= $r_no; ?>" class="page-hero">

        <div class="page-hero__content">

            <div class="page-hero__wrap group-fade-up">

                <?php if (!empty($ph_content[$r_no]['title'])): ?>
                    <h1 class="fade-up"><?= wp_kses_post($ph_content[$r_no]['title']); ?></h1>
                <?php endif; ?>

                <?php if (!empty($ph_content[$r_no]['text'])): ?>
                    <div class="page-hero__wrap--text fade-up"><?= wp_kses_post($ph_content[$r_no]['text']); ?></div>
                <?php endif; ?>

                <?php if (!empty($ph_content[$r_no]['buttons'])): ?>

                    <div class="page-hero__wrap--btns fade-up">

                        <?php foreach ($ph_content[$r_no]['buttons'] as $b => $lnk): ?>
                            
                            <?php if (!empty($lnk['button_link'])): ?>
                                <a 
                                    class="<?= $lnk['button_style']; ?>"
                                    href="<?= esc_url($lnk['button_link']['url']); ?>" 
                                    title="<?= esc_html($lnk['button_link']['title']); ?>" 
                                    target="<?= esc_attr($lnk['button_link']['target']); ?>"
                                >

                                    <span><?= esc_html($lnk['button_link']['title']); ?></span>

                                </a>
                            <?php endif; ?>
                            
                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>

        </div>

        <?php if (!empty($ph_image[$r_no])): ?>
        <div class="page-hero__image fade-right">

            <div class="page-hero__image--overlay"></div>

            <?= theme_acf_image(
                    $ph_image[$r_no],
                    'full',
                    array(
                        'loading'       => 'eager',
                        'fetchpriority' => 'high',
                        'sizes'         => '100vw',
                    )
                ); ?>
        </div>
        <?php endif; ?>

    </div>

</div>