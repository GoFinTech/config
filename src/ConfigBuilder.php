<?php

namespace GoFinTech\Config;

class ConfigBuilder
{
    private $values;

    public static function buildDefault() {
        $builder = new ConfigBuilder();

        // This cache does not refresh but it does not prevent implementing it in future
        $cacheFile = $_ENV['GFT_CONFIG_CACHE'] ?? null;

        if ($cacheFile && file_exists($cacheFile)) {
            $builder->loadCache($cacheFile);
            return $builder->build();
        }

        if (isset($_ENV['GFT_CONFIG_LOCAL']))
            $builder->loadConfigFile($_ENV['GFT_CONFIG_LOCAL']);
        else
            $builder->loadConfigFile("config.ini", true);

        if (isset($_ENV['GFT_CONFIG_GLOBAL']))
            $builder->loadConfigFile($_ENV['GFT_CONFIG_GLOBAL']);

        $builder->loadEnvironment();

        if ($cacheFile) {
            $builder->writeCache($cacheFile);
        }

        return $builder->build();
    }

    public function __construct()
    {
        $this->values = [];
    }

    public function build(): Config
    {
        return new Config($this->values);
    }

    private function loadCache(string $fileName) : void
    {
        $this->values = json_decode(file_get_contents($fileName), true);
    }

    private function writeCache(string $fileName) : void
    {
        $tempFileName = tempnam(sys_get_temp_dir(), 'cfgcache');
        if (file_put_contents($tempFileName, json_encode($this->values))) {
            rename($tempFileName, $fileName);
        }
        else {
            unlink($tempFileName);
        }
    }

    public function loadConfigFile(string $fileName, bool $required = true): ConfigBuilder
    {
        if (!$required && !file_exists($fileName))
            return $this;

        $values = parse_ini_file($fileName);
        if ($values === false) {
            throw new \InvalidArgumentException("ConfigBuilder.loadConfigFile: can't read file $fileName");
        }
        $this->values = array_merge($this->values, $values);
        return $this;
    }

    public function loadEnvironment(): ConfigBuilder
    {
        $keys = array_keys($this->values);
        foreach ($keys as $key) {
            if (array_key_exists($key, $_ENV))
                $this->values[$key] = $_ENV[$key];
        }
        return $this;
    }
}
