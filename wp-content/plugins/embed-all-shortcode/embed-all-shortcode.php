<?php
/**
 * Plugin Name: Embed All Shortcode
 * Description: Uniform PDF.js embedding for Google Drive files (Docs/Sheets/Slides and Office). Shortcodes: [embed-all], [embed-all-toc]. Dynamic /embed?id=... route. Uses a Google service account to export non-PDFs to PDF and streams via a REST endpoint.
 * Version: 1.0.1
 * Author: Peter
 */

if (!defined('ABSPATH')) exit;
/* ────────────────────────────────────────────────────────────────────────────
   CONFIG
   ──────────────────────────────────────────────────────────────────────────── */
define('EMBEDALL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EMBEDALL_PLUGIN_URL', plugin_dir_url(__FILE__));
// Service account JSON path (adjust if needed)
define('EMBEDALL_CREDS_JSON', '/var/opt/GoogleAPI/google-service-account.json');

// ✅ Enqueue plugin stylesheet
add_action('wp_enqueue_scripts', function () {
    // Get the current theme's handle (usually 'style')
    $theme_handle = 'mfm-classic-style';

    wp_enqueue_style(
        'embed-all-style',
        plugin_dir_url(__FILE__) . 'embed-all.css',
        [$theme_handle], // Load after theme style.css
        '1.0.1'
    );
}, 20); // Priority >10 ensures it runs after theme
/* ────────────────────────────────────────────────────────────────────────────
   Autoload (Google API client)
   ──────────────────────────────────────────────────────────────────────────── */
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    // If you deploy vendor/ separately, this will be present. Otherwise, install via composer (see README).
    error_log('embed-all-shortcode: Missing vendor/autoload.php (Google API client unavailable).');
}

// ─ EmbedAll: Enqueue assets when needed ─

function embedall_should_enqueue() {
    if (get_query_var('page_embed')) return true;

    if (is_singular()) {
        global $post;
        if ($post && has_shortcode($post->post_content, 'embed-all')) {
            return true;
        }
    }

    return false;
}


/* ────────────────────────────────────────────────────────────────────────────
   Google Client helper
   ──────────────────────────────────────────────────────────────────────────── */
function embedall_google_client() {
    static $client = null;
    if ($client !== null) return $client;

    if (!file_exists(EMBEDALL_CREDS_JSON)) {
        error_log("embed-all-shortcode: Missing service account at " . EMBEDALL_CREDS_JSON);
        return null;
    }
    try {
        $client = new Google_Client();
        $client->setAuthConfig(EMBEDALL_CREDS_JSON);
        $client->addScope(Google_Service_Drive::DRIVE_READONLY);
        $client->setAccessType('offline');
        return $client;
    } catch (Exception $e) {
        error_log("embed-all-shortcode: Google_Client init failed: " . $e->getMessage());
        return null;
    }
}

/* ────────────────────────────────────────────────────────────────────────────
   Drive metadata fetch (Id, Name, MimeType, ModifiedTime) with cache
   ──────────────────────────────────────────────────────────────────────────── */
function embedall_fetch_meta($fileId) {
    $fileId = trim($fileId);
    if ($fileId === '') return [null, null, null, null];

    $cache_key = 'embedall_meta' . $fileId;
    $cached = get_transient($cache_key);
    if ($cached !== false) return $cached;

    $client = embedall_google_client();
    if (!$client) return [null, null, null, null];

    $service = new Google_Service_Drive($client);

    try {
        $file = $service->files->get($fileId, [
            'fields' => 'id,name,mimeType,modifiedTime',
            'supportsAllDrives' => true,
        ]);
    } catch (Exception $e) {
        error_log("[embed-all] Google Drive fetch failed for $fileId: " . $e->getMessage());
        return [null, null, null, null];
    }

    if (!$file) return [null, null, null, null];

    $meta = [
        $file->getId(),
        $file->getName(),
        $file->getMimeType(),
        $file->getModifiedTime(),  // note getter, not property
    ];

    set_transient($cache_key, $meta, 30);
    return $meta;
}

