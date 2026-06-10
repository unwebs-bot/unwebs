/**
 * BrowserSync — 반응형 작업 핫리로드
 * 사용: npm install (최초 1회) → npm run watch
 * 접속: http://localhost:3000  (Local Sites의 unwebs.local 프록시)
 *
 * Local Sites가 https라면 PROXY_TARGET을 https로 바꾸고 https: true 켜기.
 */
const PROXY_TARGET = 'http://unwebs.local';

module.exports = {
  proxy: PROXY_TARGET,
  port: 3000,
  open: false,
  notify: false,
  reloadDebounce: 200,
  ghostMode: false,
  injectChanges: true,
  files: [
    'style.css',
    'assets/css/**/*.css',
    'assets/js/**/*.js',
    '**/*.php',
  ],
  ignore: [
    'node_modules/**',
    '.git/**',
  ],
  watchOptions: {
    ignoreInitial: true,
  },
};
