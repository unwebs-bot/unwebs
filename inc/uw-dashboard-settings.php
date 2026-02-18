<?php
/**
 * UW Dashboard Settings - 실시간 현황 대시보드 관리자 설정
 */

if (!defined('ABSPATH')) exit;

/**
 * 관리자 메뉴 등록
 */
add_action('admin_menu', 'uw_dashboard_add_menu');
function uw_dashboard_add_menu()
{
    add_menu_page(
        '대시보드 현황',
        '대시보드 현황',
        'manage_options',
        'uw-dashboard-settings',
        'uw_dashboard_settings_page',
        'dashicons-chart-line',
        31
    );
}

/**
 * 설정 등록
 */
add_action('admin_init', 'uw_dashboard_register_settings');
function uw_dashboard_register_settings()
{
    register_setting('uw_dashboard_options', 'uw_dashboard_projects', 'uw_dashboard_sanitize_items');
    register_setting('uw_dashboard_options', 'uw_dashboard_consults', 'uw_dashboard_sanitize_items');
}

/**
 * 항목 살균
 */
function uw_dashboard_sanitize_items($input)
{
    if (!is_array($input)) return array();

    $sanitized = array();
    foreach ($input as $item) {
        if (empty($item['title'])) continue;
        $sanitized[] = array(
            'type'   => sanitize_text_field($item['type']),
            'title'  => sanitize_text_field($item['title']),
            'date'   => sanitize_text_field($item['date']),
            'status' => sanitize_text_field($item['status']),
        );
    }
    return $sanitized;
}

/**
 * 관리자 페이지 스타일
 */
add_action('admin_enqueue_scripts', 'uw_dashboard_admin_assets');
function uw_dashboard_admin_assets($hook)
{
    if ($hook !== 'toplevel_page_uw-dashboard-settings') return;

    wp_enqueue_style('uw-dashboard-admin', get_theme_file_uri('/assets/css/cpt/dashboard/admin.css'), array(), filemtime(get_template_directory() . '/assets/css/cpt/dashboard/admin.css'));
}

/**
 * 설정 페이지 렌더링
 */