/* Title cleanup */
function embedall_clean_title($name) {
    $name =preg_replace('/^\d+\.\d+\s+/', '', $name);
    $name = preg_replace('/\.(pdf|docx|doc|xlsx|xls|pptx|ppt|gdoc|gsheet|gslides)$/i', '', $name);
    $name = preg_replace_callback('/\b\d{2,4}-\d{2}-\d{2,4}\b/', function($m){ return str_replace('-', '§', $m[0]); }, $name);
    $name = preg_replace('/[_]+/', ' ', $name);
    $name = preg_replace('/\s*-\s*/', ' ', $name);
    $name = str_replace('§', '-', $name);
    $name = preg_replace('/\s+/', ' ', $name);
    $name = trim($name);
    $small = ['a','an','and','as','at','but','by','for','in','nor','of','on','or','per','the','to','vs','via'];
    $acros = ['MFM','BYM','FCNL','PDF','FAQ','RFM','FGC'];
    $words = preg_split('/\s+/', $name);
    $count = count($words);
    foreach ($words as $i => &$w) {
        $orig = $w; $lo = strtolower($w); $up = strtoupper($w);
        if (in_array($up, $acros)) { $w = $up; continue; }
        if (preg_match('/^[A-Z]{2,5}s?$/', $orig)) { $w = $up; continue; }
        if ($i>0 && $i<$count-1 && in_array($lo, $small)) { $w = $lo; continue; }
        $w = ucfirst($lo);
    }
    return implode(' ', $words);
}

/* ────────────────────────────────────────────────────────────────────────────
   Cache helpers: store/retrieve converted PDFs using Drive modifiedTime
   ──────────────────────────────────────────────────────────────────────────── */
function embedall_cache_dir() {
    $uploads = wp_get_upload_dir();
    $dir = trailingslashit($uploads['basedir']) . 'embedall-cache';
    if (!is_dir($dir)) {
    	if (!wp_mkdir_p($dir)) {
        	error_log("embed-all ERROR: failed to create cache directory at $dir");
    	}
    }	
    return $dir;
}

function embedall_cache_key($id, $mtime) {
    // Use Drive file ID + modifiedTime to create a unique filename
    // Avoid special chars: base64url encoding
    $safe = rtrim(strtr(base64_encode($id . '_' . $mtime), '+/', '-_'), '=');
    return $safe . '.pdf';
}

function embedall_fetch_cache($id, $mtime) {
    $path = trailingslashit(embedall_cache_dir()) . embedall_cache_key($id, $mtime);
    if (file_exists($path)) {
        return $path; // Return full path to cached PDF
    }
    return null;
}

function embedall_put_cache($id, $mtime, $pdfData) {
    $path = trailingslashit(embedall_cache_dir()) . embedall_cache_key($id, $mtime);
    file_put_contents($path, $pdfData);
    return $path;
}

/* ────────────────────────────────────────────────────────────────────────────
   REST route: stream PDF (export if necessary) via service account
   GET /wp-json/embed-all/v1/pdf/<id>?download=0
   ──────────────────────────────────────────────────────────────────────────── */
