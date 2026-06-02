<?php

namespace App\Jobs;

use App\Models\Wp\Post\Post;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Browsershot\Browsershot;

class CreateOgImageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public Post $post;

    public $tries = 2;

    public $force = false;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function forced()
    {
        $this->force = true;
        return $this;
    }

    public function handle()
    {
        try {
            //            if (get_the_post_thumbnail($this->post->wpPost()) || get_post_meta($this->post->id(), 'og_image', true)) {
            if (($imgId = get_post_meta($this->post->id(), 'og_image', true)) && !$this->force) {
                return;
            }

            // delete the existing media upload with ID $imgId
            if ($imgId) {
                wp_delete_attachment($imgId, true);
            }

            $chromePath = '/usr/bin/chromium';

            $bshot = Browsershot::url($this->post->ogImageScreenshotUrl())
    ->devicePixelRatio(2)
    ->windowSize(1200, 630)
    ->setNodeBinary('/home/forge/.nvm/versions/node/v20.18.0/bin/node')
    ->setNpmBinary('/home/forge/.nvm/versions/node/v20.18.0/bin/npm')
    ->setChromePath($chromePath)
    ->ignoreHttpsErrors()
    ->addChromiumArguments([
        'host-resolver-rules' => 'MAP pacurar.net 127.0.0.1:8443,MAP pacurar.dev 127.0.0.1:8443',
        'headless' => 'new',
        'no-sandbox',
        'ignore-certificate-errors',
        'disable-setuid-sandbox',
        'disable-dev-shm-usage',
        'disable-gpu',
        'no-zygote',
        'single-process',
        'disable-software-rasterizer',
        'disable-features' => 'VizDisplayCompositor',
        'user-data-dir' => sys_get_temp_dir() . '/chrome-user-data',
        'data-path' => sys_get_temp_dir() . '/chrome-data',
        'disk-cache-dir' => sys_get_temp_dir() . '/chrome-cache',
        'js-flags' => '--max-old-space-size=256',
    ]);

            // === PROD / FORGE: setează binarele și calea Chrome ===
            if (!defined('LOCAL_WP')) {
                // Dacă ai Node 18 instalat prin nvm, de-comentează liniile de mai jos
                // (altfel lasă-le pe cele cu v14)
                // $bshot->setNodeBinary('/home/forge/.nvm/versions/node/v18.20.3/bin/node');
                // $bshot->setNpmBinary('/home/forge/.nvm/versions/node/v18.20.3/bin/npm');

                $bshot->setNodeBinary('/home/forge/.nvm/versions/node/v20.18.0/bin/node');
                $bshot->setNpmBinary('/home/forge/.nvm/versions/node/v20.18.0/bin/npm');

                // Alege calea către Chrome/Chromium care există
                $chromePaths = [
                    '/usr/bin/chromium',
                ];
                foreach ($chromePaths as $path) {
                    if (is_executable($path)) {
                        $bshot->setChromePath($path);
                        break;
                    }
                }
            }

            $bshot->waitUntilNetworkIdle();

            // Obține imaginea
            $base64Image = $bshot->base64Screenshot();

            // dd($bshot->bodyHtml());

            $id = $this->save_image($base64Image, 'og_'.$this->post->id());

            update_post_meta($this->post->id(), 'og_image', $id);
        } catch (Exception $exception) {
            dd($exception);
            return $exception;
        }
    }

    private function save_image($base64_img, $title)
    {

        // Upload dir.
        $upload_dir  = wp_upload_dir();
        $upload_path = str_replace('/', DIRECTORY_SEPARATOR, $upload_dir['path']) . DIRECTORY_SEPARATOR;

        $img             = str_replace('data:image/jpeg;base64,', '', $base64_img);
        $img             = str_replace(' ', '+', $img);
        $decoded         = base64_decode($img);
        $filename        = $title . '.png';
        $file_type       = 'image/png';
        $hashed_filename = md5($filename . microtime()) . '_' . $filename;

        // Save the image in the uploads directory.
        $upload_file = file_put_contents($upload_path . $hashed_filename, $decoded);

        // $wp_filetype = wp_check_filetype( $hashed_filename, null );
        // dd($wp_filetype);

        $attachment = array(
        'post_mime_type' => $file_type,
        'post_title'     => preg_replace('/\.[^.]+$/', '', basename($hashed_filename)),
        'post_content'   => '',
        'post_status'    => 'inherit',
        'guid'           => $upload_dir['url'] . '/' . basename($hashed_filename)
    );

        $attach_id = wp_insert_attachment($attachment, $upload_dir['path'] . '/' . $hashed_filename);

        if (! function_exists('wp_crop_image')) {
            include(ABSPATH . 'wp-admin/includes/image.php');
        }

        $attachment_meta = wp_generate_attachment_metadata($attach_id, $upload_path . $hashed_filename);
        wp_update_attachment_metadata($attach_id, $attachment_meta);

        return $attach_id;
    }
}
