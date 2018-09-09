<?php

namespace GoFinTech\Config;

class ConfigBuilder
{

    private $values;

    public function __construct(bool $preventDefault = false)
    {
        $this->values = [];
        if ($preventDefault)
            return;

        if (isset($_ENV['GFT_CONFIG_LOCAL']))
            $this->loadConfigFile($_ENV['GFT_CONFIG_LOCAL']);
        else
            $this->loadConfigFile("config.ini", true);

        if (isset($_ENV['GFT_CONFIG_GLOBAL']))
            $this->loadConfigFile($_ENV['GFT_CONFIG_GLOBAL']);

        $this->loadEnvironment();
    }

    public function build(): Config
    {
        return new Config($this->values);
    }

    public function loadConfigFile(string $fileName, bool $optional = false): ConfigBuilder
    {
        if ($optional && !file_exists($fileName))
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