add_action('rest_api_init', function () {
    register_rest_route('embed-all/v1', '/pdf/(?P<id>[^/]+)', [
        'methods'  => 'GET',
        'callback' => function ($request) {
            $id = sanitize_text_field($request->get_param('id'));
            $v = sanitize_text_field($request->get_param('v'));
            $download = $request->get_param('download') ? true : false;
	    error_log('[rest_api_init] Entered with id =' . $id . ' v = ' . $v);


            if (!$id) {
                return new WP_REST_Response(['error'=>'missing id'], 400);
            }

            list($fid, $name, $mime, $mtime) = (array)embedall_fetch_meta($id);
            if (!$fid) {
                return new WP_REST_Response('<div style="padding:1rem;font:14px/1.4 sans-serif;color:#900">Document not found.</div>', 404);
	    }
	// Try cache first
	$cached = embedall_fetch_cache($fid, $mtime);
	if ($cached) {
	    $data = file_get_contents($cached);
	    $filename = embedall_clean_title($name) . '.pdf';
	    error_log('[rest_api_init] Cache found file =' . $filename);
		$disp     = $download ? 'attachment' : 'inline';
		$headers = [
		    'Content-Type'        => 'application/pdf',
		    'Content-Disposition' => $disp . '; filename="' . rawurlencode($filename) . '"',
		    'Cache-Control'       => 'private, max-age=3600',
		    'Accept-Ranges'       => 'bytes',
		];
	     return new WP_HTTP_Response($data, 200, $headers);
	}

	try {
                $client = embedall_google_client();
                if (!$client) {
                    return new WP_REST_Response('<div style="padding:1rem;font:14px/1.4 sans-serif;color:#900">Google client unavailable.</div>', 500);
                }
                $token = $client->fetchAccessTokenWithAssertion();
                if (empty($token['access_token'])) {
                    return new WP_REST_Response('<div style="padding:1rem;font:14px/1.4 sans-serif;color:#900">Authentication failed.</div>', 500);
                }
                $bearer = $token['access_token'];

                // Decide fetch URL
                if ($mime === 'application/pdf') {
                    $url = "https://www.googleapis.com/drive/v3/files/$fid?alt=media&supportsAllDrives=true";
                } else {
                    $url = "https://www.googleapis.com/drive/v3/files/$fid?alt=media&supportsAllDrives=true";
                }

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $bearer"]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                $data = curl_exec($ch);
                $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($http < 200 || $http >= 300 || $data === false) {
                    return new WP_REST_Response('<div style="padding:1rem;font:14px/1.4 sans-serif;color:#900">Drive fetch failed (' . intval($http) . ').</div>', 502);
                }
	    	error_log('[rest_api_init] ' . $name . 'downloaded from Goggle Drive');

		// Office files: convert to PDF via LibreOffice
		$needsLibre = in_array($mime, [
		    'application/msword',
		    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		    'application/vnd.ms-excel',
		    'application/vnd.ms-excel.sheet.macroEnabled.12',
		    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		    'application/vnd.ms-powerpoint',
		    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		], true);
		if(!$needsLibre) {
	    		error_log('[rest_api_init] Cache PDF  id=' . $id . 'Name=' . $name);
 			embedall_put_cache($fid, $mtime, $data);
		} else {
		    // Pick an input extension so LO selects the right filter
		    $ext = match ($mime) {
			'application/msword',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => '.docx',

			'application/vnd.ms-excel',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'      => '.xlsx',
			'application/vnd.ms-excel.sheet.macroEnabled.12'                         => '.xlsm',

			'application/vnd.ms-powerpoint',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation' => '.pptx',

			default => null,
		    };
		    if ($ext === null) {
			return new WP_REST_Response(
			    '<div style="padding:1rem;font:14px/1.4 sans-serif;color:#900">Unsupported Office MIME for conversion.</div>',
			    500
			);
		    }

		    // Write input to a predictable temp path
		    $tmpBase = tempnam(sys_get_temp_dir(), 'embedall_');
		    @unlink($tmpBase);                  // keep the unique prefix, we’ll add our own ext
		    $tmpIn   = $tmpBase . $ext;         // e.g., /tmp/embedall_abc123.docx
		    $tmpOut  = $tmpBase . '.pdf';       // e.g., /tmp/embedall_abc123.pdf
		    file_put_contents($tmpIn, $data);

		    // LibreOffice needs a writable HOME for its profile under php-fpm
		    $loHome = sys_get_temp_dir() . '/lo-profile';
		    if (!is_dir($loHome)) {
			@mkdir($loHome, 0700, true);
		    }

		    // Run headless conversion
		    $cmd  = sprintf(
			'HOME=%s libreoffice --headless --nologo --nofirststartwizard --convert-to pdf --outdir %s %s 2>&1',
			escapeshellarg($loHome),
			escapeshellarg(sys_get_temp_dir()),
			escapeshellarg($tmpIn)
		    );
		    $out = [];
		    $code = 1;
		    exec($cmd, $out, $code);
		    error_log('[rest_api_init]: LO code=' . $code . ' out=' . implode(' | ', $out));

		    // Verify output exactly where we expect it
		    if ($code !== 0 || !is_file($tmpOut) || filesize($tmpOut) < 10) {
			@unlink($tmpIn);
			if (is_file($tmpOut)) @unlink($tmpOut);
			return new WP_REST_Response(
			    '<div style="padding:1rem;font:14px/1.4 sans-serif;color:#900">Conversion failed.</div>',
			    500
			);
		    }

		    // Read, cache, cleanup
		    $data = file_get_contents($tmpOut);
	    	    error_log('[rest_api_init] Cache MS Doc id=' . $id . 'Name=' . $name);
		    embedall_put_cache($fid, $mtime, $data);
	    	    error_log('[rest_api_init] ' . $name . ' converted to pdf and cached');

		    @unlink($tmpIn);
		    @unlink($tmpOut);
		}


                // Final response
                $filename = embedall_clean_title($name) . '.pdf';
                $disp     = $download ? 'attachment' : 'inline';

                $headers = [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => $disp . '; filename="' . rawurlencode($filename) . '"',
                    'Cache-Control'       => 'private, max-age=3600',
                    'Accept-Ranges'       => 'bytes',
                ];

                return new WP_HTTP_Response($data, 200, $headers);

            } catch (Exception $e) {
                return new WP_REST_Response('<div style="padding:1rem;font:14px/1.4 sans-serif;color:#900">Exception: '.htmlspecialchars($e->getMessage()).'</div>', 500);
            }
        },
        'permission_callback' => '__return_true',
        'args' => [ 'id' => ['required'=>true] ],
    ]);
});


