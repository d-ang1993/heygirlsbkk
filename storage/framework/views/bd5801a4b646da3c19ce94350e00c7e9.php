<?php
$footer_enable = get_theme_mod('footer_enable', true);
$customer_title = get_theme_mod('footer_customer_title', 'CUSTOMER CENTER');
$customer_phone = get_theme_mod('footer_customer_phone', '000-0000-00000');
$customer_hours = get_theme_mod('footer_customer_hours', "OPEN Mon\Fri 13:00 ~ 오후 18:00\Sat/Sun/Public Holiday OFF");
$company_brand = get_theme_mod('footer_company_brand', 'HEYGIRLSBKK.');
$company_info = get_theme_mod('footer_company_info', "COMPANY : (주)히프나틱\nCOMPANY: HYPNOTIC INC.\nOWNER : 김윤주, 김지수\nOWNER: KIM YUNJU, KIM JISOO\nTEL: 070-4364-9255\nBUSINESS NUMBER: 589-88-00495 [사업자정보확인]\nADD : 서울특별시 중구 소공로 70(충무로1가, 서울 중앙 우체국) 히프나틱 물류센터\nMAIL ORDER LICENSE: 2017-서울중구-0147\nCHIEF PRIVACY OFFICER : 김윤주, 김지수 (PLUSHYPNOTIC@HYPNOTIC.CO.KR)\n광고, 제휴문의 : PLUSHYPNOTIC@NAVER.COM");
$tiktok_url = get_theme_mod('footer_tiktok_url', '');
$instagram_url = get_theme_mod('footer_instagram_url', '');
$shopee_url = get_theme_mod('footer_shopee_url', '');
$line_url = get_theme_mod('footer_line_url', '');
?>

<?php if($footer_enable): ?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Customer Center Column -->
            <div class="footer-column footer-customer">
                <h3 class="footer-title"><?php echo e($customer_title); ?></h3>
                <div class="footer-phone"><?php echo e($customer_phone); ?></div>
                <div class="footer-hours">
                    <?php $__currentLoopData = explode("\n", $customer_hours); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(trim($line)): ?>
                            <div><?php echo e(trim($line)); ?></div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <!-- Company Information Column -->
            <div class="footer-column footer-company">
                <h3 class="footer-brand"><?php echo e($company_brand); ?></h3>
                <div class="footer-company-info">
                    <?php $__currentLoopData = explode("\n", $company_info); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(trim($line)): ?>
                            <div><?php echo e(trim($line)); ?></div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <!-- Social Links Column -->
            <div class="footer-column footer-social">
                <?php if(!empty($tiktok_url) || !empty($instagram_url) || !empty($shopee_url) || !empty($line_url)): ?>
                    <div class="footer-social-grid">
                        <?php if(!empty($tiktok_url)): ?>
                            <a href="<?php echo e($tiktok_url); ?>" target="_blank" class="footer-social-link footer-social-tiktok">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/>
                                </svg>
                                <span>TikTok</span>
                            </a>
                        <?php endif; ?>
                        
                        <?php if(!empty($instagram_url)): ?>
                            <a href="<?php echo e($instagram_url); ?>" target="_blank" class="footer-social-link footer-social-instagram">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                                <span>Instagram</span>
                            </a>
                        <?php endif; ?>
                        
                        <?php if(!empty($shopee_url)): ?>
                            <a href="<?php echo e($shopee_url); ?>" target="_blank" class="footer-social-link footer-social-shopee">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                                <span>Shopee</span>
                            </a>
                        <?php endif; ?>
                        
                        <?php if(!empty($line_url)): ?>
                            <a href="<?php echo e($line_url); ?>" target="_blank" class="footer-social-link footer-social-line">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.349 0 .63.285.63.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/>
                                </svg>
                                <span>Line</span>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</footer>
<?php endif; ?><?php /**PATH /Users/dang/Local Sites/heygirlsbkk/app/public/wp-content/themes/heygirlsbkk/resources/views/partials/footer.blade.php ENDPATH**/ ?>