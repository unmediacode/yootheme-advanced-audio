<?php

namespace YOOtheme\AdvancedAudio;

class Updater
{
    private $plugin_slug;
    private $version;
    private $cache_key;
    private $cache_allowed;
    private $github_user;
    private $github_repo;

    public function __construct($plugin_slug, $version, $github_user, $github_repo)
    {
        $this->plugin_slug = $plugin_slug;
        $this->version = $version;
        $this->cache_key = 'ytaa_updater_' . $plugin_slug;
        $this->cache_allowed = false;
        $this->github_user = $github_user;
        $this->github_repo = $github_repo;

        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_update']);
        add_filter('plugins_api', [$this, 'check_info'], 10, 3);
    }

    public function check_update($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $remote = $this->request();

        if (
            $remote
            && version_compare($this->version, $remote->version, '<')
            && version_compare($remote->requires, get_bloginfo('version'), '<=')
            && version_compare($remote->requires_php, PHP_VERSION, '<=')
        ) {
            $res = new \stdClass();
            $res->slug = $this->plugin_slug;
            $res->plugin = $this->plugin_slug . '/' . $this->plugin_slug . '.php';
            $res->new_version = $remote->version;
            $res->tested = $remote->tested;
            $res->package = $remote->download_url;
            
            $transient->response[$res->plugin] = $res;
        }

        return $transient;
    }

    public function check_info($false, $action, $arg)
    {
        if ($action !== 'plugin_information') {
            return $false;
        }

        if (!isset($arg->slug) || $arg->slug !== $this->plugin_slug) {
            return $false;
        }

        $remote = $this->request();

        if (!$remote) {
            return $false;
        }

        $res = new \stdClass();
        $res->name = $remote->name;
        $res->slug = $this->plugin_slug;
        $res->version = $remote->version;
        $res->tested = $remote->tested;
        $res->requires = $remote->requires;
        $res->author = $remote->author;
        $res->author_profile = $remote->author_profile;
        $res->download_link = $remote->download_url;
        $res->trunk = $remote->download_url;
        $res->requires_php = $remote->requires_php;
        $res->last_updated = $remote->last_updated;
        $res->sections = [
            'description' => $remote->sections['description'],
            'installation' => $remote->sections['installation'],
            'changelog' => $remote->sections['changelog'],
        ];

        return $res;
    }

    public function request()
    {
        $remote = get_transient($this->cache_key);

        if (false === $remote || !$this->cache_allowed) {
            $remote = $this->request_github();
            set_transient($this->cache_key, $remote, DAY_IN_SECONDS);
        }

        return $remote;
    }

    private function request_github()
    {
        $response = wp_remote_get(
            "https://api.github.com/repos/{$this->github_user}/{$this->github_repo}/releases/latest",
            [
                'headers' => [
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url')
                ]
            ]
        );

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response));

        if (empty($data)) {
            return false;
        }

        $remote = new \stdClass();
        $remote->name = $data->name;
        $remote->version = ltrim($data->tag_name, 'v');
        $remote->last_updated = $data->published_at;
        $remote->download_url = $data->zipball_url;
        $remote->author = $data->author->login;
        $remote->author_profile = $data->author->html_url;
        
        // Extract descriptions from body
        $parsed_body = $this->parse_body($data->body);
        $remote->sections = [
            'description' => $parsed_body['description'] ?? 'Plugin description',
            'installation' => $parsed_body['installation'] ?? 'Installation instructions',
            'changelog' => $parsed_body['changelog'] ?? 'Changelog',
        ];

        // Default requirements (should ideally be parsed from a file in the release or readme)
        $remote->requires = '5.0';
        $remote->requires_php = '7.2';
        $remote->tested = get_bloginfo('version');

        return $remote;
    }

    private function parse_body($body)
    {
        // Simple parser to split body by headers
        // Assumes Markdown headers like ## Description, ## Changelog
        $sections = [];
        $current_section = 'description';
        $lines = explode("\n", $body);
        
        foreach ($lines as $line) {
            if (preg_match('/^##\s+(.+)/', $line, $matches)) {
                $current_section = strtolower(trim($matches[1]));
                continue;
            }
            
            if (!isset($sections[$current_section])) {
                $sections[$current_section] = '';
            }
            
            $sections[$current_section] .= $line . "\n";
        }
        
        // Convert Markdown to HTML (basic)
        foreach ($sections as &$content) {
            $content = wpautop($content);
        }

        return $sections;
    }
}