add_filter('rest_pre_serve_request', function ($served, $result, $request, $server) {
    $route = $request->get_route();

    // Only intercept our PDF route
    if (strpos($route, '/embed-all/v1/pdf/') === 0 && $result instanceof WP_HTTP_Response) {
        // Send headers verbatim
        foreach ($result->get_headers() as $k => $v) {
            header("$k: $v", true);
        }

        // Make sure nothing is buffering/compressing the body
        while (ob_get_level() > 0) { @ob_end_clean(); }
        if (function_exists('apache_setenv')) { @apache_setenv('no-gzip', '1'); }
        @ini_set('zlib.output_compression', '0');

        $body = $result->get_data();
        if (is_string($body)) {
	    error_log('[rest_pre_serve_request] fully handled output');
            echo $body;      // raw PDF bytes
            return true;     // tell the REST server we fully handled output
        }
    }
    return $served;
}, 10, 4);

function getParentFolderId($fileId): string {
  try {  
    $fileId = trim($fileId);
    if ($fileId === '') {
	    error_log('Bad file id=' . $filedId);
	    return null;
    }
    $client = embedall_google_client();
    if (!$client) {
	    error_log('No $client');
	    return null;
    }

    $service = new Google_Service_Drive($client);
    $file = $service->files->get($fileId, ['fields' => 'parents']);
    $parents = $file->getParents();

    if (empty($parents)) {
	    error('No parents found');
	    return null;
    }

    $parentId = $parents[0];
    return $parentId;
  } catch (Exception $e) {
    return null;
  }
}

/* ────────────────────────────────────────────────────────────────────────────
   Shortcodes
   ──────────────────────────────────────────────────────────────────────────── */
