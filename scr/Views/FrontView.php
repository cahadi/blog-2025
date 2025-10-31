<?php
declare(strict_types=1);

namespace App\Views;

use App\Traits\Helper;

class FrontView
{
    use Helper;

    protected string $templatePath;

    public function __construct(string $theme = DEFAULT_THEMA)
    {
        $this->templatePath = RESOURCES_PATH . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR;
    }

    public function render(string $template, array $data = []): void
    {
        extract($data);
        include $this->templatePath . $template;
    }

    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }
}