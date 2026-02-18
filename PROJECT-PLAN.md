# UNWEBS 프로젝트 완성 계획

> 마감: 2026년 2월 28일
> 현재 완성도: 약 55~60%
> 최종 업데이트: 2026-02-15

---

## 완료 항목

- [x] 테마 코어 구조 (functions.php, header.php, footer.php, config.php)
- [x] 네비게이션 시스템 (GNB, 모바일 메뉴, 사이트맵 오버레이)
- [x] 메인 비주얼 포트폴리오 슬라이더 (4슬라이드)
- [x] 대시보드 티커 시스템 (프로젝트/상담 현황)
- [x] 상태 배너 시스템 (banner-status)
- [x] UW Board CPT (관리자 + 프론트엔드 + CSV)
- [x] UW Gallery CPT (관리자 + 프론트엔드 5종 레이아웃)
- [x] UW Inquiry CPT (폼빌더 + SMTP 메일 발송)
- [x] UW Maintenance CPT (관리자 백엔드)
- [x] CSS 아키텍처 (reset, layout, responsive, animate)
- [x] SEO / OG 메타태그 / favicon
- [x] 보안 헤더 (X-Frame, Referrer-Policy 등)
- [x] 푸터 법적 모달 (개인정보처리방침, 이용약관)

---

## Phase 1: 코어 페이지 완성 (2/15 ~ 2/18)

서비스 소개 관련 핵심 페이지 제작

| # | 작업 | 파일 | 상태 |
|---|------|------|------|
| 1-1 | [ ] 서비스 안내 페이지 템플릿 | `page-templates/page-service.php` | 미착수 |
| 1-2 | [ ] 준비자료 페이지 템플릿 | `page-templates/page-preparation.php` | 미착수 |
| 1-3 | [ ] 제작절차 페이지 템플릿 | `page-templates/page-process.php` | 미착수 |
| 1-4 | [ ] 회사소개 페이지 완성 | `page-templates/page-company-intro.php` | 초기화 상태 |

### 참고사항
- sub-visual.php 공통 컴포넌트 활용 (브레드크럼 + LNB)
- config.php GNB 구조에 맞게 메뉴 연동 확인

---

## Phase 2: 유지보수 & 포트폴리오 (2/19 ~ 2/21)

| # | 작업 | 파일 | 상태 |
|---|------|------|------|
| 2-1 | [ ] 유지보수 신청 페이지 | `page-templates/page-maintenance-apply.php` | 미착수 |
| 2-2 | [ ] 건별 유지보수 페이지 | `page-templates/page-maintenance-once.php` | 미착수 |
| 2-3 | [ ] 정기 유지보수 페이지 | `page-templates/page-maintenance-regular.php` | 미착수 |
| 2-4 | [ ] 포트폴리오 아카이브 페이지 | `page-templates/page-portfolio.php` | 미착수 |
| 2-5 | [ ] UW Maintenance 프론트엔드 완성 | `inc/uw-maintenance/` | 진행중 |

### 참고사항
- 포트폴리오 페이지는 Gallery CPT `[uw_gallery]` shortcode 연동
- 유지보수 신청 페이지는 Inquiry CPT `[uw_inquiry_form]` 연동 가능

---

## Phase 3: 고객지원 & 블로그 (2/22 ~ 2/24)

| # | 작업 | 파일 | 상태 |
|---|------|------|------|
| 3-1 | [ ] 공지사항 페이지 (Board CPT 연동) | `page-templates/page-notice.php` | 미착수 |
| 3-2 | [ ] FAQ 페이지 템플릿 | `page-templates/page-faq.php` | 미착수 |
| 3-3 | [ ] 가이드 페이지 템플릿 | `page-templates/page-guide.php` | 미착수 |
| 3-4 | [ ] 블로그 리스트 스타일링 | `home.php` + CSS | 미착수 |
| 3-5 | [ ] 블로그 싱글 스타일링 | `single.php` + CSS | 미착수 |
| 3-6 | [ ] 블로그 아카이브 스타일링 | `archive.php` + CSS | 미착수 |

### 참고사항
- 공지사항: `[uw_board name="notice"]` shortcode 활용
- FAQ: 아코디언 UI 적용 권장
- 블로그: WP 기본 포스트 타입 사용

---

## Phase 4: 반응형 & QA (2/25 ~ 2/26)

| # | 작업 | 체크포인트 | 상태 |
|---|------|-----------|------|
| 4-1 | [ ] 모바일 반응형 검증 (375px~) | 전체 페이지 레이아웃 깨짐 확인 | 미착수 |
| 4-2 | [ ] 태블릿 반응형 검증 (768px~) | 그리드/이미지 비율 확인 | 미착수 |
| 4-3 | [ ] 크로스 브라우저 테스트 | Chrome, Safari, Edge | 미착수 |
| 4-4 | [ ] 문의폼 전송 테스트 | SMTP 메일 수신 확인 | 미착수 |
| 4-5 | [ ] 게시판 CRUD 테스트 | 작성/수정/삭제/비밀글 | 미착수 |
| 4-6 | [ ] 갤러리 레이아웃 테스트 | 5종 레이아웃 정상 렌더링 | 미착수 |
| 4-7 | [ ] 이미지 최적화 | WebP 변환, lazy loading 확인 | 미착수 |
| 4-8 | [ ] 페이지 로딩 속도 점검 | Lighthouse 80점 이상 목표 | 미착수 |

---

## Phase 5: 배포 준비 (2/27 ~ 2/28)

| # | 작업 | 상세 | 상태 |
|---|------|------|------|
| 5-1 | [ ] 개발용 코드 정리 | 시드 데이터, console.log 제거 | 미착수 |
| 5-2 | [ ] .gitignore 정비 | .DS_Store, node_modules 등 제외 확인 | 미착수 |
| 5-3 | [ ] Git 커밋 정리 및 태깅 | v1.0 release tag | 미착수 |
| 5-4 | [ ] wp-config.php 운영 설정 | 디버그 끄기, SMTP 운영 설정 | 미착수 |
| 5-5 | [ ] 도메인 연결 확인 | unwebs.co.kr SSL/DNS | 미착수 |
| 5-6 | [ ] 최종 라이브 테스트 | 운영 서버에서 전체 기능 확인 | 미착수 |

---

## 일정 요약

```
2/15 ████████░░░░░░░░░░░░░░░░░░░░ Phase 1 시작
2/18 ████████████░░░░░░░░░░░░░░░░ Phase 1 완료
2/19 ████████████████░░░░░░░░░░░░ Phase 2 시작
2/21 ████████████████████░░░░░░░░ Phase 2 완료
2/22 ████████████████████████░░░░ Phase 3 시작
2/24 ████████████████████████████ Phase 3 완료
2/25 ██████████████████████████████ Phase 4 QA
2/27 ████████████████████████████████ Phase 5 배포
2/28 ████████████████████████████████ 마감
```

---

## 작업 전 확인사항

- [ ] 각 서브페이지 디자인 시안 준비 여부
- [ ] 포트폴리오 실제 콘텐츠/이미지 확보 여부
- [ ] 블로그 운영 방식 결정 (WP 기본 포스트 vs 별도 CPT)
- [ ] 운영 서버 호스팅 환경 확인 (PHP 버전, DB 등)
- [ ] SMTP 운영 계정 정보 확보 여부

---

## 진행 기록

| 날짜 | 작업 내용 | 비고 |
|------|----------|------|
| 2/15 | 프로젝트 계획 수립 | PROJECT-PLAN.md 작성 |
| | | |
| | | |