function embedall_shortcode($atts) {
    $a = shortcode_atts([
        'id'     => '',
        'top'    => '',
        'title'  => '',
        'height' => '80vh',
    ], $atts, 'embed-all');
    
    $id    = trim($a['id']);
    $top   = trim($a['top']);
    $title = trim($a['title']);

    if ($id === '') {
        return '<div class="embed-all-error">Missing id.</div>';
    }

    // Get metadata for the ID
    list($fid, $fname, $fmime, $fmtime) = (array) embedall_fetch_meta($id);
    if (empty($fmime)) {
        return '<div class="embed-all-error">Invalid or inaccessible file ID.</div>';
    }

    // ─── FOLDER MODE → Render TOC inline ───────────────────────────────
    if ($fmime === 'application/vnd.google-apps.folder') {
        // Optional: use filename as title if not given
        if ($title === '' && $fname) {
            $title = embedall_clean_title($fname);
        }
	if($top === ''){
		return '<div class="embed-all-error">Missing id.</div>';
	}
        return build_toc($fid, $title, $top);
    }

    // ─── FILE MODE → Nothing more here ─────────────────────────────────
    // Redirect is already handled by add_action('template_redirect', …)
    // This fallback prevents double rendering if something goes wrong.
    return '<p class="embed-all-note">Preparing document...</p>';
}
add_shortcode('embed-all', 'embedall_shortcode');

