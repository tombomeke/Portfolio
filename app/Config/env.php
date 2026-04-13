<?php
// Shared env helper for application bootstrap and config loading.

if (!function_exists('portfolioEnv')) {
	function portfolioEnv(string $key, string $default = ''): string {
		$value = getenv($key);
		if ($value !== false && $value !== '') {
			return (string) $value;
		}

		if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
			return (string) $_ENV[$key];
		}

		if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
			return (string) $_SERVER[$key];
		}

		static $dotEnv = null;
		if ($dotEnv === null) {
			$dotEnv = [];

			$candidatePaths = [
				dirname(__DIR__, 2) . '/.env',
				dirname(__DIR__, 3) . '/.env',
			];

			$documentRoot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\');
			if ($documentRoot !== '') {
				$candidatePaths[] = $documentRoot . '/.env';
				$candidatePaths[] = dirname($documentRoot) . '/.env';
			}

			if (DIRECTORY_SEPARATOR === '/') {
				$candidatePaths[] = '/.env';
			}

			$candidatePaths = array_values(array_unique($candidatePaths));

			foreach ($candidatePaths as $dotEnvPath) {
				if (!is_file($dotEnvPath) || !is_readable($dotEnvPath)) {
					continue;
				}

				$lines = file($dotEnvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
				foreach ($lines as $line) {
					$line = ltrim((string) $line, "\xEF\xBB\xBF");
					$line = trim($line);
					if ($line === '' || str_starts_with($line, '#') || strpos($line, '=') === false) {
						continue;
					}

					[$envKey, $envValue] = array_map('trim', explode('=', $line, 2));
					if ($envKey === '') {
						continue;
					}

					$envValue = trim($envValue, " \t\n\r\0\x0B\"'");
					$dotEnv[$envKey] = $envValue;
				}
			}
		}

		return isset($dotEnv[$key]) ? (string) $dotEnv[$key] : $default;
	}
}