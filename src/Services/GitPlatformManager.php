<?php

namespace ThisIsDevelopment\GitManager\Services;

use Modules\Core\Helpers\ConfigHelper;
use ThisIsDevelopment\GitManager\Contracts\GitPlatformInterface;

class GitPlatformManager
{
    /**
     * @var ConfigHelper
     */
    private $config;

    public function __construct(ConfigHelper $config)
    {
        $this->config = $config;
    }

    public function platform($name): GitPlatformInterface
    {
        $config = $this->config->getFromModule($this, "platforms.{$name}");
        $config['name'] = $name;

        return app($config['type'], ['config' => $config]);
    }
}