function embedall_drive_list_folder($folderId) {
    $cache_key = 'embedall_folder_' . $folderId;
    $cached = get_transient($cache_key);
    if ($cached !== false) return $cached;

    $out = [];
    try {
        $client = embedall_google_client();
        if (!$client) return [];
        $service = new Google_Service_Drive($client);

        $q = sprintf("'%s' in parents and trashed=false", esc_sql($folderId));
        $fields = 'nextPageToken, files(id,name,mimeType)';
        $pageToken = null;
        do {
            $resp = $service->files->listFiles([
                'q' => $q,
                'spaces' => 'drive',
                'fields' => $fields,
                'pageSize' => 100,
                'pageToken' => $pageToken
            ]);
            foreach ($resp->getFiles() as $f) {
                $out[] = ['id'=>$f->getId(), 'name'=>$f->getName(), 'mimeType'=>$f->getMimeType()];
            }
            $pageToken = $resp->getNextPageToken();
        } while ($pageToken);

        // Deduplicate: prefer PDF over DOCX/GDoc with same basename
        $byBase = [];
        $others = [];
        foreach ($out as $file) {
            $name = $file['name'];
            $mime = $file['mimeType'];
            $base = mb_strtolower(trim(preg_replace('/\.(pdf|docx)$/i', '', $name)));
            $is_toc  = ($mime === 'application/vnd.google-apps.folder');
            $is_pdf  = ($mime === 'application/pdf');
            $is_docx = ($mime === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            $is_gdoc = ($mime === 'application/vnd.google-apps.document');
            if ($is_pdf || $is_docx || $is_gdoc || $istoc) {
                if (!isset($byBase[$base]) || $is_pdf) $byBase[$base] = $file;
            } else {
                $others[] = $file;
            }
        }
        $list = array_values(array_merge($others, array_values($byBase)));

        set_transient($cache_key, $list, 12 * HOUR_IN_SECONDS);
        return $list;
    } catch (Exception $e) {
        error_log("embed-all-shortcode: folder list failed: " . $e->getMessage());
        return [];
    }
}

function build_toc($folderId, $folderName, $topFolder) {

        if ($folderId === '') {
		return '<p class="embed-all-error">Missing folder id.</p>';
	}
        if ($topFolder === '') {
		return '<p class="embed-all-error">Missing top folder id.</p>';
	}

	$title = embedall_clean_title($folderName);
        $files = embedall_drive_list_folder($folderId);
	$tmp = getParentFolderId($folderId);
	$upId = ($folderId === $topFolder) ? null : getParentFolderId($folderId);
	if($upId ===  null) {
		$upUrl = null;
	}
	else {
                $upUrl   = add_query_arg(['id' => $upId, 'top' => $topFolder], site_url('/embed'));
	}
        $supported_mimes = [
	    'application/vnd.google-apps.folder',
            'application/pdf',
            'application/vnd.google-apps.document',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.google-apps.spreadsheet',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.google-apps.presentation',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];
        $supported = array_values(array_filter($files, function($f) use ($supported_mimes){
            return in_array($f['mimeType'], $supported_mimes, true);
        }));
        usort($supported, function ($a, $b) { return strnatcasecmp($a['name'], $b['name']); });
        ob_start(); ?>
	<div class="entry-header-wrapper">
	  <div class="entry-header-row">
	    <div class="entry-left">
		<?php if (!empty($upUrl)) : ?>
		      <a href="<?php echo esc_url($upUrl); ?>" class="upButton" title="Up to previous contents">
	      		<svg viewBox="0 0 24 24" aria-hidden="true" width="36" height="36">
			<path d="M6 15l6-6 6 6" fill="none" stroke="black" stroke-width="2" />
	      		</svg>
		      </a>
		<?php endif; ?>
	    </div>
	    <div class="entry-center">
	      <h1 class="entry-embed-title"><?php echo esc_html($title); ?></h1>
	    </div>
	    <div class="entry-right"></div>
	  </div>
	  <hr class="entry-title-rule">
	</div>
        <!--<h1 class="entry-title"><?php echo esc_html($title); ?></h1>-->
        <div class="embed-all-toc">
          <ol>
            <?php foreach ($supported as $f):
                $cid    = $f['id'];
                $ctitle = embedall_clean_title($f['name']);
                $href   = add_query_arg(['id' => $cid, 'top' => $topFolder, 'title' => $ctitle], site_url('/embed'));
            ?>
              <li><a href="<?php echo esc_url($href); ?>"><?php echo esc_html($ctitle); ?></a></li>
            <?php endforeach; ?>
          </ol>
        </div>
        <?php
        return ob_get_clean();
}

// ───────────────────────────────────────────────────────────────
// Redirect single files (PDF, DOCX, etc.) to the Mozilla PDF.js viewer
// ───────────────────────────────────────────────────────────────
add_action('template_redirect', function() {

    // 1️⃣ Capture the ?id= or ?Id=
    $id = isset($_GET['id']) ? sanitize_text_field($_GET['id'])
         : (isset($_GET['Id']) ? sanitize_text_field($_GET['Id']) : '');
    if ($id === '') return; // no ID → normal page load

    // 2️⃣ Ask Google Drive for metadata
    list($fid, $fname, $fmime, $fmtime) = (array) embedall_fetch_meta($id);
    if (empty($fmime)) return; // couldn’t determine → do nothing

    // 3️⃣ If it's a folder, stay on page-embed.php (TOC view)
    if ($fmime === 'application/vnd.google-apps.folder') return;

    // 4️⃣ Otherwise it's a file — redirect to PDF.js viewer
    $file_url = add_query_arg([
  	'v'        => md5($fmtime),
  	'download' => '0',
     ], rest_url('embed-all/v1/pdf/' . rawurlencode($id)));

    wp_redirect($file_url);
    exit;
});

/*
Plugin Name: Stewardship Viewer
Description: Custom routing and template override for viewer embed.
Version: 1.0
Author: Peter
*/
// 🔁 Register rewrite rule
add_action('init', function () {
    add_rewrite_rule('^embed/?$', 'index.php?page_embed=1', 'top');
//    error_log('Rewrite rule registered for /embed');
});

// 🧩 Register query var
add_filter('query_vars', function ($vars) {
    $vars[] = 'page_embed';
//    error_log('Query var page_embed registered');
    return $vars;
});

// 🧰 Override template if query var is present (frontend only)
add_filter('template_include', function ($template) {
    $is_rest = !empty($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/wp-json/') !== false;

    if (!is_admin() && !$is_rest && get_query_var('page_embed')) {
        $custom_template = plugin_dir_path(__FILE__) . 'page-embed.php';
//        error_log('page_embed detected — loading: ' . $custom_template);
        return $custom_template;
    }

    return $template;
});

// 🔄 Flush rewrite rules on activation
register_activation_hook(__FILE__, function () {
    add_rewrite_rule('^embed/?$', 'index.php?page_embed=1', 'top');
    flush_rewrite_rules();
});

// 🔄 Flush rewrite rules on deactivation
register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});

// ✅ Global confirmation
error_log('Stewardship Viewer plugin loaded — all hooks registered');
