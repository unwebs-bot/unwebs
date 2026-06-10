<?php
/**
 * Stats Helpers — 메인/계열 페이지에서 공용 사용
 *
 * - section-stats.php / section-contact.php 양쪽에서 사용
 *   각 섹션 파일에 정의돼 있던 함수를 분리해 단일 진입점 보장
 *   (front-page 외 페이지에서 section-contact 단독 호출 시 fatal 방지)
 */

if (!defined('ABSPATH')) exit;

if (!function_exists('uw_stats_digit_list')) {
    /**
     * 숫자 롤링용 digit 리스트 생성.
     *
     * @param int    $target    표시할 최종 숫자 (0~9)
     * @param string $direction 'up' (상승: target이 끝) | 'down' (하강: target이 시작)
     * @param int    $seed      mt_rand 시드 (디지트별 분산용)
     * @return int[] 0~9 셔플된 배열 (target 위치는 direction에 따라 결정)
     */
    function uw_stats_digit_list($target, $direction, $seed)
    {
        mt_srand($seed);
        $remaining = array();
        for ($d = 0; $d <= 9; $d++) {
            if ($d !== $target) $remaining[] = $d;
        }
        for ($i = count($remaining) - 1; $i > 0; $i--) {
            $j   = mt_rand(0, $i);
            $tmp = $remaining[$i];
            $remaining[$i] = $remaining[$j];
            $remaining[$j] = $tmp;
        }
        mt_srand();

        if ($direction === 'up') {
            $remaining[] = $target;
        } else {
            array_unshift($remaining, $target);
        }
        return $remaining;
    }
}
