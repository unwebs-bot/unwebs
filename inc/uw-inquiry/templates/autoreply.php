<?php
/**
 * 자동회신 메일 HTML 템플릿
 *
 * 변수: $entry_data (array), $fields (array), $form (WP_Post),
 *       $contact_email (string), $kakao_url (string), $site_url (string), $site_name (string)
 *
 * 메일 호환성을 위해 인라인 CSS + table 기반 레이아웃.
 */
if (!defined('ABSPATH')) exit;

$customer_name = !empty($entry_data['field_name']) ? esc_html(strtok($entry_data['field_name'], '/')) : '고객';
$primary       = '#174eff';
$bg            = '#f5f7fb';
$text          = '#222222';
$sub           = '#888888';
$border        = '#e5e8ee';

// 본문에 표기할 필드 (라벨/값) — 빈 값/첨부는 별도 처리
$display_rows = array();
$attachment_summary = '';

if (is_array($fields)) {
    foreach ($fields as $field) {
        if (empty($field['enabled']) || !isset($entry_data[$field['id']])) continue;
        $value = $entry_data[$field['id']];

        if ($field['type'] === 'file') {
            if (is_array($value) && isset($value[0]) && is_array($value[0])) {
                $names = array();
                foreach ($value as $item) if (!empty($item['name'])) $names[] = $item['name'];
                if (!empty($names)) {
                    $attachment_summary = count($names) . '개 (' . implode(', ', $names) . ')';
                }
            } elseif (is_array($value) && !empty($value['name'])) {
                $attachment_summary = $value['name'];
            }
            continue;
        }

        if ($value === '' || $value === null) continue;

        $display_rows[] = array(
            'label' => $field['label'],
            'value' => $value,
            'is_textarea' => ($field['type'] === 'textarea'),
        );
    }
}
?><!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>문의 접수가 완료되었습니다</title>
</head>
<body style="margin:0;padding:0;background:<?php echo $bg; ?>;font-family:-apple-system,BlinkMacSystemFont,'Apple SD Gothic Neo','Segoe UI',Roboto,sans-serif;color:<?php echo $text; ?>;-webkit-font-smoothing:antialiased;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:<?php echo $bg; ?>;padding:40px 16px;">
  <tr>
    <td align="center">

      <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.04);">

        <!-- 헤더 -->
        <tr>
          <td style="background:<?php echo $primary; ?>;padding:28px 32px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="font-size:18px;font-weight:700;color:#ffffff;letter-spacing:-0.01em;">
                  <?php echo esc_html($site_name); ?>
                </td>
                <td align="right" style="font-size:12px;color:rgba(255,255,255,0.7);">
                  문의 접수 완료
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- 본문: 인사 + 핵심 메시지 -->
        <tr>
          <td style="padding:36px 32px 24px;">
            <h1 style="margin:0 0 16px;font-size:24px;font-weight:800;line-height:1.3;letter-spacing:-0.02em;color:<?php echo $text; ?>;">
              문의 접수가 완료되었습니다
            </h1>
            <p style="margin:0 0 20px;font-size:15px;line-height:1.7;color:<?php echo $text; ?>;">
              <?php echo $customer_name; ?> 님, 안녕하세요.
            </p>
            <p style="margin:0;font-size:15px;line-height:1.7;color:<?php echo $text; ?>;">
              정상적으로 접수되었습니다.<br>
              담당자가 <strong style="color:<?php echo $primary; ?>;">영업일 1일 이내</strong> 확인하고 연락드리겠습니다.
            </p>
          </td>
        </tr>

        <!-- 작성하신 내용 박스 -->
        <tr>
          <td style="padding:0 32px 28px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:<?php echo $bg; ?>;border-radius:10px;">
              <tr>
                <td style="padding:22px 24px;">
                  <p style="margin:0 0 16px;font-size:13px;font-weight:700;color:<?php echo $sub; ?>;letter-spacing:0.04em;text-transform:uppercase;">
                    작성하신 내용
                  </p>

                  <?php foreach ($display_rows as $row) : ?>
                  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:14px;">
                    <tr>
                      <td style="font-size:12px;font-weight:600;color:<?php echo $sub; ?>;padding-bottom:4px;">
                        <?php echo esc_html($row['label']); ?>
                      </td>
                    </tr>
                    <tr>
                      <td style="font-size:14px;line-height:<?php echo $row['is_textarea'] ? '1.7' : '1.5'; ?>;color:<?php echo $text; ?>;word-break:break-word;<?php echo $row['is_textarea'] ? 'white-space:pre-wrap;' : ''; ?>">
                        <?php echo nl2br(esc_html($row['value'])); ?>
                      </td>
                    </tr>
                  </table>
                  <?php endforeach; ?>

                  <?php if (!empty($attachment_summary)) : ?>
                  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:0;border-top:1px solid <?php echo $border; ?>;padding-top:14px;">
                    <tr>
                      <td style="font-size:12px;font-weight:600;color:<?php echo $sub; ?>;padding:14px 0 4px;">
                        첨부파일
                      </td>
                    </tr>
                    <tr>
                      <td style="font-size:14px;color:<?php echo $text; ?>;word-break:break-word;">
                        <?php echo esc_html($attachment_summary); ?>
                      </td>
                    </tr>
                  </table>
                  <?php endif; ?>

                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- CTA: 답장은 본 메일에 회신 / 빠른 상담은 카카오 채널 -->
        <tr>
          <td style="padding:0 32px 32px;">
            <p style="margin:0 0 16px;font-size:14px;line-height:1.6;color:<?php echo $sub; ?>;">
              추가로 전달하실 내용은 <strong style="color:<?php echo $text; ?>;">본 메일에 그대로 회신</strong>해주세요.<?php if (!empty($kakao_url)) : ?><br>빠른 상담을 원하시면 카카오톡 채널로 연락주세요.<?php endif; ?>
            </p>

            <?php if (!empty($kakao_url)) : ?>
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td>
                  <a href="<?php echo esc_url($kakao_url); ?>" target="_blank" rel="noopener"
                     style="display:inline-block;padding:13px 22px;background:#FEE500;color:#191919;text-decoration:none;font-size:14px;font-weight:700;border-radius:8px;letter-spacing:-0.01em;">
                    <span style="display:inline-block;vertical-align:middle;margin-right:6px;">
                      <img src="data:image/svg+xml;base64,<?php
                        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18"><path fill="#191919" d="M9 1.5C4.582 1.5 1 4.34 1 7.84c0 2.27 1.51 4.262 3.781 5.376l-.957 3.498c-.085.31.262.555.534.379l4.205-2.78a10.4 10.4 0 0 0 .437.011c4.418 0 8-2.84 8-6.484S13.418 1.5 9 1.5"/></svg>';
                        echo base64_encode($svg);
                      ?>" width="18" height="18" alt="" style="vertical-align:middle;border:0;">
                    </span><span style="vertical-align:middle;">카카오톡 채널 문의</span>
                  </a>
                </td>
              </tr>
            </table>
            <?php endif; ?>
          </td>
        </tr>

        <!-- 푸터 -->
        <tr>
          <td style="padding:24px 32px;background:#fafbfc;border-top:1px solid <?php echo $border; ?>;">
            <p style="margin:0 0 6px;font-size:13px;font-weight:700;color:<?php echo $text; ?>;">
              <?php echo esc_html($site_name); ?>
            </p>
            <p style="margin:0 0 12px;font-size:12px;line-height:1.6;color:<?php echo $sub; ?>;">
              <a href="<?php echo esc_url($site_url); ?>" style="color:<?php echo $sub; ?>;text-decoration:none;"><?php echo esc_html(preg_replace('#^https?://#', '', $site_url)); ?></a>
            </p>
            <p style="margin:0;font-size:11px;line-height:1.6;color:<?php echo $sub; ?>;">
              본 메일에 회신하시면 담당자에게 직접 전달됩니다.
            </p>
          </td>
        </tr>

      </table>

    </td>
  </tr>
</table>

</body>
</html>
