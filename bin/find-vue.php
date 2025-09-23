<?php
/**
 * check-vue.php
 *
 * Scan Blade templates for Vue usage with fewer false positives.
 * - Looks only inside HTML tags.
 * - Matches v-*, @event=, :prop= (excluding xmlns/xlink/xml).
 * - Detects registered Vue components from resources/js/app.js.
 * - Skips Blade components <x-...> and <flux:...>.
 * - Skips vendor-ish paths by default.
 */

declare(strict_types=1);

$root = __DIR__;
$bladeRoot = $root . '/resources/views';
$appJsPath = $root . '/resources/js/app.js';

/** CONFIG *******************************************************************/
$excludePaths = [
    $root . '/vendor',
    $root . '/node_modules',
    $bladeRoot . '/vendor', // vendor-published views
];
$flagCustomTags = true; // set false if you only want definite Vue hits
/*****************************************************************************/

function pathIsExcluded(string $path, array $excludePaths): bool {
    $p = str_replace('\\', '/', $path);
    foreach ($excludePaths as $ex) {
        $ex = str_replace('\\', '/', $ex);
        if (strpos($p, rtrim($ex, '/')) === 0) return true;
    }
    return false;
}

function loadVueComponentsFromAppJs(string $appJsPath): array {
    if (!is_file($appJsPath)) return [];
    $js = file_get_contents($appJsPath);

    // Vue.component("name", ...)
    preg_match_all('/Vue\.component\(\s*[\'"]([^\'"]+)[\'"]\s*,/i', $js, $m1);
    // app.component("name", ...) — Vue 3 style (just in case)
    preg_match_all('/\bapp\.component\(\s*[\'"]([^\'"]+)[\'"]\s*,/i', $js, $m2);

    $names = array_unique(array_filter(array_merge($m1[1] ?? [], $m2[1] ?? [])));

    // Also try to infer kebab-case from PascalCase if present
    $extra = [];
    foreach ($names as $n) {
        if (strpos($n, '-') === false && preg_match('/[A-Z]/', $n)) {
            // convert PascalCase to kebab-case
            $kebab = strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $n));
            $extra[] = $kebab;
        }
    }
    return array_values(array_unique(array_merge($names, $extra)));
}

$vueComponents = loadVueComponentsFromAppJs($appJsPath);

/**
 * Minimal set of standard HTML/SVG tag names to avoid flagging them as “custom tags”.
 * (Not exhaustive — add more if needed.)
 */
$standardTags = array_flip([
    // HTML
    'html','head','title','meta','link','style','script','body','header','footer','nav','main','section','article','aside',
    'h1','h2','h3','h4','h5','h6','p','div','span','a','ul','ol','li','dl','dt','dd','table','thead','tbody','tfoot','tr','td','th',
    'form','label','input','textarea','select','option','button','fieldset','legend','datalist','output','progress','meter',
    'img','picture','source','figure','figcaption','canvas','iframe','video','audio','track','map','area','blockquote','pre','code',
    'small','strong','em','i','b','u','s','sub','sup','br','hr','time','mark','kbd','samp','var','template','slot',
    // SVG (common)
    'svg','g','path','rect','circle','ellipse','line','polyline','polygon','text','defs','use','symbol','clipPath','mask','linearGradient','radialGradient','stop','pattern','filter'
]);

/**
 * Find all tags in the content and return iterable of [tagName, attrsString, offsetStart].
 * We use a reluctant match for attributes chunk to avoid over-greedy captures.
 */
function findTags(string $content): array {
    $tags = [];
    if (preg_match_all('/<([a-zA-Z][\w:-]*)\b([^>]*?)>/s', $content, $matches, PREG_OFFSET_CAPTURE)) {
        $count = count($matches[0]);
        for ($i = 0; $i < $count; $i++) {
            $tagName = $matches[1][$i][0];
            $attrs   = $matches[2][$i][0];
            $offset  = $matches[0][$i][1];
            $tags[] = [$tagName, $attrs, $offset];
        }
    }
    return $tags;
}

function lineFromOffset(string $content, int $offset): int {
    // Count newlines up to offset
    $prefix = substr($content, 0, $offset);
    return substr_count($prefix, "\n") + 1;
}

/**
 * Scan attributes string for Vue-ish directives/shortcuts.
 * Returns list of hits with type and exact attribute match.
 */
