<?php
/**
 * Template Part: Breadcrumbs (Rank Math 래퍼)
 *
 * Rank Math SEO 플러그인이 활성화되어 있으면 `rank_math_breadcrumbs()` 출력.
 * 비활성화 상태라면 페이지별로 수동 브레드크럼이 필요할 수 있음.
 *
 * 사용법:
 * get_template_part('template-parts/common/breadcrumbs');
 */

if (function_exists('rank_math_the_breadcrumbs')) {
    echo '<div class="cm-breadcrumbs">';
    rank_math_the_breadcrumbs();
    echo '</div>';
}