function uw_dashboard_settings_page()
{
    if (!current_user_can('manage_options')) return;

    $projects = get_option('uw_dashboard_projects', array());
    $consults = get_option('uw_dashboard_consults', array());

    $project_statuses = array('진행중', '완료');
    $consult_statuses = array('접수중', '상담완료');
    $project_types    = array('신규', '리뉴얼', '유지보수');
    $consult_types    = array('견적의뢰', '문의');
    ?>
    <div class="wrap uw-dash-wrap">
        <h1>대시보드 현황 관리</h1>
        <p class="description">메인페이지에 표시되는 실시간 프로젝트 현황과 상담/견적 안내 현황을 관리합니다.</p>

        <?php settings_errors('uw_dashboard_options'); ?>

        <form method="post" action="options.php" id="uwDashboardForm">
            <?php settings_fields('uw_dashboard_options'); ?>

            <!-- 프로젝트 현황 -->
            <div class="uw-dash-section">
                <h2>실시간 프로젝트 현황</h2>
                <table class="widefat uw-dash-table" id="uwProjectTable">
                    <thead>
                        <tr>
                            <th style="width:100px;">구분</th>
                            <th>제목</th>
                            <th style="width:140px;">날짜</th>
                            <th style="width:100px;">상태</th>
                            <th style="width:50px;">삭제</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($projects)) : foreach ($projects as $i => $item) : ?>
                        <tr>
                            <td>
                                <select name="uw_dashboard_projects[<?php echo $i; ?>][type]">
                                    <?php foreach ($project_types as $t) : ?>
                                    <option value="<?php echo esc_attr($t); ?>"<?php selected($item['type'], $t); ?>><?php echo esc_html($t); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="text" name="uw_dashboard_projects[<?php echo $i; ?>][title]" value="<?php echo esc_attr($item['title']); ?>" class="widefat"></td>
                            <td><input type="date" name="uw_dashboard_projects[<?php echo $i; ?>][date]" value="<?php echo esc_attr($item['date']); ?>"></td>
                            <td>
                                <select name="uw_dashboard_projects[<?php echo $i; ?>][status]">
                                    <?php foreach ($project_statuses as $s) : ?>
                                    <option value="<?php echo esc_attr($s); ?>"<?php selected($item['status'], $s); ?>><?php echo esc_html($s); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><button type="button" class="button uw-dash-remove">&times;</button></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
                <button type="button" class="button uw-dash-add" data-target="project">+ 항목 추가</button>
            </div>

            <!-- 상담/견적 현황 -->
            <div class="uw-dash-section">
                <h2>상담 및 견적 안내 현황</h2>
                <table class="widefat uw-dash-table" id="uwConsultTable">
                    <thead>
                        <tr>
                            <th style="width:100px;">구분</th>
                            <th>제목</th>
                            <th style="width:140px;">날짜</th>
                            <th style="width:100px;">상태</th>
                            <th style="width:50px;">삭제</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($consults)) : foreach ($consults as $i => $item) : ?>
                        <tr>
                            <td>
                                <select name="uw_dashboard_consults[<?php echo $i; ?>][type]">
                                    <?php foreach ($consult_types as $t) : ?>
                                    <option value="<?php echo esc_attr($t); ?>"<?php selected($item['type'], $t); ?>><?php echo esc_html($t); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="text" name="uw_dashboard_consults[<?php echo $i; ?>][title]" value="<?php echo esc_attr($item['title']); ?>" class="widefat"></td>
                            <td><input type="date" name="uw_dashboard_consults[<?php echo $i; ?>][date]" value="<?php echo esc_attr($item['date']); ?>"></td>
                            <td>
                                <select name="uw_dashboard_consults[<?php echo $i; ?>][status]">
                                    <?php foreach ($consult_statuses as $s) : ?>
                                    <option value="<?php echo esc_attr($s); ?>"<?php selected($item['status'], $s); ?>><?php echo esc_html($s); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><button type="button" class="button uw-dash-remove">&times;</button></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
                <button type="button" class="button uw-dash-add" data-target="consult">+ 항목 추가</button>
            </div>

            <?php submit_button('저장'); ?>
        </form>
    </div>

    <script>
    (function() {
        var projectTypes  = <?php echo wp_json_encode($project_types); ?>;
        var consultTypes  = <?php echo wp_json_encode($consult_types); ?>;
        var projectStats  = <?php echo wp_json_encode($project_statuses); ?>;
        var consultStats  = <?php echo wp_json_encode($consult_statuses); ?>;
        var today = new Date().toISOString().slice(0, 10);

        function makeOptions(arr, selected) {
            return arr.map(function(v) {
                return '<option value="' + v + '"' + (v === selected ? ' selected' : '') + '>' + v + '</option>';
            }).join('');
        }

        function reindex(table, prefix) {
            var rows = table.querySelectorAll('tbody tr');
            rows.forEach(function(row, idx) {
                row.querySelectorAll('input, select').forEach(function(el) {
                    var name = el.getAttribute('name');
                    if (name) {
                        el.setAttribute('name', name.replace(/\[\d+\]/, '[' + idx + ']'));
                    }
                });
            });
        }

        document.querySelectorAll('.uw-dash-add').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var target = this.getAttribute('data-target');
                var isProject = target === 'project';
                var table = document.getElementById(isProject ? 'uwProjectTable' : 'uwConsultTable');
                var tbody = table.querySelector('tbody');
                var idx = tbody.querySelectorAll('tr').length;
                var prefix = isProject ? 'uw_dashboard_projects' : 'uw_dashboard_consults';
                var types = isProject ? projectTypes : consultTypes;
                var stats = isProject ? projectStats : consultStats;

                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td><select name="' + prefix + '[' + idx + '][type]">' + makeOptions(types) + '</select></td>' +
                    '<td><input type="text" name="' + prefix + '[' + idx + '][title]" class="widefat" placeholder="제목을 입력하세요"></td>' +
                    '<td><input type="date" name="' + prefix + '[' + idx + '][date]" value="' + today + '"></td>' +
                    '<td><select name="' + prefix + '[' + idx + '][status]">' + makeOptions(stats) + '</select></td>' +
                    '<td><button type="button" class="button uw-dash-remove">&times;</button></td>';
                tbody.appendChild(tr);
            });
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('uw-dash-remove')) {
                var row = e.target.closest('tr');
                var table = row.closest('table');
                var prefix = table.id === 'uwProjectTable' ? 'uw_dashboard_projects' : 'uw_dashboard_consults';
                row.remove();
                reindex(table, prefix);
            }
        });
    })();
    </script>
    <?php
}

/**
 * 대시보드 데이터 가져오기
 */
function uw_dashboard_get_projects()
{
    return get_option('uw_dashboard_projects', array());
}

function uw_dashboard_get_consults()
{
    return get_option('uw_dashboard_consults', array());
}

/**
 * 상태에 따른 CSS modifier 클래스 반환
 */
function uw_dashboard_status_class($status)
{
    switch ($status) {
        case '접수중':   return 'uw-dashboard__badge--receiving';
        case '상담완료':
        case '완료':     return 'uw-dashboard__badge--completed';
        default:         return 'uw-dashboard__badge--ongoing';
    }
}