function scanVueAttributes(string $attrs): array {
    $hits = [];

    // v- directives (if, else[-if], for, show, model, on, bind, html, text, cloak, once, slot)
    if (preg_match_all('/\s(v-(?:if|else-if|else|for|show|model|on:[\w.-]+|bind:[\w.-]+|html|text|cloak|once|slot))\s*=/i', $attrs, $m)) {
        foreach ($m[1] as $attr) {
            $hits[] = ['type' => 'v-directive', 'attr' => $attr];
        }
    }

    // @event="..." shorthand — must be an attribute (i.e., followed by '=')
    if (preg_match_all('/\s(@[A-Za-z][\w.-]*)\s*=/i', $attrs, $m2)) {
        foreach ($m2[1] as $attr) {
            $hits[] = ['type' => '@event', 'attr' => $attr];
        }
    }

    // :prop="..." shorthand — exclude XML namespaces like xmlns:, xlink:, xml:
    if (preg_match_all('/\s:(?!xmlns\b|xlink\b|xml\b)([A-Za-z_][\w.-]*)\s*=/i', $attrs, $m3)) {
        foreach ($m3[1] as $prop) {
            $hits[] = ['type' => ':bind', 'attr' => ':' . $prop];
        }
    }

    // Mustache conflict marker @{{ ... }} is often present in Blade+Vue mixes
    if (preg_match('/@{{/', $attrs)) {
        $hits[] = ['type' => 'mustache-escape', 'attr' => '@{{ ... }}'];
    }

    return $hits;
}

function scanBladeFile(string $path, array $vueComponents, array $standardTags, bool $flagCustomTags): array {
    $content = file_get_contents($path);
    $results = [];

    foreach (findTags($content) as [$tag, $attrs, $offset]) {
        // Skip closing tags and Blade component tags
        // (findTags only returns opening tags, but tagName could be 'x-...' or 'flux:...')
        $lowerTag = strtolower($tag);
        if (strpos($lowerTag, 'x-') === 0 || strpos($lowerTag, 'flux:') === 0) {
            continue; // blade component
        }

        $line = lineFromOffset($content, $offset);

        // Vue component name match (exact tag name in known list)
        $isKnownVueComponent = in_array($lowerTag, array_map('strtolower', $vueComponents), true);

        // Heuristic: “custom tag” if it’s not a standard tag and not Blade component.
        $looksCustom = !isset($standardTags[$lowerTag]) && !$isKnownVueComponent;

        // Check attributes for Vue usage
        $attrHits = scanVueAttributes($attrs);

        // Record hits
        if ($isKnownVueComponent) {
            $results[] = [
                'line' => $line,
                'kind' => 'vue-component-tag',
                'detail' => "<{$tag}>"
            ];
        }

        foreach ($attrHits as $hit) {
            $results[] = [
                'line' => $line,
                'kind' => $hit['type'],
                'detail' => $hit['attr']
            ];
        }

        if ($flagCustomTags && $looksCustom) {
            // Don’t flag obvious template helpers like <template>
            if ($lowerTag !== 'template') {
                $results[] = [
                    'line' => $line,
                    'kind' => 'custom-tag',
                    'detail' => "<{$tag}>"
                ];
            }
        }
    }

    return $results;
}

// Walk views dir
$rii = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($bladeRoot, FilesystemIterator::SKIP_DOTS)
);

$report = [];

foreach ($rii as $file) {
    /** @var SplFileInfo $file */
    if ($file->isDir()) continue;
    $path = $file->getPathname();

    if (pathIsExcluded($path, $excludePaths)) continue;
    if (!preg_match('/\.blade\.php$/', $path)) continue;

    $hits = scanBladeFile($path, $vueComponents, $standardTags, $flagCustomTags);

    if (!empty($hits)) {
        // Sort hits by line number
        usort($hits, fn($a, $b) => $a['line'] <=> $b['line']);
        $report[$path] = $hits;
    }
}

// Output
if (empty($report)) {
    echo "✅ No Vue-like usage found in Blade templates (after filters).\n";
    exit(0);
}

foreach ($report as $path => $hits) {
    echo "📄 {$path}\n";
    foreach ($hits as $h) {
        printf("  Line %d: %-18s %s\n", $h['line'], '['.$h['kind'].']', $h['detail']);
    }
    echo "\n";
}

// Small summary
$totalFiles = count($report);
$totalHits = array_sum(array_map('count', $report));
echo "— Scanned complete. {$totalHits} hits across {$totalFiles} files.\n";


