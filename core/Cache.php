<?php

class Cache
{
    private $path = __DIR__ . '/../storage/cache/';

    public function __construct()
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }
    }

    public function get($key, $ttl = 60)
    {
        $file = $this->path . $key . '.json';

        if (!file_exists($file)) {
            return null;
        }

        $content = json_decode(file_get_contents($file), true);

        if (!$content) {
            return null;
        }

        $cacheTTL = $content['ttl'] ?? $ttl;

        if ((time() - $content['created_at']) > $cacheTTL) {

            unlink($file);

            return null;
        }

        return $content['data'];
    }

    public function set($key, $data, $ttl = 60)
    {
        $file = $this->path . $key . '.json';

        $payload = [
            'data' => $data,
            'created_at' => time(),
            'ttl' => $ttl,
            'meta' => [
                'cached_at' => date('Y-m-d H:i:s'),
                'memory_usage' => memory_get_usage(true)
            ]
        ];

        file_put_contents($file, json_encode($payload));

        return true;
    }

    public function clear($key)
    {
        $file = $this->path . $key . '.json';

        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function clearByPrefix($prefix)
    {
        $deleted = 0;

        foreach (glob($this->path . $prefix . '*.json') as $file) {

            if (unlink($file)) {
                $deleted++;
            }
        }

        return $deleted;
    }
}
// class Cache
// {
//     private $path = __DIR__ . '/../storage/cache/';

//     public function __construct()
//     {
//         if (!is_dir($this->path)) {
//             mkdir($this->path, 0777, true);
//         }
//     }

//     public function get($key, $ttl = 60)
//     {
//         $file = $this->path . $key . '.json';

//         if (!file_exists($file)) {
//             return [
//                 'success' => false,
//                 'message' => 'Cache not found',
//                 'data' => null
//             ];
//         }

//         $content = json_decode(file_get_contents($file), true);

//         if (!$content) {
//             return [
//                 'success' => false,
//                 'message' => 'Invalid cache data',
//                 'data' => null
//             ];
//         }

//         $cacheTTL = $content['ttl'] ?? $ttl;

//         if ((time() - $content['created_at']) > $cacheTTL) {

//             unlink($file);

//             return [
//                 'success' => false,
//                 'message' => 'Cache expired',
//                 'data' => null
//             ];
//         }

//         return [
//             'success' => true,
//             'message' => 'Cache loaded successfully',
//             'data' => $content['data'],
//             'meta' => [
//                 'created_at' => $content['created_at'],
//                 'ttl' => $cacheTTL,
//                 'expires_in' => $cacheTTL - (time() - $content['created_at'])
//             ]
//         ];
//     }

//     public function set($key, $data, $ttl = 60)
//     {
//         $file = $this->path . $key . '.json';

//         $payload = [
//             'success' => true,
//             'message' => 'Cache saved successfully',
//             'data' => $data,
//             'created_at' => time(),
//             'ttl' => $ttl
//         ];

//         file_put_contents($file, json_encode($payload));

//         return [
//             'success' => true,
//             'message' => 'Cache stored',
//             'file' => $file
//         ];
//     }

//     public function clearByPrefix($prefix)
//     {
//         $deleted = 0;

//         foreach (glob($this->path . $prefix . '*.json') as $file) {

//             if (unlink($file)) {
//                 $deleted++;
//             }
//         }

//         return [
//             'success' => true,
//             'message' => 'Cache cleared successfully',
//             'deleted_files' => $deleted
//         ];
//     }
// }
// class Cache
// {
//     private $path = __DIR__ . '/../storage/cache/';

//     public function get($key, $ttl = 60)
//     {
//         $file = $this->path . $key . '.json';

//         if (file_exists($file)) {

//             $content = json_decode(file_get_contents($file), true);

//             if (
//                 $content &&
//                 (time() - $content['created_at']) < $ttl
//             ) {
//                 return $content['data'];
//             }

//             unlink($file); // remove expired cache
//         }

//         return null;
//     }

//     public function set($key, $data, $ttl = 60)
//     {
//         $file = $this->path . $key . '.json';

//         $payload = [
//             'data' => $data,
//             'created_at' => time(),
//             'ttl' => $ttl
//         ];

//         file_put_contents($file, json_encode($payload));
//     }

//     public function clearByPrefix($prefix)
//     {
//         foreach (glob($this->path . $prefix . '*.json') as $file) {
//             unlink($file);
//         }
//     }
// }
// class Cache
// {
//     private $path = __DIR__ . '/../storage/cache/';

//     public function get($key, $ttl = 60)
//     {
//         $file = $this->path . md5($key) . '.json';

//         if (file_exists($file)) {
//             $content = json_decode(file_get_contents($file), true);

//             if ($content && (time() - $content['created_at']) < $ttl) {
//                 return $content['data'];
//             }
//         }

//         return null;
//     }

//     public function set($key, $data, $ttl = 60)
//     {
//         $file = $this->path . md5($key) . '.json';

//         $payload = [
//             'data' => $data,
//             'created_at' => time(),
//             'ttl' => $ttl
//         ];

//         file_put_contents($file, json_encode($payload));
//     }
//     public function clearByPrefix($prefix)
//     {
//         foreach (glob($this->path . '*.json') as $file) {
//             if (strpos($file, md5($prefix)) !== false) {
//                 unlink($file);
//             }
//         }
//     }
// }
?>
