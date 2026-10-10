#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
PHP_IMAGE='php@sha256:6ce95208609dc66df163ab936c970b3b34cd901b85c747102c5999f08ade9143'
PHPUNIT_SHA256='ab8bc3017a64cf75a2112f5e089a1d5b0c4cc2116556d60c1af5e3f584e85c5e'
PHPUNIT_FINGERPRINT='D8406D0D82947747293778314AA394086372C20A'
EXPECTED_TEST_COUNT=25
SHUFFLE_SEED=20261010

DOCKER=(docker)
if ! docker info >/dev/null 2>&1; then
  DOCKER=(sudo docker)
fi

WORK="$(mktemp -d)"
GNUPGHOME="$(mktemp -d)"
chmod 700 "$GNUPGHOME"
export GNUPGHOME
trap 'rm -rf "$WORK" "$GNUPGHOME"' EXIT

curl -fsSL -o "$GNUPGHOME/bergmann.asc" https://sebastian-bergmann.de/gpg.asc
gpg --batch --import "$GNUPGHOME/bergmann.asc" >/dev/null 2>&1
curl -fsSL -o "$WORK/phpunit-4.8.36.phar" https://phar.phpunit.de/phpunit-4.8.36.phar
curl -fsSL -o "$WORK/phpunit-4.8.36.phar.asc" https://phar.phpunit.de/phpunit-4.8.36.phar.asc

VERIFY_OUT="$(gpg --batch --status-fd 1 --verify "$WORK/phpunit-4.8.36.phar.asc" "$WORK/phpunit-4.8.36.phar" 2>&1)"
VALIDSIG_LINE="$(printf '%s\n' "$VERIFY_OUT" | awk '$2 == "VALIDSIG" { print; exit }')"
if [[ -z "$VALIDSIG_LINE" ]]; then
  echo "PHPUnit PHAR signature verification failed: no VALIDSIG" >&2
  exit 1
fi
if [[ "$VALIDSIG_LINE" != *"$PHPUNIT_FINGERPRINT"* ]]; then
  echo "PHPUnit PHAR fingerprint mismatch" >&2
  exit 1
fi
echo "$PHPUNIT_SHA256  $WORK/phpunit-4.8.36.phar" | sha256sum -c -

PHP_VERSION="$("${DOCKER[@]}" run --rm --network none "$PHP_IMAGE" php -r 'echo PHP_VERSION;')"
if [[ "$PHP_VERSION" != "5.6.40" ]]; then
  echo "Unexpected PHP floor version: $PHP_VERSION" >&2
  exit 1
fi

ORDER1="$("${DOCKER[@]}" run --rm --network none -v "$REPO_ROOT":/src:ro "$PHP_IMAGE" \
  php -d error_reporting=-1 /src/tests/phpunit/support/write-shuffled-suite.php /tmp/out1.xml "$SHUFFLE_SEED" \
  | tail -n 1)"
ORDER2="$("${DOCKER[@]}" run --rm --network none -v "$REPO_ROOT":/src:ro "$PHP_IMAGE" \
  php -d error_reporting=-1 /src/tests/phpunit/support/write-shuffled-suite.php /tmp/out2.xml "$SHUFFLE_SEED" \
  | tail -n 1)"
if [[ "$ORDER1" != "$ORDER2" ]]; then
  echo "Shuffle order not reproducible: $ORDER1 vs $ORDER2" >&2
  exit 1
fi

JUNIT="$WORK/junit.xml"
JUNIT_SHUFFLED="$WORK/junit-shuffled.xml"
SHUFFLED_XML="$WORK/phpunit-shuffled.xml"

"${DOCKER[@]}" run --rm --network none \
  -v "$REPO_ROOT":/src:ro \
  -v "$WORK":/work \
  -w /src/tests/phpunit \
  "$PHP_IMAGE" \
  php -d error_reporting=-1 -d display_errors=1 -d display_startup_errors=1 \
  /work/phpunit-4.8.36.phar -c phpunit.xml --log-junit /work/junit.xml \
  | tee "$WORK/phpunit.log"

assert_clean_phpunit_log() {
  local log_file="$1"
  local label="$2"
  if ! grep -q "OK ($EXPECTED_TEST_COUNT tests," "$log_file"; then
    echo "$label did not end with OK ($EXPECTED_TEST_COUNT tests, ...)" >&2
    exit 1
  fi
  if grep -qE '^Warning:' "$log_file"; then
    echo "$label reported a PHPUnit Warning line" >&2
    exit 1
  fi
  if grep -qiE '(, Skipped:|, Incomplete:|, Risky:|There were [0-9]+ warnings|WARNINGS!)' "$log_file"; then
    echo "$label reported skipped/incomplete/risky/test warnings" >&2
    exit 1
  fi
  if grep -qiE '\bNotice\b|\bWarning\b|\bDeprecated\b|Strict Standards|\bParse error\b|\bFatal error\b|Catchable fatal error' "$log_file"; then
    echo "$label contained a PHP diagnostic" >&2
    exit 1
  fi
}

assert_clean_phpunit_log "$WORK/phpunit.log" "Canonical run"

"${DOCKER[@]}" run --rm --network none \
  -v "$REPO_ROOT":/src:ro \
  -v "$WORK":/work \
  "$PHP_IMAGE" \
  php -d error_reporting=-1 /src/tests/phpunit/support/write-shuffled-suite.php /work/phpunit-shuffled.xml "$SHUFFLE_SEED" >/dev/null

"${DOCKER[@]}" run --rm --network none \
  -v "$REPO_ROOT":/src:ro \
  -v "$WORK":/work \
  "$PHP_IMAGE" \
  php -d error_reporting=-1 -d display_errors=1 -d display_startup_errors=1 \
  /work/phpunit-4.8.36.phar -c /work/phpunit-shuffled.xml --log-junit /work/junit-shuffled.xml \
  | tee "$WORK/phpunit-shuffled.log"

assert_clean_phpunit_log "$WORK/phpunit-shuffled.log" "Shuffled run"

echo "All runs passed (PHP $PHP_VERSION, $EXPECTED_TEST_COUNT tests)."
