<?php
/**
 * Cliente de la API de Unsplash: busca fotos y las importa a la Media Library.
 *
 * @package WPRealEstate\DemoData
 */

namespace WPRealEstate\DemoData;

defined('ABSPATH') || exit;

class UnsplashClient
{
    private string $apiKey;

    private bool $available;

    public function __construct()
    {
        $this->apiKey = get_option('wpre_unsplash_key', '');
        $this->available = !empty($this->apiKey);
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    /**
     * @return int[] IDs de los adjuntos creados.
     */
    public function importListingPhotos(string $query, int $count, int $postId): array
    {
        if (!$this->available) {
            return [];
        }

        $attachmentIds = [];
        foreach ($this->searchPhotos($query, $count) as $photo) {
            $id = $this->importPhoto($photo, $postId);
            if ($id) {
                $attachmentIds[] = $id;
            }
        }

        return $attachmentIds;
    }

    public function importAgentPortrait(string $query, int $postId): int
    {
        if (!$this->available) {
            return 0;
        }

        $photos = $this->searchPhotos($query, 1);
        if (empty($photos)) {
            return 0;
        }

        return $this->importPhoto($photos[0], $postId) ?? 0;
    }

    private function searchPhotos(string $query, int $count): array
    {
        $url = add_query_arg([
            'query'       => $query,
            'per_page'    => $count,
            'orientation' => 'landscape',
        ], 'https://api.unsplash.com/search/photos');

        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization'  => 'Client-ID ' . $this->apiKey,
                'Accept-Version' => 'v1',
            ],
            'timeout' => 15,
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return [];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body['results'] ?? [];
    }

    private function importPhoto(array $photo, int $postId): ?int
    {
        $url = $photo['urls']['regular'] ?? $photo['urls']['small'] ?? '';
        if (empty($url)) {
            return null;
        }

        $description = $photo['description'] ?? $photo['alt_description'] ?? '';
        $author = $photo['user']['name'] ?? 'Unsplash';

        if (!function_exists('media_handle_sideload')) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $tmpFile = download_url($url, 30);
        if (is_wp_error($tmpFile)) {
            return null;
        }

        $fileArray = [
            'name'     => 'wp-real-estate-' . uniqid() . '.jpg',
            'tmp_name' => $tmpFile,
        ];

        $attachmentId = media_handle_sideload($fileArray, $postId, $description);

        if (is_wp_error($attachmentId)) {
            @unlink($fileArray['tmp_name']);
            return null;
        }

        update_post_meta($attachmentId, '_wpre_demo_content', '1');
        update_post_meta($attachmentId, '_wpre_photo_credit', sanitize_text_field($author));

        return $attachmentId;
    }
}
