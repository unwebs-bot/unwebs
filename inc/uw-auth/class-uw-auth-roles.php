<?php
/**
 * UW Auth Roles — 유지보수 고객 역할 등록/관리
 *
 *  - maintenance_pending : 가입 직후 (승인 대기). 로그인 차단.
 *  - maintenance_client  : 승인 완료. 유지보수 신청 가능.
 *
 * 활성화 시 add_role, 비활성화 시 remove_role.
 */

if (!defined('ABSPATH')) exit;

class UW_Auth_Roles {

    const ROLE_PENDING = 'maintenance_pending';
    const ROLE_CLIENT  = 'maintenance_client';
    const CAP_SUBMIT   = 'submit_uw_inquiry';

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('after_switch_theme', array(__CLASS__, 'register_roles'));
        add_action('init', array($this, 'ensure_roles'));
    }

    /**
     * 역할이 없으면 자동 보강 (테마 활성화 시점을 놓친 환경 대비).
     */
    public function ensure_roles() {
        if (!get_role(self::ROLE_PENDING) || !get_role(self::ROLE_CLIENT)) {
            self::register_roles();
        }
    }

    public static function register_roles() {
        add_role(
            self::ROLE_PENDING,
            __('유지보수 대기', 'unwebs'),
            array(
                'read' => false,
            )
        );

        add_role(
            self::ROLE_CLIENT,
            __('유지보수 고객', 'unwebs'),
            array(
                'read'           => true,
                self::CAP_SUBMIT => true,
            )
        );

        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap(self::CAP_SUBMIT);
        }
    }

    public static function unregister_roles() {
        remove_role(self::ROLE_PENDING);
        remove_role(self::ROLE_CLIENT);

        $admin = get_role('administrator');
        if ($admin) {
            $admin->remove_cap(self::CAP_SUBMIT);
        }
    }

    /**
     * 사용자가 승인된 고객인지 (= 신청 가능 권한 보유).
     */
    public static function is_approved_client($user = null) {
        $user = $user ?: wp_get_current_user();
        if (!$user || empty($user->ID)) return false;
        return user_can($user, self::CAP_SUBMIT);
    }

    /**
     * 사용자가 승인 대기 상태인지.
     */
    public static function is_pending($user = null) {
        $user = $user ?: wp_get_current_user();
        if (!$user || empty($user->ID)) return false;
        return in_array(self::ROLE_PENDING, (array) $user->roles, true);
    }
}

UW_Auth_Roles::get_instance();
