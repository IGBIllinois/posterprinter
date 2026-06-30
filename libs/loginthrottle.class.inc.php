<?php

class loginthrottle {
    private $cache_dir;
    private $max_attempts;
    private $lockout_seconds;

    public function __construct($max_attempts = 7, $lockout_seconds = 900) {
        $this->max_attempts = $max_attempts;
        $this->lockout_seconds = $lockout_seconds;
        $this->cache_dir = sys_get_temp_dir() . '/posterprinter_login_attempts';
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0700, true);
        }
    }

    private function get_file($ip) {
        // Use hash to avoid directory traversal with IPv6 addresses
        return $this->cache_dir . '/' . hash('sha256', $ip) . '.json';
    }

    private function get_record($ip) {
        $file = $this->get_file($ip);
        if (!file_exists($file)) {
            return array('attempts' => 0, 'last_attempt' => 0, 'locked_until' => 0);
        }
        $data = json_decode(file_get_contents($file), true);
        if (!is_array($data)) {
            return array('attempts' => 0, 'last_attempt' => 0, 'locked_until' => 0);
        }
        return $data;
    }

    private function save_record($ip, $record) {
        $file = $this->get_file($ip);
        file_put_contents($file, json_encode($record), LOCK_EX);
    }

    public function is_locked($ip) {
        $record = $this->get_record($ip);
        if ($record['locked_until'] > time()) {
            return true;
        }
        // If lockout has expired, reset
        if ($record['locked_until'] > 0 && $record['locked_until'] <= time()) {
            $this->reset($ip);
        }
        return false;
    }

    public function get_remaining_lockout($ip) {
        $record = $this->get_record($ip);
        if ($record['locked_until'] > time()) {
            return $record['locked_until'] - time();
        }
        return 0;
    }

    public function record_failure($ip) {
        $record = $this->get_record($ip);
        $record['attempts']++;
        $record['last_attempt'] = time();
        if ($record['attempts'] >= $this->max_attempts) {
            $record['locked_until'] = time() + $this->lockout_seconds;
        }
        $this->save_record($ip, $record);
        return $record['attempts'];
    }

    public function get_attempts($ip) {
        $record = $this->get_record($ip);
        return $record['attempts'];
    }

    public function reset($ip) {
        $file = $this->get_file($ip);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    // Clean up expired lockout files older than 1 hour
    public function cleanup() {
        $files = glob($this->cache_dir . '/*.json');
        if (!is_array($files)) {
            return;
        }
        foreach ($files as $file) {
            if (filemtime($file) < time() - 3600) {
                unlink($file);
            }
        }
    }
}

?>
