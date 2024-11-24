<?php
class DC_Event_Cache {
    private $cache_group = 'dc_event_manager';

    public function get($key) {
        return wp_cache_get($key, $this->cache_group);
    }

    public function set($key, $value, $expiration = 3600) {
        wp_cache_set($key, $value, $this->cache_group, $expiration);
    }

    public function delete($key) {
        wp_cache_delete($key, $this->cache_group);
    }

    public function flush() {
        wp_cache_flush();
    }

    public function get_or_set($key, $callback, $expiration = 3600) {
        $cached_value = $this->get($key);

        if ($cached_value === false) {
            $value = $callback();
            $this->set($key, $value, $expiration);
            return $value;
        }

        return $cached_value;
    }
}