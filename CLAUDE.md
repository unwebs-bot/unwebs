# 언웹스리뉴얼 — 프로젝트 지침

> 자체 사이트 전면 재설계. 시작일: 2026-04-18.

## 코딩 지침

**반드시 참조 (매 작업 시 읽고 반영):**
- `~/Library/Mobile Documents/iCloud~md~obsidian/Documents/Obsidian Vault/02-영역/웹팩토리/퍼블리싱/코딩-지침.md` — 파일구조, 네이밍, HTML 패턴, CSS 규칙, 애니메이션, 이미지, 접근성, PHP 보안
- `~/Library/Mobile Documents/iCloud~md~obsidian/Documents/Obsidian Vault/01-프로젝트/260418-언웹스리뉴얼/3-퍼블리싱/SEO-통합지침-코드에이전트용.md` — **SEO 적용 필수 참조**. 메타/구조화데이터/퍼머링크/사이트맵/OG/CWV 등 리뉴얼 작업의 모든 단계에서 SEO 반영
- 글로벌 `~/.claude/CLAUDE.md` — 언웹스 운영 규칙(응답 톤, 기술 스택, 디버깅)

---

## 하네스 설정

글로벌 `~/.claude/settings.json`에 이미 등록된 hooks/agents/permissions 공통 적용.

### hooks
| 타이밍 | 트리거 | 스크립트 | 동작 |
|--------|--------|----------|------|
| PreToolUse | Edit\|Write | `file-guard.sh` | default.css, animate.css, header.js, animate.js 편집 차단 |
| PostToolUse | Edit\|Write | `php-lint.sh` | PHP 저장 후 문법 체크 |
| PostToolUse | Edit\|Write | `css-var-check.sh` | 하드코딩 hex 경고 |

### agents
| 에이전트 | 용도 |
|----------|------|
| `code-reviewer` | 퍼블리싱 코드 검수. `/검수` 스킬에서 호출 |

---

## 프로젝트 개요

- **유형**: 자체 사이트 전면 재설계
- **주 드라이버**: SEO 유입 (언웹스 자체 사이트 타겟)
- **제약**: 리드마그넷 계획 폐기 → 전환 경로 재설계 필요
- **상태**: 기획 단계. 사이트맵 / 요구정의 미확정

## 사이트맵 (확정 2026-04-18)

