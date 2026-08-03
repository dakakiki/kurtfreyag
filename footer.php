<?php

    $footerSetttings = getFooterSettings();
?>

<footer id="kontakt">

    <div class="footer">

        <?php if(
                    isset($footerSetttings['f_cta_button']) 
                    && $footerSetttings['f_cta_button'] != '' 
                    && isset($footerSetttings['f_cta_button']['url']) 
                    && isset($footerSetttings['f_cta_text'])
                    && $footerSetttings['f_cta_text'] != ''): ?>
        <div class="footer__cta">
            
            <h3><?= wp_kses_post($footerSetttings['f_cta_text']); ?></h3>

            <a 
                class="btn-contact"
                href="<?= esc_url($footerSetttings['f_cta_button']['url']); ?>" 
                title="<?= esc_attr($footerSetttings['f_cta_button']['title']); ?>" 
                target="<?= esc_attr($footerSetttings['f_cta_button']['target']); ?>"
                >
                <?= esc_attr($footerSetttings['f_cta_button']['title']); ?>
            </a>
        </div>
        <?php endif; ?>

        <?php if (!empty($footerSetttings['f_contact_info'])): ?>
            <div class="footer__contact-info">
                <?= wp_kses_post($footerSetttings['f_contact_info']); ?>
            </div>
        <?php endif; ?>

        <div class="footer__sn">
            <?php
                include 'template_parts/sn.php';
            ?>
        </div>

        <div class="footer__row">

            <div class="footer__left">

                <?php if (!empty($footerSetttings['f_copy_text'])): ?>
                    <div class="footer__left--copy">
                        <?= wp_kses_post($footerSetttings['f_copy_text']); ?>
                    </div>
                <?php endif; ?>

            </div>

            <div class="footer__right">

                <div class="footer__right--nav">
                    <?php
                        include 'template_parts/footer_menu.php';
                    ?>
                </div>

            </div>

        </div>

    </div>

</footer>

<?php wp_footer(); ?>

</body>
</html>