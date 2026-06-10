<?php
/**
 * UW Board Admin Interface
 * 
 * 관리자 메뉴 및 페이지 처리
 * 
 * @package starter-theme
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class UW_Board_Admin
{

    private static $instance = null;

    /**
     * 게시판 설정 옵션 키
     */
    const OPTION_KEY = 'uw_board_settings';

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_uw_board_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_uw_board_delete_board', array($this, 'ajax_delete_board'));
        add_action('wp_ajax_uw_board_save_post', array($this, 'ajax_save_post'));
        add_action('wp_ajax_uw_board_delete_post', array($this, 'ajax_delete_post'));
        add_action('wp_ajax_uw_board_upload_image', array($this, 'ajax_upload_image'));
        add_action('wp_ajax_uw_board_bulk_empty_posts', array($this, 'ajax_bulk_empty_posts'));
        add_action('wp_ajax_uw_board_bulk_delete_boards', array($this, 'ajax_bulk_delete_boards'));
        add_action('wp_ajax_uw_board_export_csv', array($this, 'ajax_export_csv'));
        add_action('wp_ajax_uw_board_import_csv', array($this, 'ajax_import_csv'));
    }

    /**
     * 관리자 메뉴 등록
     */
    public function add_admin_menu()
    {
        // 메인 메뉴: 게시판
        add_menu_page(
            'UW Board Center',
            '게시판',
            'manage_options',
            'uw-board',
            array($this, 'render_dashboard_page'),
            'dashicons-welcome-write-blog',
            25
        );

        // 서브메뉴 1: 게시판 관리 (대시보드)
        add_submenu_page(
            'uw-board',
            '게시판 관리',
            '게시판 관리',
            'manage_options',
            'uw-board',
            array($this, 'render_dashboard_page')
        );

        // 서브메뉴 2: 게시판 생성
        add_submenu_page(
            'uw-board',
            '게시판 생성',
            '게시판 생성',
            'manage_options',
            'uw-board-settings',
            array($this, 'render_settings_page')
        );

        // 동적 서브메뉴: 각 게시판별
        $boards = $this->get_all_boards();
        foreach ($boards as $slug => $board) {
            // column 보드는 별도 'column' CPT — 표준 WP-admin 리스트로 직접 링크
            if ($slug === 'column' && post_type_exists('column')) {
                add_submenu_page(
                    'uw-board',
                    $board['name'],
                    $board['name'],
                    'edit_posts',
                    'edit.php?post_type=column'
                );
                continue;
            }
            add_submenu_page(
                'uw-board',
                $board['name'],
                $board['name'],
                'manage_options',
                'uw-board-' . $slug,
                array($this, 'render_board_manager_page')
            );
        }

        // 관리자 메뉴 하이라이트 수정 (게시판 수정 시 '게시판 생성' 대신 '게시판 관리' 활성화)
        add_filter('parent_file', array($this, 'fix_admin_menu_highlight'));
        add_filter('submenu_file', array($this, 'fix_admin_submenu_highlight'));
    }

    /**
     * 게시판 수정 시 부모 메뉴 하이라이트 수정
     */
    public function fix_admin_menu_highlight($parent_file)
    {
        global $pagenow, $post_type;

        if ($pagenow === 'admin.php' && isset($_GET['page']) && $_GET['page'] === 'uw-board-settings' && isset($_GET['edit'])) {
            return 'uw-board';
        }

        // column CPT 편집·리스트·신규 작성 시 — uw-board 부모 메뉴 활성화 유지
        if (in_array($pagenow, array('edit.php', 'post.php', 'post-new.php'), true)) {
            $pt = isset($_GET['post_type']) ? sanitize_key($_GET['post_type']) : $post_type;
            if ($pt === 'column') {
                return 'uw-board';
            }
        }

        return $parent_file;
    }

    /**
     * 게시판 수정 시 서브메뉴 하이라이트 수정
     */
    public function fix_admin_submenu_highlight($submenu_file)
    {
        global $pagenow, $post_type;

        if ($pagenow === 'admin.php' && isset($_GET['page']) && $_GET['page'] === 'uw-board-settings' && isset($_GET['edit'])) {
            return 'uw-board'; // 게시판 관리 메뉴 활성화
        }

        // column CPT 페이지 — uw-board 서브메뉴 "전문 칼럼" 활성화
        if (in_array($pagenow, array('edit.php', 'post.php', 'post-new.php'), true)) {
            $pt = isset($_GET['post_type']) ? sanitize_key($_GET['post_type']) : $post_type;
            if ($pt === 'column') {
                return 'edit.php?post_type=column';
            }
        }

        return $submenu_file;
    }

    /**
     * 관리자 에셋 로드
     */
    public function enqueue_admin_assets($hook)
    {
        if (strpos($hook, 'uw-board') === false) {
            return;
        }

        // WordPress Media Uploader
        wp_enqueue_media();


        // Summernote for editor
        wp_enqueue_style('summernote', 'https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css');
        wp_enqueue_script('summernote', 'https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js', array('jquery'), '0.8.18', true);

        // Custom admin styles
        wp_enqueue_style('uw-board-admin', get_theme_file_uri('/assets/css/cpt/board/admin.css'), array(), '1.0.3');
        wp_enqueue_script('uw-board-admin', get_theme_file_uri('/assets/js/CPT/board/uw-board-admin.js'), array('jquery', 'summernote', 'media-upload'), '1.0.2', true);

        wp_localize_script('uw-board-admin', 'uwBoardAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('uw_board_admin_nonce'),
        ));
    }

    /**
     * 모든 게시판 설정 가져오기
     */
    public function get_all_boards()
    {
        return get_option(self::OPTION_KEY, array());
    }

    /**
     * 특정 게시판 설정 가져오기
     */
    public function get_board_settings($slug)
    {
        $boards = $this->get_all_boards();
        return isset($boards[$slug]) ? $boards[$slug] : null;
    }

    /**
     * 게시판 설정 저장
     */
    public function save_board_settings($slug, $settings)
    {
        $boards = $this->get_all_boards();
        $boards[$slug] = $settings;
        update_option(self::OPTION_KEY, $boards);

        // Taxonomy term 생성/업데이트
        if (!term_exists($slug, 'uw_board_type')) {
            wp_insert_term($settings['name'], 'uw_board_type', array('slug' => $slug));
        }
    }

    /**
     * 대시보드 페이지 렌더링
     */
    public function render_dashboard_page()
    {
        $boards = $this->get_all_boards();
        ?>
        <div class="wrap uw-board-admin">
            <h1>게시판 관리</h1>

            <div class="uw-board-dashboard">
                <?php if (empty($boards)): ?>
                    <div class="uw-board-empty">
                        <p>등록된 게시판이 없습니다.</p>
                        <a href="<?php echo admin_url('admin.php?page=uw-board-settings'); ?>" class="button button-primary">
                            새 게시판 만들기
                        </a>
                    </div>
                <?php else: ?>
                    <div class="tablenav top">
                        <div class="alignleft actions bulkactions">
                            <select name="bulk_action" id="bulk-action-selector">
                                <option value="">일괄 동작</option>
                                <option value="empty_posts">모든 게시글 비우기</option>
                                <option value="delete_boards">영구적으로 삭제하기</option>
                            </select>
                            <button type="button" id="doaction" class="button action">적용</button>
                        </div>
                    </div>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <td class="manage-column column-cb check-column" style="width: 30px;">
                                    <input type="checkbox" id="cb-select-all" />
                                </td>
                                <th>게시판명</th>
                                <th>숏코드</th>
                                <th>읽기 권한</th>
                                <th>쓰기 권한</th>
                                <th>글 수</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody id="the-list">
                            <?php foreach ($boards as $slug => $board):
                                $post_count = $this->get_board_post_count($slug);
                                ?>
                                <tr>
                                    <th scope="row" class="check-column">
                                        <input type="checkbox" name="board_slugs[]" value="<?php echo esc_attr($slug); ?>"
                                            class="board-checkbox" />
                                    </th>
                                    <td>
                                        <strong>
                                            <a href="<?php echo admin_url('admin.php?page=uw-board-settings&edit=' . $slug); ?>">
                                                <?php echo esc_html($board['name']); ?>
                                            </a>
                                        </strong>
                                    </td>
                                    <td>
                                        <code>[uw_board name="<?php echo esc_attr($slug); ?>"]</code>
                                    </td>
                                    <td><?php echo $board['read_permission'] === 'all' ? '전체' : '로그인 사용자'; ?></td>
                                    <td><?php echo $board['write_permission'] === 'all' ? '전체' : '로그인 사용자'; ?></td>
                                    <td><?php echo $post_count; ?>개</td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=uw-board-settings&edit=' . $slug); ?>"
                                            class="button button-small">설정</a>
                                        <a href="<?php echo admin_url('admin.php?page=uw-board-' . $slug); ?>"
                                            class="button button-small">글 관리</a>
                                        <button type="button" class="button button-small uw-delete-board"
                                            data-slug="<?php echo esc_attr($slug); ?>">삭제</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <p style="margin-top: 20px;">
                        <a href="<?php echo admin_url('admin.php?page=uw-board-settings'); ?>" class="button button-primary">
                            + 새 게시판 추가
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * 설정 페이지 렌더링
     */
    public function render_settings_page()
    {
        $edit_slug = isset($_GET['edit']) ? sanitize_key($_GET['edit']) : '';
        $board = $edit_slug ? $this->get_board_settings($edit_slug) : null;
        $is_edit = !empty($board);
        ?>
        <div class="wrap uw-board-admin">
            <h1><?php echo $is_edit ? '게시판 수정' : '새 게시판 만들기'; ?></h1>

            <?php if ($is_edit): ?>
                <!-- 탭 네비게이션 -->
                <nav class="uw-settings-tabs">
                    <a href="#" class="uw-tab-link active" data-tab="basic">기본 수정</a>
                    <a href="#" class="uw-tab-link" data-tab="bulk">대량관리</a>
                </nav>
            <?php endif; ?>

            <!-- 기본 수정 탭 -->
            <div class="uw-tab-panel active" id="tab-basic">
                <form id="uw-board-settings-form" class="uw-board-form">
                    <input type="hidden" name="original_slug" value="<?php echo esc_attr($edit_slug); ?>">

                    <table class="form-table">
                        <tr>
                            <th><label for="board_name">게시판 이름 *</label></th>
                            <td>
                                <input type="text" id="board_name" name="name" class="regular-text"
                                    value="<?php echo esc_attr($board['name'] ?? ''); ?>" required>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="board_slug">슬러그 (영문) *</label></th>
                            <td>
                                <input type="text" id="board_slug" name="slug" class="regular-text"
                                    value="<?php echo esc_attr($edit_slug); ?>" pattern="[a-z0-9_-]+" <?php echo $is_edit ? 'readonly' : ''; ?> required>
                                <p class="description">영문 소문자, 숫자, 밑줄, 하이픈만 사용 가능</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="per_page">페이지당 글 수</label></th>
                            <td>
                                <select id="per_page" name="per_page">
                                    <?php foreach (array(5, 10, 15, 20, 30) as $num): ?>
                                        <option value="<?php echo $num; ?>" <?php selected(($board['per_page'] ?? 10), $num); ?>>
                                            <?php echo $num; ?>개
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="read_permission">읽기 권한</label></th>
                            <td>
                                <select id="read_permission" name="read_permission">
                                    <option value="all" <?php selected(($board['read_permission'] ?? 'all'), 'all'); ?>>
                                        제한 없음
                                    </option>
                                    <option value="logged_in" <?php selected(($board['read_permission'] ?? ''), 'logged_in'); ?>>
                                        로그인 사용자만
                                    </option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="write_permission">쓰기 권한</label></th>
                            <td>
                                <select id="write_permission" name="write_permission">
                                    <option value="all" <?php selected(($board['write_permission'] ?? 'all'), 'all'); ?>>
                                        제한 없음 (비회원 포함)
                                    </option>
                                    <option value="logged_in" <?php selected(($board['write_permission'] ?? ''), 'logged_in'); ?>>
                                        로그인 사용자만
                                    </option>
                                </select>
                                <p class="description">비회원 쓰기 허용 시 비밀번호 입력이 필수가 됩니다.</p>
                            </td>
                        </tr>
                        <tr>
                            <th>개인정보 동의</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="require_privacy" value="1" <?php checked($board['require_privacy'] ?? true); ?>>
                                    글쓰기 시 개인정보 수집 동의 필수
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="skin">스킨</label></th>
                            <td>
                                <select id="skin" name="skin">
                                    <option value="style01" <?php selected(($board['skin'] ?? 'style01'), 'style01'); ?>>Style 1
                                        (리스트형)</option>
                                    <option value="style02" <?php selected(($board['skin'] ?? ''), 'style02'); ?>>Style 2 (카드형)
                                    </option>
                                    <option value="style03" <?php selected(($board['skin'] ?? ''), 'style03'); ?>>Style 3 (섬네일형)
                                    </option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label>카테고리 설정</label></th>
                            <td>
                                <div class="uw-category-settings">
                                    <div id="uw-category-list" class="uw-category-list">
                                        <?php
                                        $categories = $board['categories'] ?? array();
                                        if (!empty($categories)):
                                            foreach ($categories as $index => $cat):
                                        ?>
                                            <div class="uw-category-item" data-index="<?php echo $index; ?>">
                                                <span class="uw-category-drag dashicons dashicons-menu"></span>
                                                <input type="text" name="categories[]" value="<?php echo esc_attr($cat); ?>" placeholder="카테고리명" class="regular-text">
                                                <button type="button" class="button uw-remove-category" title="삭제">
                                                    <span class="dashicons dashicons-trash"></span>
                                                </button>
                                            </div>
                                        <?php
                                            endforeach;
                                        endif;
                                        ?>
                                    </div>
                                    <button type="button" id="uw-add-category" class="button">
                                        <span class="dashicons dashicons-plus-alt2"></span> 카테고리 추가
                                    </button>
                                    <p class="description">글쓰기 시 선택할 수 있는 카테고리를 관리합니다. 드래그하여 순서를 변경할 수 있습니다.</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>카테고리 필수 여부</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="category_required" value="1" <?php checked($board['category_required'] ?? false); ?>>
                                    글쓰기 시 카테고리 선택 필수
                                </label>
                                <p class="description">카테고리가 등록된 경우에만 적용됩니다.</p>
                            </td>
                        </tr>
                    </table>

                    <?php if (!$is_edit): ?>
                        <h2>최신글 설정</h2>
                        <table class="form-table">
                            <tr>
                                <th><label for="latest_page">최신글 이동 페이지</label></th>
                                <td>
                                    <select id="latest_page" name="latest_page">
                                        <option value="">선택하세요</option>
                                        <?php
                                        $pages = get_pages();
                                        foreach ($pages as $page) {
                                            echo '<option value="' . esc_url(get_permalink($page)) . '">'
                                                . esc_html($page->post_title) . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <p class="description">최신글을 클릭하면 선택된 페이지로 이동합니다.<br>
                                        최신글 숏코드를 사용하면 메인페이지 또는 사이드바에 새로 등록된 게시글을 표시할 수 있습니다.</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="latest_limit">출력 개수</label></th>
                                <td>
                                    <input type="number" id="latest_limit" name="latest_limit" value="5" min="1" max="20"
                                        style="width: 80px;">
                                    <p class="description">메인페이지 등에 보여줄 최신글 개수</p>
                                </td>
                            </tr>
                        </table>
                    <?php endif; ?>

                    <?php if ($is_edit): ?>
                        <h2>최신글 숏코드(Shortcode)</h2>
                        <table class="form-table">
                            <tr>
                                <th>숏코드 미리보기</th>
                                <td>
                                    <?php
                                    $saved_url = $board['latest_page'] ?? '';
                                    $saved_limit = $board['latest_limit'] ?? 5;
                                    ?>
                                    <input type="text" id="latest-shortcode-preview"
                                        value='[latest_posts id="<?php echo esc_attr($edit_slug); ?>" url="<?php echo esc_attr($saved_url); ?>" limit="<?php echo esc_attr($saved_limit); ?>"]'
                                        readonly class="regular-text code"
                                        style="width: 100%; max-width: 500px; background: #f0f0f1;">
                                    <p class="description">최신글 리스트를 생성합니다. 위 숏코드를 메인페이지 또는 사이드바에 입력하세요.</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="latest_page">최신글 이동 페이지</label></th>
                                <td>
                                    <select id="latest_page" name="latest_page">
                                        <option value="">선택하세요</option>
                                        <?php
                                        $pages = get_pages();
                                        foreach ($pages as $page) {
                                            $page_url = get_permalink($page);
                                            $selected = ($saved_url === $page_url) ? 'selected' : '';
                                            echo '<option value="' . esc_url($page_url) . '" ' . $selected . '>'
                                                . esc_html($page->post_title) . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <p class="description">최신글을 클릭하면 선택된 페이지로 이동합니다.</p>
                                </td>
                            </tr>
                        </table>

                        <script>
                            (function () {
                                var boardSlug = '<?php echo esc_js($edit_slug); ?>';
                                var savedLimit = '<?php echo esc_js($saved_limit); ?>';
                                var pageSelect = document.getElementById('latest_page');
                                var preview = document.getElementById('latest-shortcode-preview');

                                function updatePreview() {
                                    var url = pageSelect.value || '';
                                    preview.value = '[latest_posts id="' + boardSlug + '" url="' + url + '" limit="' + savedLimit + '"]';
                                }

                                pageSelect.addEventListener('change', updatePreview);
                            })();
                        </script>
                    <?php endif; ?>

                    <p class="submit">
                        <button type="submit" class="button button-primary">
                            <?php echo $is_edit ? '저장하기' : '게시판 생성'; ?>
                        </button>
                        <a href="<?php echo admin_url('admin.php?page=uw-board'); ?>" class="button">취소</a>
                    </p>
                </form>
            </div><!-- /.uw-tab-panel #tab-basic -->

            <?php if ($is_edit):
                // 게시글 수 가져오기
                $post_count = wp_count_posts('uw_board');
                $total_posts = 0;
                $args = array(
                    'post_type' => 'uw_board',
                    'post_status' => array('publish', 'draft', 'trash'),
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'uw_board_type',
                            'field' => 'slug',
                            'terms' => $edit_slug,
                        ),
                    ),
                    'posts_per_page' => -1,
                    'fields' => 'ids',
                );
                $count_query = new WP_Query($args);
                $total_posts = $count_query->found_posts;
                wp_reset_postdata();
                ?>
                <!-- 대량관리 탭 -->
                <div class="uw-tab-panel" id="tab-bulk">
                    <div class="uw-csv-section">
                        <h2>CSV 파일 다운로드</h2>
                        <table class="form-table">
                            <tr>
                                <th>CSV 파일 다운로드</th>
                                <td>
                                    <button type="button" id="uw-export-csv" class="button button-primary"
                                        data-slug="<?php echo esc_attr($edit_slug); ?>">
                                        다운로드
                                    </button>
                                    <p class="description">
                                        대략 <strong><?php echo number_format($total_posts); ?></strong>개의 게시글 정보를 다운로드합니다. (휴지통에 있는
                                        게시글이 포함됩니다.)<br>
                                        게시글 양이 많다면 웹호스팅의 트래픽 사용량이 높아지니 주의해주세요.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="uw-csv-section">
                        <h2>CSV 파일 업로드</h2>
                        <table class="form-table">
                            <tr>
                                <th>CSV 파일 업로드</th>
                                <td>
                                    <select id="uw-import-mode" style="min-width: 250px;">
                                        <option value="add">기존 게시글을 유지하고 추가 등록</option>
                                        <option value="replace">모든 게시글 삭제 후 새로 등록</option>
                                    </select>
                                    <div style="margin-top: 10px;">
                                        <input type="file" id="uw-csv-file" accept=".csv">
                                        <span class="uw-file-name">선택된 파일 없음</span>
                                    </div>
                                    <button type="button" id="uw-import-csv" class="button button-primary" style="margin-top: 10px;"
                                        data-slug="<?php echo esc_attr($edit_slug); ?>">
                                        업로드
                                    </button>
                                    <p class="description" style="margin-top: 10px;">
                                        CSV 파일의 인코딩을 UTF-8로 변경해서 시도해보세요.<br>
                                        너무 많은 데이터를 한 번에 업로드하게 되면 에러가 발생될 수 있으니 가급적 나눠서 여러 번 업로드해주세요.<br>
                                        댓글과 첨부파일은 등록되지 않습니다.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div id="uw-csv-progress" style="display: none; margin-top: 20px;">
                        <div class="uw-progress-bar">
                            <div class="uw-progress-fill" style="width: 0%;"></div>
                        </div>
                        <p class="uw-progress-text">처리 중...</p>
                    </div>

                    <div id="uw-csv-result" style="display: none; margin-top: 20px;"></div>
                </div><!-- /.uw-tab-panel #tab-bulk -->
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * 개별 게시판 관리 페이지 렌더링
     */
    public function render_board_manager_page()
    {
        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
        $slug = str_replace('uw-board-', '', $page);

        // column 보드는 uw_board CPT가 아닌 별도 'column' CPT를 사용 — 표준 WP-admin 리스트로 위임
        if ($slug === 'column' && post_type_exists('column')) {
            wp_safe_redirect(admin_url('edit.php?post_type=column'));
            exit;
        }

        $board = $this->get_board_settings($slug);

        if (!$board) {
            echo '<div class="wrap"><h1>게시판을 찾을 수 없습니다.</h1></div>';
            return;
        }

        $view = isset($_GET['view']) ? sanitize_key($_GET['view']) : 'list';
        $post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;

        ?>
        <div class="wrap uw-board-admin uw-board-manager" data-board-slug="<?php echo esc_attr($slug); ?>">
            <?php
            switch ($view) {
                case 'single':
                    $this->render_board_single($slug, $board, $post_id);
                    break;
                case 'write':
                case 'edit':
                    $this->render_board_editor($slug, $board, $post_id);
                    break;
                default:
                    $this->render_board_list($slug, $board);
                    break;
            }
            ?>
        </div>
        <?php
    }

    /**
     * 게시판 리스트 뷰 렌더링
     */
    private function render_board_list($slug, $board)
    {
        $paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $search_type = isset($_GET['search_type']) ? sanitize_key($_GET['search_type']) : 'title';

        $per_page = $board['per_page'] ?? 10;

        $args = array(
            'post_type' => 'uw_board',
            'posts_per_page' => $per_page,
            'paged' => $paged,
            'post_status' => 'publish',
            'tax_query' => array(
                array(
                    'taxonomy' => 'uw_board_type',
                    'field' => 'slug',
                    'terms' => $slug,
                ),
            ),
            'orderby' => 'date',
            'order' => 'DESC',
        );

        // 검색
        // 검색
        if ($search) {
            if ($search_type === 'title') {
                add_filter('posts_search', array($this, 'filter_search_title'), 10, 2);
                $args['s'] = $search;
            } elseif ($search_type === 'content') {
                add_filter('posts_search', array($this, 'filter_search_content'), 10, 2);
                $args['s'] = $search;
            } elseif ($search_type === 'author') {
                $user = get_user_by('login', $search) ?: get_user_by('slug', $search) ?: get_user_by('nicename', $search);
                if ($user) {
                    $args['author'] = $user->ID;
                } else {
                    // 비회원 등 검색 불가 시 결과 없음 처리
                    $args['post__in'] = array(0);
                }
            } else {
                $args['s'] = $search;
            }
        }

        $query = new WP_Query($args);

        // 필터 해제
        if ($search) {
            remove_filter('posts_search', array($this, 'filter_search_title'), 10);
            remove_filter('posts_search', array($this, 'filter_search_content'), 10);
        }
        $total = $query->found_posts;
        $total_pages = $query->max_num_pages;

        ?>
        <h1><?php echo esc_html($board['name']); ?> <span class="uw-total-count">(Total: <?php echo $total; ?>)</span></h1>

        <div class="uw-board-list-view">
            <table class="wp-list-table widefat fixed striped uw-board-table">
                <thead>
                    <tr>
                        <th class="column-num" style="width: 60px;">번호</th>
                        <th class="column-title">제목</th>
                        <th class="column-author" style="width: 100px;">작성자</th>
                        <th class="column-date" style="width: 100px;">등록일</th>
                        <th class="column-views" style="width: 60px;">조회</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $num = $total - (($paged - 1) * $per_page);
                    while ($query->have_posts()):
                        $query->the_post();
                        $post_id = get_the_ID();
                        $is_pinned = get_post_meta($post_id, '_uw_is_pinned', true);
                        $views = get_post_meta($post_id, '_uw_views', true) ?: 0;
                        $is_new = (time() - get_the_time('U')) < 86400; // 24시간
                        $single_url = admin_url('admin.php?page=uw-board-' . $slug . '&view=single&post_id=' . $post_id);
                        // 실제 첨부파일만 체크 (썸네일 제외)
                        $attachments = get_post_meta($post_id, '_uw_attachments', true);
                        $has_attachment = !empty($attachments);
                        ?>
                        <tr class="<?php echo $is_pinned ? 'uw-pinned-row' : ''; ?>">
                            <td class="column-num">
                                <?php echo $is_pinned ? '<span class="uw-notice-badge">공지</span>' : $num--; ?>
                            </td>
                            <td class="column-title">
                                <a href="<?php echo esc_url($single_url); ?>">
                                    <?php the_title(); ?>
                                </a>
                                <?php if ($is_new): ?>
                                    <span class="uw-new-badge">N</span>
                                <?php endif; ?>
                                <?php if ($has_attachment): ?>
                                    <i class="xi-attachment uw-has-attachment" title="첨부파일"></i>
                                <?php endif; ?>
                            </td>
                            <td class="column-author"><?php echo UW_Board_CPT::get_author_display_name($post_id); ?></td>
                            <td class="column-date"><?php echo get_the_date('Y.m.d'); ?></td>
                            <td class="column-views"><?php echo number_format($views); ?></td>
                        </tr>
                    <?php endwhile;
                    wp_reset_postdata(); ?>

                    <?php if (!$query->have_posts()): ?>
                        <tr>
                            <td colspan="5" class="uw-no-posts">등록된 글이 없습니다.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- 검색바 & 페이지네이션 -->
            <div class="uw-board-footer">
                <form class="uw-search-form" method="get">
                    <input type="hidden" name="page" value="uw-board-<?php echo esc_attr($slug); ?>">
                    <select name="search_type">
                        <option value="title" <?php selected($search_type, 'title'); ?>>제목</option>
                        <option value="content" <?php selected($search_type, 'content'); ?>>내용</option>
                        <option value="author" <?php selected($search_type, 'author'); ?>>작성자</option>
                    </select>
                    <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="검색어 입력">
                    <button type="submit" class="button">검색</button>
                </form>

                <div class="uw-pagination">
                    <?php
                    $base_url = admin_url('admin.php?page=uw-board-' . $slug);
                    if ($total_pages > 1):
                        if ($paged > 1):
                            echo '<a href="' . esc_url($base_url . '&paged=1') . '" class="button">«</a>';
                            echo '<a href="' . esc_url($base_url . '&paged=' . ($paged - 1)) . '" class="button">‹</a>';
                        endif;

                        for ($i = max(1, $paged - 2); $i <= min($total_pages, $paged + 2); $i++):
                            $class = ($i === $paged) ? 'button button-primary' : 'button';
                            echo '<a href="' . esc_url($base_url . '&paged=' . $i) . '" class="' . $class . '">' . $i . '</a>';
                        endfor;

                        if ($paged < $total_pages):
                            echo '<a href="' . esc_url($base_url . '&paged=' . ($paged + 1)) . '" class="button">›</a>';
                            echo '<a href="' . esc_url($base_url . '&paged=' . $total_pages) . '" class="button">»</a>';
                        endif;
                    endif;
                    ?>
                </div>

                <a href="<?php echo admin_url('admin.php?page=uw-board-' . $slug . '&view=write'); ?>"
                    class="button button-primary uw-write-btn">글쓰기</a>
            </div>
        </div>
        <?php
    }

    /**
     * 게시판 싱글(상세) 뷰 렌더링 - KBoard 스타일
     */
    private function render_board_single($slug, $board, $post_id)
    {
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'uw_board') {
            echo '<div class="notice notice-error"><p>게시글을 찾을 수 없습니다.</p></div>';
            return;
        }

        // 조회수 증가
        $views = (int) get_post_meta($post_id, '_uw_views', true);
        update_post_meta($post_id, '_uw_views', $views + 1);

        // 첨부파일
        $attachments = get_post_meta($post_id, '_uw_attachments', true) ?: array();

        // 이전/다음 글 가져오기
        $prev_post = $this->get_adjacent_board_post($post_id, $slug, 'prev');
        $next_post = $this->get_adjacent_board_post($post_id, $slug, 'next');

        // URL
        $list_url = admin_url('admin.php?page=uw-board-' . $slug);
        $edit_url = admin_url('admin.php?page=uw-board-' . $slug . '&view=edit&post_id=' . $post_id);

        ?>
        <div class="uw-board-single-view">
            <!-- 제목 영역 -->
            <div class="uw-single-header">
                <h1 class="uw-single-title"><?php echo esc_html($post->post_title); ?></h1>
                <div class="uw-single-meta">
                    <span class="meta-item">
                        <strong>작성자</strong> <?php echo UW_Board_CPT::get_author_display_name($post); ?>
                    </span>
                    <span class="meta-item">
                        <strong>작성일</strong> <?php echo get_the_date('Y-m-d H:i', $post_id); ?>
                    </span>
                    <span class="meta-item">
                        <strong>조회</strong> <?php echo number_format($views + 1); ?>
                    </span>
                </div>
            </div>

            <!-- 본문 영역 -->
            <div class="uw-single-content">
                <?php
                // 본문 이미지 자동 리사이징을 위한 필터
                $content = $post->post_content;
                $content = preg_replace('/<img([^>]+)>/i', '<img$1 style="max-width:100%;height:auto;">', $content);
                echo wp_kses_post(wpautop($content));
                ?>
            </div>

            <!-- 인쇄 버튼 -->
            <div class="uw-single-print">
                <button type="button" class="button uw-print-btn" onclick="window.print();">
                    <span class="dashicons dashicons-printer"></span> 인쇄
                </button>
            </div>

            <!-- 첨부파일 섹션 -->
            <?php if (!empty($attachments)): ?>
                <div class="uw-single-attachments">
                    <h3>첨부파일</h3>
                    <ul class="attachment-list">
                        <?php foreach ($attachments as $attachment_id):
                            $file_path = get_attached_file($attachment_id);
                            $file_name = basename($file_path);
                            $file_url = wp_get_attachment_url($attachment_id);
                            ?>
                            <li>
                                <a href="<?php echo esc_url($file_url); ?>" download>
                                    <span class="dashicons dashicons-media-default"></span>
                                    <?php echo esc_html($file_name); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- 이전/다음 글 네비게이션 -->
            <div class="uw-single-navigation">
                <?php if ($next_post): ?>
                    <?php $next_url = admin_url('admin.php?page=uw-board-' . $slug . '&view=single&post_id=' . $next_post->ID); ?>
                    <div class="nav-next">
                        <span class="nav-label">다음 글</span>
                        <a href="<?php echo esc_url($next_url); ?>"><?php echo esc_html($next_post->post_title); ?></a>
                    </div>
                <?php else: ?>
                    <div class="nav-next nav-empty">
                        <span class="nav-label">다음 글</span>
                        <span class="no-post">다음 글이 없습니다.</span>
                    </div>
                <?php endif; ?>

                <?php if ($prev_post): ?>
                    <?php $prev_url = admin_url('admin.php?page=uw-board-' . $slug . '&view=single&post_id=' . $prev_post->ID); ?>
                    <div class="nav-prev">
                        <span class="nav-label">이전 글</span>
                        <a href="<?php echo esc_url($prev_url); ?>"><?php echo esc_html($prev_post->post_title); ?></a>
                    </div>
                <?php else: ?>
                    <div class="nav-prev nav-empty">
                        <span class="nav-label">이전 글</span>
                        <span class="no-post">이전 글이 없습니다.</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 액션 버튼 -->
            <div class="uw-single-actions">
                <div class="actions-left">
                    <a href="<?php echo esc_url($list_url); ?>" class="button button-secondary">목록보기</a>
                </div>
                <div class="actions-right">
                    <a href="<?php echo esc_url($edit_url); ?>" class="button button-primary">글수정</a>
                    <button type="button" class="button uw-delete-post" data-post-id="<?php echo $post_id; ?>"
                        data-board-slug="<?php echo esc_attr($slug); ?>">글삭제</button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * 이전/다음 게시글 가져오기 (ID 기반 정렬)
     */
    private function get_adjacent_board_post($post_id, $slug, $direction = 'prev')
    {
        global $wpdb;

        $current_post = get_post($post_id);
        if (!$current_post) {
            return null;
        }

        // 게시판 taxonomy term ID 가져오기
        $term = get_term_by('slug', $slug, 'uw_board_type');
        if (!$term) {
            return null;
        }

        // 이전 글: 현재보다 날짜가 이전인 글 중 가장 최근 것
        // 다음 글: 현재보다 날짜가 이후인 글 중 가장 오래된 것
        if ($direction === 'prev') {
            // 이전 글 = 더 오래된 글 (날짜 DESC, 같은 날짜면 ID ASC)
            $compare = '<';
            $order = 'DESC';
        } else {
            // 다음 글 = 더 최근 글 (날짜 ASC, 같은 날짜면 ID DESC)
            $compare = '>';
            $order = 'ASC';
        }

        $query = $wpdb->prepare("
            SELECT p.ID, p.post_title
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
            WHERE p.post_type = 'uw_board'
            AND p.post_status = 'publish'
            AND tr.term_taxonomy_id = %d
            AND p.post_date {$compare} %s
            ORDER BY p.post_date {$order}, p.ID {$order}
            LIMIT 1
        ", $term->term_taxonomy_id, $current_post->post_date);

        return $wpdb->get_row($query);
    }

    /**
     * 게시판 에디터 뷰 렌더링
     */
    private function render_board_editor($slug, $board, $post_id = 0)
    {
        $post = $post_id ? get_post($post_id) : null;
        $is_edit = !empty($post);

        $title = $post ? $post->post_title : '';
        $content = $post ? $post->post_content : '';
        $is_pinned = $post ? get_post_meta($post_id, '_uw_is_pinned', true) : false;
        $attachments = $post ? get_post_meta($post_id, '_uw_attachments', true) : array();
        $current_category = $post ? get_post_meta($post_id, '_uw_category', true) : '';
        
        // 카테고리 설정 가져오기
        $categories = $board['categories'] ?? array();
        $category_required = $board['category_required'] ?? false;

        ?>
        <h1><?php echo $is_edit ? '글 수정' : '글쓰기'; ?> - <?php echo esc_html($board['name']); ?></h1>

        <?php if ($is_edit): ?>
        <div class="uw-editor-meta-info" style="margin-bottom: 20px; padding: 15px; background: #f5f5f5; border-radius: 4px;">
            <span style="margin-right: 20px;"><strong>작성자:</strong> <?php echo UW_Board_CPT::get_author_display_name($post); ?></span>
            <span style="margin-right: 20px;"><strong>작성일:</strong> <?php echo get_the_date('Y-m-d H:i', $post); ?></span>
            <span><strong>조회수:</strong> <?php echo number_format(get_post_meta($post_id, '_uw_views', true) ?: 0); ?></span>
        </div>
        <?php endif; ?>

        <form id="uw-board-editor-form" class="uw-board-editor">
            <input type="hidden" name="board_slug" value="<?php echo esc_attr($slug); ?>">
            <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">

            <div class="uw-editor-field">
                <label for="post_title">제목 *</label>
                <input type="text" id="post_title" name="title" value="<?php echo esc_attr($title); ?>" required>

                <label class="uw-checkbox-inline">
                    <input type="checkbox" name="is_pinned" value="1" <?php checked($is_pinned); ?>>
                    상단 공지사항으로 지정
                </label>
            </div>

            <?php if (!empty($categories)): ?>
            <div class="uw-editor-field">
                <label for="post_category">카테고리<?php echo $category_required ? ' *' : ''; ?></label>
                <select id="post_category" name="category" <?php echo $category_required ? 'required' : ''; ?>>
                    <option value="">카테고리를 선택하세요</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo esc_attr($cat); ?>" <?php selected($current_category, $cat); ?>>
                            <?php echo esc_html($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="uw-editor-field">
                <label>본문</label>
                <textarea id="uw-summernote" name="content"><?php echo esc_textarea($content); ?></textarea>
            </div>

            <div class="uw-editor-field">
                <label>대표 이미지</label>
                <div class="uw-thumbnail-upload">
                    <?php
                    $thumb_id = get_post_thumbnail_id($post_id);
                    $thumb_url = $thumb_id ? get_the_post_thumbnail_url($post_id, 'medium') : '';
                    ?>
                    <input type="hidden" name="thumbnail_id" id="thumbnail_id" value="<?php echo esc_attr($thumb_id); ?>">
                    <div class="uw-thumbnail-preview" id="uw-thumbnail-preview"
                        style="<?php echo $thumb_url ? '' : 'display:none;'; ?>">
                        <img src="<?php echo esc_url($thumb_url); ?>" alt="대표 이미지 미리보기" id="uw-thumbnail-img">
                        <button type="button" class="uw-remove-thumbnail" title="이미지 삭제">&times;</button>
                    </div>
                    <button type="button" class="button uw-select-thumbnail"
                        style="<?php echo $thumb_url ? 'display:none;' : ''; ?>">이미지 선택</button>
                </div>
            </div>

            <div class="uw-editor-field">
                <label>첨부파일 (최대 3개)</label>
                <div class="uw-attachments-upload">
                    <?php for ($i = 0; $i < 3; $i++):
                        $att_id = isset($attachments[$i]) ? $attachments[$i] : '';
                        $att_name = $att_id ? basename(get_attached_file($att_id)) : '';
                        ?>
                        <div class="uw-attachment-slot">
                            <input type="hidden" name="attachments[]" value="<?php echo esc_attr($att_id); ?>">
                            <button type="button" class="button uw-select-file">파일 선택</button>
                            <span class="uw-file-name"><?php echo $att_name ?: '선택된 파일 없음'; ?></span>
                            <?php if ($att_id): ?>
                                <button type="button" class="button uw-remove-file">삭제</button>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="uw-editor-actions">
                <a href="<?php echo admin_url('admin.php?page=uw-board-' . $slug); ?>" class="button">목록으로</a>
                <button type="submit" class="button button-primary">
                    <?php echo $is_edit ? '저장하기' : '작성하기'; ?>
                </button>
                <?php if ($is_edit): ?>
                    <button type="button" class="button uw-delete-post" data-post-id="<?php echo $post_id; ?>">
                        삭제
                    </button>
                <?php endif; ?>
            </div>
        </form>
        <?php
    }

    /**
     * 게시판 글 개수 조회
     */
    private function get_board_post_count($slug)
    {
        $args = array(
            'post_type' => 'uw_board',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'tax_query' => array(
                array(
                    'taxonomy' => 'uw_board_type',
                    'field' => 'slug',
                    'terms' => $slug,
                ),
            ),
        );
        $query = new WP_Query($args);
        return $query->found_posts;
    }

    /**
     * AJAX: 게시판 설정 저장
     */
    public function ajax_save_settings()
    {
        check_ajax_referer('uw_board_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('권한이 없습니다.');
        }

        $slug = sanitize_key($_POST['slug']);
        
        // 카테고리 처리
        $categories = array();
        if (!empty($_POST['categories']) && is_array($_POST['categories'])) {
            foreach ($_POST['categories'] as $cat) {
                $cat = sanitize_text_field(trim($cat));
                if (!empty($cat)) {
                    $categories[] = $cat;
                }
            }
        }
        
        $settings = array(
            'name' => sanitize_text_field($_POST['name']),
            'per_page' => absint($_POST['per_page']),
            'read_permission' => sanitize_key($_POST['read_permission']),
            'write_permission' => sanitize_key($_POST['write_permission']),
            'require_privacy' => !empty($_POST['require_privacy']),
            'skin' => sanitize_key($_POST['skin']),
            'latest_page' => esc_url_raw($_POST['latest_page'] ?? ''),
            'latest_limit' => absint($_POST['latest_limit'] ?? 5),
            'categories' => $categories,
            'category_required' => !empty($_POST['category_required']),
        );

        $this->save_board_settings($slug, $settings);

        wp_send_json_success(array(
            'message' => '저장되었습니다.',
            'redirect' => admin_url('admin.php?page=uw-board'),
        ));
    }

    /**
     * AJAX: 게시판 삭제
     */
    public function ajax_delete_board()
    {
        check_ajax_referer('uw_board_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('권한이 없습니다.');
        }

        $slug = sanitize_key($_POST['slug']);
        $boards = $this->get_all_boards();

        if (isset($boards[$slug])) {
            unset($boards[$slug]);
            update_option(self::OPTION_KEY, $boards);

            // 관련 글 삭제 (선택적)
            // TODO: 글 삭제 옵션 추가?

            wp_send_json_success(array('message' => '삭제되었습니다.'));
        }

        wp_send_json_error('게시판을 찾을 수 없습니다.');
    }

    /**
     * AJAX: 글 저장
     */
    public function ajax_save_post()
    {
        check_ajax_referer('uw_board_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('권한이 없습니다.');
        }

        $board_slug = sanitize_key($_POST['board_slug']);
        $post_id = absint($_POST['post_id']);

        $post_data = array(
            'post_title' => sanitize_text_field($_POST['title']),
            'post_content' => wp_kses_post($_POST['content']),
            'post_type' => 'uw_board',
            'post_status' => 'publish',
        );

        if ($post_id) {
            $post_data['ID'] = $post_id;
            wp_update_post($post_data);
        } else {
            $post_id = wp_insert_post($post_data);
            wp_set_object_terms($post_id, $board_slug, 'uw_board_type');
        }

        // Meta 저장
        update_post_meta($post_id, '_uw_is_pinned', !empty($_POST['is_pinned']) ? '1' : '');
        
        // 카테고리 저장
        if (isset($_POST['category'])) {
            update_post_meta($post_id, '_uw_category', sanitize_text_field($_POST['category']));
        }

        // 썸네일
        if (!empty($_POST['thumbnail_id'])) {
            set_post_thumbnail($post_id, absint($_POST['thumbnail_id']));
        } else {
            delete_post_thumbnail($post_id);
        }

        // 첨부파일
        $attachments = isset($_POST['attachments']) ? array_filter(array_map('absint', $_POST['attachments'])) : array();
        update_post_meta($post_id, '_uw_attachments', $attachments);

        wp_send_json_success(array(
            'message' => '저장되었습니다.',
            'redirect' => admin_url('admin.php?page=uw-board-' . $board_slug),
        ));
    }

    /**
     * AJAX: 글 삭제
     */
    public function ajax_delete_post()
    {
        check_ajax_referer('uw_board_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('권한이 없습니다.');
        }

        $post_id = absint($_POST['post_id']);
        $board_slug = sanitize_key($_POST['board_slug']);

        if ($post_id && get_post_type($post_id) === 'uw_board') {
            wp_delete_post($post_id, true);
            wp_send_json_success(array(
                'message' => '삭제되었습니다.',
                'redirect' => admin_url('admin.php?page=uw-board-' . $board_slug),
            ));
        }

        wp_send_json_error('글을 찾을 수 없습니다.');
    }

    /**
     * AJAX: 이미지 업로드 (Summernote 에디터용)
     */
    public function ajax_upload_image()
    {
        check_ajax_referer('uw_board_admin_nonce', 'nonce');

        if (!current_user_can('upload_files')) {
            wp_send_json_error('업로드 권한이 없습니다.');
        }

        if (empty($_FILES['file'])) {
            wp_send_json_error('파일이 없습니다.');
        }

        // WordPress 미디어 라이브러리에 업로드
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $attachment_id = media_handle_upload('file', 0);

        if (is_wp_error($attachment_id)) {
            wp_send_json_error($attachment_id->get_error_message());
        }

        $url = wp_get_attachment_url($attachment_id);

        wp_send_json_success(array(
            'url' => $url,
            'id' => $attachment_id,
        ));
    }


    /**
     * 제목만 검색 필터
     */
    public function filter_search_title($search, $wp_query)
    {
        global $wpdb;
        if (empty($search))
            return $search;
        $q = $wp_query->query_vars;
        $n = !empty($q['exact']) ? '' : '%';
        $search = $searchand = '';
        foreach ((array) $q['search_terms'] as $term) {
            $term = esc_sql($wpdb->esc_like($term));
            $search .= "{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
            $searchand = ' AND ';
        }
        if (!empty($search)) {
            $search = " AND ({$search}) ";
        }
        return $search;
    }

    /**
     * 내용만 검색 필터
     */
    public function filter_search_content($search, $wp_query)
    {
        global $wpdb;
        if (empty($search))
            return $search;
        $q = $wp_query->query_vars;
        $n = !empty($q['exact']) ? '' : '%';
        $search = $searchand = '';
        foreach ((array) $q['search_terms'] as $term) {
            $term = esc_sql($wpdb->esc_like($term));
            $search .= "{$searchand}($wpdb->posts.post_content LIKE '{$n}{$term}{$n}')";
            $searchand = ' AND ';
        }
        if (!empty($search)) {
            $search = " AND ({$search}) ";
        }
        return $search;
    }

    /**
     * 일괄 동작: 모든 게시글 비우기
     */
    public function ajax_bulk_empty_posts()
    {
        check_ajax_referer('uw_board_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('권한이 없습니다.');
        }

        // board_slugs 처리 - 배열 또는 문자열 모두 처리
        $raw_slugs = isset($_POST['board_slugs']) ? $_POST['board_slugs'] : array();

        if (!is_array($raw_slugs)) {
            $raw_slugs = array($raw_slugs);
        }

        $board_slugs = array_map('sanitize_key', $raw_slugs);
        $board_slugs = array_filter($board_slugs); // 빈 값 제거

        if (empty($board_slugs)) {
            wp_send_json_error('게시판을 선택해주세요.');
        }

        $deleted_count = 0;

        foreach ($board_slugs as $slug) {
            $args = array(
                'post_type' => 'uw_board',
                'posts_per_page' => -1,
                'post_status' => 'any',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'uw_board_type',
                        'field' => 'slug',
                        'terms' => $slug,
                    ),
                ),
            );

            $posts = get_posts($args);

            foreach ($posts as $post) {
                wp_delete_post($post->ID, true);
                $deleted_count++;
            }
        }

        wp_send_json_success(array(
            'message' => $deleted_count . '개의 게시글이 삭제되었습니다.',
        ));
    }

    /**
     * 일괄 동작: 게시판 영구 삭제
     */
    public function ajax_bulk_delete_boards()
    {
        check_ajax_referer('uw_board_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('권한이 없습니다.');
        }

        // board_slugs 처리 - 배열 또는 문자열 모두 처리
        $raw_slugs = isset($_POST['board_slugs']) ? $_POST['board_slugs'] : array();

        if (!is_array($raw_slugs)) {
            $raw_slugs = array($raw_slugs);
        }

        $board_slugs = array_map('sanitize_key', $raw_slugs);
        $board_slugs = array_filter($board_slugs); // 빈 값 제거

        if (empty($board_slugs)) {
            wp_send_json_error('게시판을 선택해주세요.');
        }

        $boards = $this->get_all_boards();

        foreach ($board_slugs as $slug) {
            // 해당 게시판의 모든 글 삭제
            $args = array(
                'post_type' => 'uw_board',
                'posts_per_page' => -1,
                'post_status' => 'any',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'uw_board_type',
                        'field' => 'slug',
                        'terms' => $slug,
                    ),
                ),
            );

            $posts = get_posts($args);

            foreach ($posts as $post) {
                wp_delete_post($post->ID, true);
            }

            // 택소노미 term 삭제
            $term = get_term_by('slug', $slug, 'uw_board_type');
            if ($term && !is_wp_error($term)) {
                wp_delete_term($term->term_id, 'uw_board_type');
            }

            // 게시판 설정 삭제
            if (isset($boards[$slug])) {
                unset($boards[$slug]);
            }
        }

        update_option(self::OPTION_KEY, $boards);

        wp_send_json_success(array(
            'message' => count($board_slugs) . '개의 게시판이 삭제되었습니다.',
        ));
    }

    /**
     * CSV 내보내기 AJAX 핸들러
     */
    public function ajax_export_csv()
    {
        check_ajax_referer('uw_board_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('권한이 없습니다.');
        }

        $board_slug = isset($_POST['board_slug']) ? sanitize_key($_POST['board_slug']) : '';

        if (empty($board_slug)) {
            wp_send_json_error('게시판 정보가 없습니다.');
        }

        // 게시글 조회
        $args = array(
            'post_type' => 'uw_board',
            'post_status' => array('publish', 'draft', 'trash'),
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'uw_board_type',
                    'field' => 'slug',
                    'terms' => $board_slug,
                ),
            ),
            'orderby' => 'date',
            'order' => 'DESC',
        );

        $query = new WP_Query($args);
        $csv_data = array();

        // 헤더 행
        $csv_data[] = array('ID', '제목', '내용', '작성자', '작성일', '조회수', '상단고정');

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                $csv_data[] = array(
                    $post_id,
                    get_the_title(),
                    get_the_content(),
                    UW_Board_CPT::get_author_display_name($post_id),
                    get_the_date('Y-m-d H:i:s'),
                    get_post_meta($post_id, '_uw_views', true) ?: 0,
                    get_post_meta($post_id, '_uw_is_pinned', true) ? 1 : 0,
                );
            }
            wp_reset_postdata();
        }

        wp_send_json_success(array(
            'filename' => $board_slug . '_' . date('Y-m-d') . '.csv',
            'data' => $csv_data,
        ));
    }

    /**
     * CSV 가져오기 AJAX 핸들러
     */
    public function ajax_import_csv()
    {
        check_ajax_referer('uw_board_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('권한이 없습니다.');
        }

        $board_slug = isset($_POST['board_slug']) ? sanitize_key($_POST['board_slug']) : '';
        $mode = isset($_POST['mode']) ? sanitize_key($_POST['mode']) : 'add';

        if (empty($board_slug)) {
            wp_send_json_error('게시판 정보가 없습니다.');
        }

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error('파일 업로드에 실패했습니다.');
        }

        $file = $_FILES['csv_file'];

        // CSV 파일 확인
        if (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'csv') {
            wp_send_json_error('CSV 파일만 업로드 가능합니다.');
        }

        // 기존 게시글 삭제 모드
        if ($mode === 'replace') {
            $existing_posts = get_posts(array(
                'post_type' => 'uw_board',
                'post_status' => array('publish', 'draft', 'trash'),
                'posts_per_page' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'uw_board_type',
                        'field' => 'slug',
                        'terms' => $board_slug,
                    ),
                ),
                'fields' => 'ids',
            ));

            foreach ($existing_posts as $post_id) {
                wp_delete_post($post_id, true);
            }
        }

        // CSV 파일 파싱
        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            wp_send_json_error('파일을 읽을 수 없습니다.');
        }

        // BOM 제거
        $bom = fread($handle, 3);
        if ($bom !== "\xef\xbb\xbf") {
            rewind($handle);
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            wp_send_json_error('CSV 파일 형식이 올바르지 않습니다.');
        }

        $imported = 0;
        $errors = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) {
                $errors++;
                continue;
            }

            // 필드 매핑
            $title = isset($row[1]) ? sanitize_text_field($row[1]) : '';
            $content = isset($row[2]) ? wp_kses_post($row[2]) : '';
            $author = isset($row[3]) ? sanitize_text_field($row[3]) : '관리자';
            $date = isset($row[4]) ? sanitize_text_field($row[4]) : current_time('mysql');
            $views = isset($row[5]) ? absint($row[5]) : 0;
            $pinned = isset($row[6]) ? absint($row[6]) : 0;

            if (empty($title)) {
                $errors++;
                continue;
            }

            // 게시글 생성
            $post_data = array(
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => 'publish',
                'post_type' => 'uw_board',
                'post_date' => $date,
            );

            $post_id = wp_insert_post($post_data);

            if ($post_id && !is_wp_error($post_id)) {
                // 택소노미 설정
                wp_set_object_terms($post_id, $board_slug, 'uw_board_type');

                // 메타 데이터 설정
                update_post_meta($post_id, '_uw_board_views', $views);
                update_post_meta($post_id, '_uw_board_pinned', $pinned ? true : false);
                update_post_meta($post_id, '_uw_board_guest_author', $author);

                $imported++;
            } else {
                $errors++;
            }
        }

        fclose($handle);

        wp_send_json_success(array(
            'message' => sprintf('%d개의 게시글이 등록되었습니다.', $imported),
            'imported' => $imported,
            'errors' => $errors,
        ));
    }
}

// Initialize
UW_Board_Admin::get_instance();