| # | 페이지 | URL | 템플릿 | 데이터 | 상태 |
|---|---|---|---|---|---|
| 1 | 메인 | `/` | `front-page.php` | 하드코딩 (포트폴리오 섹션 포함) | ⬜ 리팩터 |
| 2 | 홈페이지 제작 (통합) | `/service` | `page-service.php` (Hero + 5섹션 + 플로팅 탭) | 하드코딩 | 🟡 통합 완료(2026-05-12) |
| 2-Hero | └ 반응형 체험 (탭 X, 맨 위) | `/service` | `responsive-demo.php` | 하드코딩 | ✅ |
| 2-a | └ 서비스 안내 (앵커) | `/service#info` | `service-info.php` (placeholder 4카드) | 하드코딩 | 🟡 placeholder |
| 2-b | └ 무료 제공항목 (앵커) | `/service#features` | `service-features.php` | 하드코딩 | ✅ 카피·구조, 아이콘 슬롯 |
| 2-c | └ 제작절차 (앵커) | `/service#process` | `section-schedule.php` + `service-process.php` | 하드코딩 | ✅ |
| 2-d | └ 홈페이지 준비자료 (앵커) | `/service#materials` | `page-service.php` 인라인 | 하드코딩 | ✅ |
| 2-e | └ 자주묻는질문 (앵커) | `/service#faq` | `faq-section.php` (Q1·3·4·9·10·18) | starter_faq_items | ✅ |
| — | 드롭다운 GNB(3개) | `#info` / `#materials` / `#process` | — | — | ✅ |
| — | 플로팅 섹션 탭(5개·PC전용) | — | `service-tabs.php` + `assets/js/service-tabs.js` | — | ✅ |
| — | (구) 준비자료 단독 | `/service-materials` | — | — | ⛔ 301 → `/service#materials` |
| — | (구) 제작절차 단독 | `/service-process` | — | — | ⛔ 301 → `/service#process` |
| — | (구) 서비스안내 별칭 | `/service-intro` | — | — | ⛔ 301 → `/service#info` |
| 3 | 유지보수 | `/maintenance` | `page-maintenance.php` | 허브 (신청 / 비용 안내 2 카드) | 🟡 카드 정리 완료, 비주얼 보강 필요 |
| 3-a | └ 유지보수 신청 | `/maintenance-request` | `page-maintenance-request.php` | 절차·비용표·CTA 분기 | ✅ 완료 (2026-05-15) |
| 3-b | └ 비용 안내 | `/maintenance-pricing` | `page-maintenance-pricing.php` | 건별 + 부가 서비스 비용표 통합 | ⬜ 신규 (구 on-demand·subscription 통합) |
| 3-c | └ 신청 시 유의사항 + 신청 폼 | `/maintenance-request-notice` | `page-maintenance-request-notice.php` | 회원 전용 (maintenance_client). figma 117:398 + uw_inquiry 폼 prefill | ✅ 완료 (2026-05-15) |
| — | (구) 건별 유지보수 단독 | `/maintenance-on-demand` | — | — | ⛔ 폐기 (`/maintenance-pricing`로 통합) |
| — | (구) 정기 유지보수 단독 | `/maintenance-subscription` | — | — | ⛔ 폐기 (`/maintenance-pricing`로 통합) |
| 4 | 포트폴리오 | `/portfolio/` | `archive-uw_gallery.php` + `single-uw_gallery.php` | `uw_gallery` CPT (page 없음, archive가 목록) | ⬜ 신규 |
| 5 | 고객지원 | `/support` | `page-support.php` | 허브 | ⬜ 신규 |
| 5-a | └ 공지사항 | `/notice/` | `archive-uw_board.php` (notice) + single | `uw_board` CPT | ⬜ 신규 |
| 5-b | └ 자주묻는질문 | `/faq/` | `archive-uw_board.php` (faq) — **FAQPage 스키마** | `uw_board` CPT | ⬜ 신규 |
| 5-c | └ 가이드 센터 | `guide.unwebs.co.kr` | `target="_blank"` | 외부 링크 | ⬜ 링크만 |
| 6 | 블로그 (구 전문 칼럼 통합) | `/blog/` · `/{slug}/` | `home.php` + `archive.php` + `search.php` + `single.php` + `template-parts/common/blog-grid.php` | WP 기본 `post` + 카테고리 5종 | ✅ column→post 통합 (2026-05-20), /column/* → 301 |
| 7 | 문의하기 | `/contact` | `page-contact.php` | `uw_inquiry` 폼 | ⬜ 신규 |
| 8 | **인증 (유지보수 회원 전용)** | — | `inc/uw-auth/` 모듈 + 4개 페이지 | maintenance_pending → maintenance_client | ✅ 완료 (2026-05-15) |
| 8-a | └ 로그인 | `/login` | `page-login.php` | 비로그인 전용, redirect_to 지원 | ✅ 완료 |
| 8-b | └ 회원가입 | `/register` | `page-register.php` | 셀프 가입 → 관리자 승인 필요 | ✅ 완료 |
| 8-c | └ 비밀번호 찾기 | `/lostpassword` | `page-lostpassword.php` | 이메일=아이디 안내 포함 | ✅ 완료 |
| 8-d | └ 비밀번호 재설정 | `/resetpassword` | `page-resetpassword.php` | `key`/`login` 쿼리 검증 | ✅ 완료 |

**URL 원칙**: 영문 소문자 + 하이픈 / 평면 구조(depth 1) / CPT archive가 곧 목록 페이지 역할(portfolio/notice/faq)

## 디자인 토큰

> 기획/디자인 확정 후 값 채움.

```css
:root {
  --uw-primary: ;
  --uw-secondary: ;
  --uw-text: ;
  --uw-bg: ;
  --area-width: px;
  --area-padding: 20px;
}
```

- 제목 폰트: 미정
- 본문 폰트: 미정

## SEO 역할 분담 (2026-04-18 확정)

**플러그인: Rank Math Free** (Yoast 금지). 아래는 역할 분리.

| 영역 | Rank Math가 처리 | 테마(우리)가 처리 |
|---|---|---|
| 메타 title/desc/OG | ✅ UI 자동 | - |
| Organization / ProfessionalService JSON-LD | ✅ Local SEO 탭 | - |
| BlogPosting / Article JSON-LD | ✅ 자동 | - |
| canonical / robots / Twitter card | ✅ 자동 | - |
| sitemap.xml | ✅ 자동 | - |
| Breadcrumb JSON-LD | ✅ 자동 | **`rank_math_breadcrumbs()` 호출만** |
| FAQPage JSON-LD | ✅ Schema Generator(페이지별) | **FAQ 섹션 HTML 템플릿 파트** |
| `llms.txt` | ❌ | **루트 정적 파일 직접 관리** |
| 이미지 WebP/lazy/CLS | ❌ | **테마·업로드 단계** |
| 정의문·수치·인용 콘텐츠 | ❌ | **사람이 작성 시** (FAQ 20·Princeton 9전술) |

**→ `inc/uw-seo/` 모듈 만들지 않음.** 테마 추가물 3개만:
1. `template-parts/common/faq-section.php` — FAQ 배치용 재사용 파트
2. `template-parts/common/breadcrumbs.php` — Rank Math 브레드크럼 래퍼
3. `/llms.txt` — 루트 정적 파일

## CPT 정책 (기존 유지 + 업그레이드)

| CPT | 용도 | 아카이브 URL | 상태 |
|---|---|---|---|
| `uw_gallery` | 포트폴리오 | `/portfolio/` | ⬜ 슬러그 정리 + archive/single 템플릿 하드코딩 + CreativeWork 스키마 |
| `uw_board` (notice) | 공지사항 | `/notice/` | ⬜ Article 스키마 |
| `uw_board` (faq) | FAQ | `/faq/` | ⬜ **FAQPage 스키마** 리치 결과 |
| `uw_inquiry_form` | 폼 정의(필드/메일/캡챠) | 관리자만 | ✅ `main-contact`(ID 51) / `maintenance-request`(ID 101) 등록 — 후자는 다중 첨부(3개·각 10MB) |
| `uw_inquiry_entry` | 폼 제출 내역 저장 | 관리자만 | ✅ post_author=신청자 ID 연결, 메타 `_uw_inquiry_data`에 모든 필드 + 파일 무손실 |
| `uw_maintenance` | 유지보수 처리 현황(메인 KPI/티커) | 관리자만 | 기존 유지 |

## 사용자 역할 (2026-05-15)

`inc/uw-auth/class-uw-auth-roles.php` — 테마 활성화 시 `add_role()`.

| Role | 라벨 | 권한 | 부여 시점 |
|---|---|---|---|
| `maintenance_pending` | 유지보수 대기 | (없음 — wp_signon 차단) | 가입 직후 |
| `maintenance_client` | 유지보수 고객 | `read`, `submit_uw_inquiry` (custom cap) | 관리자 승인 후 |

승인 흐름: wp-admin → 사용자 목록 → 행 액션 **"유지보수 승인"** (`UW_Auth_Admin::process_approve`). 승인 시 고객에게 안내 메일 자동 발송.

## 인증 모듈 (`inc/uw-auth/`, 2026-05-15)

| 파일 | 역할 |
|---|---|
| `class-uw-auth-roles.php` | 역할/cap 등록 |
| `class-uw-auth-handler.php` | 로그인/가입/비번찾기·재설정 POST 처리 + `template_redirect` 접근 제어 + `wp_robots`/Rank Math noindex |
| `class-uw-auth-admin.php` | wp-admin 사용자 목록 승인 액션·회사명/상태 컬럼 + 가입/승인 메일 + uw_inquiry 폼 커스터마이즈(자동회신 제목·entry post_title 회사명 prepend) |

**보안 적용 (2026-05-15 침투 테스트 통과)**:
- nonce / wp_unslash / sanitize_* / esc_* 전부 적용
- 비밀번호 강도: 8자 이상 + 영문 + 숫자 혼합 (정규식)
- 사용자 열거 차단: 가입 중복 시 응답을 success로 위장 + 본인에게 안내 메일
- Open redirect 차단: host 매칭 (절대/protocol-relative 둘 다)
- Rate limit (IP+action 기준): login 5/5분, register·lostpw 3/10분
- 파일 업로드 Phase 1/2 + MIME finfo 화이트리스트 + 롤백

## 확정 사항 (2026-04-18)

- 전면 재설계, themes/Unwebs에서 새로 제작
- 리드마그넷 폐기
- **CSS 파일 구조**: 지침대로 쪼개기 (`main.css` / `main_responsive.css` / `content.css` / `content_responsive.css`)
- **이미지 폴더**: 지침 구조(`common/`, `main/`, `sub/`, `content/{slug}/`)로 재조정
- **네이밍**: 지침 전면 통일 (`cm-/uw-/main-/sub-/{slug}-` + `-con/-wrap/-box/-tit/-txt/-btn/-list/-img`). **예외: `inc/uw-*/`의 CPT 내부 코드 + `assets/css/cpt/*/`의 CPT CSS는 기존 BEM `__` 유지**
- **페이지 제작 방식**: 하드코딩 (`page-{slug}.php` 개별 생성, 에디터 콘텐츠 사용 X)
- **블로그**: WP 기본 `post` (CPT 아님)

## 반응형 작업 룰 (2026-04-28~)

> **현재 단계**: 태블릿/모바일 반응형 디테일 작업. 진행표는 옵시디언.

### BP 표준
- **XL ≤1440** / **LG ≤1180** / **MD ≤768** / **SM ≤480** (`assets/css/layout/layout.css`)
- 모바일 다운 (max-width) 기준. 새 BP 추가 금지

### 작업 순서
- **페이지 단위 순차**: 한 페이지에서 1180 → 768 → 480 끝낸 후 다음 페이지
- 우선순위: 메인 → service → maintenance → portfolio → support → blog → contact

### 작업 룰
- 가로 스크롤 금지. `overflow-x:hidden` 임시처방 X. 래핑/스태킹으로 해결
- cascade 충돌 체크. 새 클래스 만들기 전 `cm-` 공통 재사용 + 부모 셀렉터 override
- HTML 구조 먼저 확인 → CSS 범위 정확히 → 일괄 수정
- 작업 파일: `main_responsive.css` / `content_responsive.css`만. 신규 분리 금지

### 디버그
- URL에 `?debug=1` → 우상단 BP 라벨 + 가로 스크롤 자동 감지 (`assets/css/dev-debug.css` + `dev-debug.js`)
- 운영엔 enqueue 안 됨 (functions.php 조건 분기)

### 진행 추적
- `옵시디언/01-프로젝트/260418-언웹스리뉴얼/3-퍼블리싱/반응형-진행표.md`
- 페이지×BP 매트릭스 + 이슈 로그. 페이지 완료 시 ✅ 표시 + 날짜 기록

---

## 작업 경로

| 용도 | 경로 |
|------|------|
| 프로젝트 관리 (옵시디언) | `~/Library/Mobile Documents/iCloud~md~obsidian/Documents/Obsidian Vault/01-프로젝트/260418-언웹스리뉴얼/` |
| 프로젝트 자료 (데스크탑) | `~/Desktop/260418-언웹스리뉴얼/` |
| 이미지 소스 | `~/Desktop/260418-언웹스리뉴얼/03_퍼블리싱/01_이미지/` |
| 테마 (여기) | `/Users/2belljin/Local Sites/unwebs/app/public/wp-content/themes/Unwebs` |

## 관련 리소스

| 문서 | 경로 |
|------|------|
| 프로젝트 현황 | 옵시디언/0-프로젝트 관리/2-프로젝트 현황.md |
| 요구정의서 | 옵시디언/1-기획/요구정의서.md |
| 소통 내역 | 옵시디언/0-프로젝트 관리/1-소통.md |
| 퍼블리싱 지침 | `~/Library/Mobile Documents/iCloud~md~obsidian/Documents/Obsidian Vault/02-영역/웹팩토리/퍼블리싱/` |
