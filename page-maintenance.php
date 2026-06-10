<?php
/**
 * Template Name: 유지보수 허브 (Maintenance)
 *
 * 하위: 유지보수 신청 / 비용 안내 (건별·부가 서비스 통합)
 * FAQ 배치: Q11, Q12
 */
get_header();
?>


<main class="cm-main" id="main-content" role="main">

  <h1 class="blind">홈페이지 유지보수</h1>

  <section class="maintenance-intro-con sub-content-con">
    <div class="area">
      <div class="cm-tit-box" data-animate="fade-up">
        <span class="cm-tit-sub">MAINTENANCE</span>
        <h2 class="cm-tit">워드프레스 유지보수</h2>
        <p class="cm-tit-txt">건별 작업부터 부가 서비스까지. 빠른 대응으로 안정적인 운영을 지원합니다.</p>
      </div>

      <div class="maintenance-type-list" data-stagger>
        <a href="<?php echo esc_url(home_url('/maintenance-request')); ?>" class="maintenance-type-item" data-animate="fade-up">
          <span class="maintenance-type-tag">신청</span>
          <h3 class="maintenance-type-tit">유지보수 신청</h3>
          <p class="maintenance-type-txt">수정사항을 접수해 주세요. 접수 후 72시간 내 완료 회신.</p>
          <span class="maintenance-type-link">신청하기 →</span>
        </a>
        <a href="<?php echo esc_url(home_url('/maintenance-pricing')); ?>" class="maintenance-type-item" data-animate="fade-up">
          <span class="maintenance-type-tag">비용</span>
          <h3 class="maintenance-type-tit">비용 안내</h3>
          <p class="maintenance-type-txt">건별 유지보수와 부가 서비스 항목별 비용을 한 번에 확인.</p>
          <span class="maintenance-type-link">자세히 보기 →</span>
        </a>
      </div>

      <div class="maintenance-cta" data-animate="fade-up">
        <a href="<?php echo esc_url(home_url('/maintenance-request')); ?>" class="cm-btn cm-btn-primary">유지보수 신청하기</a>
      </div>
    </div>
  </section>

  <!-- FAQ 섹션 -->
  <?php
  set_query_var('faq_items', starter_faq_items(array(11, 12)));
  set_query_var('faq_title', '유지보수 FAQ');
  get_template_part('template-parts/common/faq-section');
  ?>

</main>

<?php get_footer(); ?>
