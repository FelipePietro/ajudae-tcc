<?php

// Static source inventory only: no Laravel bootstrap, network, DB or file writes.
$root = dirname(__DIR__);
$files = [];
foreach (['resources/views', 'resources/css', 'resources/js', 'app/Http', 'app/Policies', 'app/Services', 'app/Models', 'routes', 'tests/Feature'] as $folder) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$folder, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isFile() && in_array($file->getExtension(), ['php', 'js', 'css'], true)) {
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            $files[$relative] = file_get_contents($file->getPathname());
        }
    }
}
ksort($files);
$inventory = ['frontend' => [], 'backend' => [], 'tests' => []];
foreach ($files as $path => $source) {
    $base = ['path' => $path, 'lines' => substr_count($source, "\n") + 1, 'sha256' => hash('sha256', $source)];
    if (str_starts_with($path, 'tests/')) {
        preg_match_all('/function\s+(test_\w+)/', $source, $tests);
        $inventory['tests'][] = $base + ['tests' => $tests[1]];
        continue;
    }
    if (str_starts_with($path, 'resources/')) {
        preg_match_all('/<form\b(?:[^>"\']|"[^"]*"|\'[^\']*\')*>/si', $source, $forms);
        preg_match_all('/\bname=(["\'])(.*?)\1/s', $source, $fields);
        preg_match_all('/(?:@include\s*\([^\n]+|<x-[a-zA-Z0-9.:-]+)/', $source, $components);
        preg_match_all('/route\(\s*["\']([^"\']+)["\']/', $source, $routes);
        preg_match_all('/(?:fetch\s*\(|axios\b|XMLHttpRequest|localStorage|sessionStorage|preventDefault\s*\(|MOCK|mock)/', $source, $markers, PREG_OFFSET_CAPTURE);
        $inventory['frontend'][] = $base + ['forms' => $forms[0], 'fields' => array_values(array_unique(array_diff($fields[2], ['viewport']))), 'components' => array_values(array_unique($components[0])), 'route_names' => array_values(array_unique($routes[1])), 'markers' => array_map(fn ($m) => ['text' => $m[0], 'line' => substr_count(substr($source, 0, $m[1]), "\n") + 1], $markers[0])];
        continue;
    }
    if (! str_starts_with($path, 'app/')) {
        continue;
    }
    $tokens = token_get_all($source);
    $offset = 0;
    $methods = [];
    $active = null;
    foreach ($tokens as $index => $token) {
        $text = is_array($token) ? $token[1] : $token;
        if (is_array($token) && $token[0] === T_FUNCTION && $active === null) {
            $next = $index + 1;
            while (isset($tokens[$next]) && is_array($tokens[$next]) && in_array($tokens[$next][0], [T_WHITESPACE, T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG], true)) {
                $next++;
            }
            if (isset($tokens[$next]) && is_array($tokens[$next]) && $tokens[$next][0] === T_STRING) {
                $active = ['name' => $tokens[$next][1], 'line' => $token[2], 'offset' => $offset, 'depth' => 0, 'opened' => false];
            }
        }
        if ($active !== null) {
            if ($token === '{' || (is_array($token) && in_array($token[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) {
                $active['depth']++;
                $active['opened'] = true;
            } elseif ($token === '}') {
                $active['depth']--;
                if ($active['opened'] && $active['depth'] === 0) {
                    $body = substr($source, $active['offset'], $offset + strlen($text) - $active['offset']);
                    $validation = [];
                    $bodyTokens = token_get_all('<?php '.$body);
                    for ($i = 0; $i < count($bodyTokens); $i++) {
                        if (is_array($bodyTokens[$i]) && $bodyTokens[$i][0] === T_STRING && $bodyTokens[$i][1] === 'validate') {
                            $expression = 'validate';
                            $depth = 0;
                            for ($j = $i + 1; $j < count($bodyTokens); $j++) {
                                $part = $bodyTokens[$j];
                                $expression .= is_array($part) ? $part[1] : $part;
                                if ($part === '(') { $depth++; }
                                if ($part === ')' && --$depth === 0) { break; }
                            }
                            $validation[] = $expression;
                        }
                    }
                    $returns = [];
                    foreach ($bodyTokens as $k => $part) {
                        if (is_array($part) && $part[0] === T_RETURN) {
                            $expression = 'return';
                            for ($j = $k + 1; $j < count($bodyTokens); $j++) {
                                $expression .= is_array($bodyTokens[$j]) ? $bodyTokens[$j][1] : $bodyTokens[$j];
                                if ($bodyTokens[$j] === ';') { break; }
                            }
                            $returns[] = $expression;
                        }
                    }
                    $relevant = preg_grep('/throw new|abort\(|authorize\(|hasPermission|status_|isApprovedVolunteer|Rule::in/', explode("\n", $body));
                    $methods[] = ['name' => $active['name'], 'line' => $active['line'], 'validation' => $validation, 'returns' => $returns, 'conditions' => array_values(array_map('trim', $relevant))];
                    $active = null;
                }
            }
        }
        $offset += strlen($text);
    }
    $inventory['backend'][] = $base + ['methods' => $methods];
}
echo json_encode($inventory, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
