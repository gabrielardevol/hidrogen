<?php
namespace App\Command;

class CreateProductCommand
{
    public string $title;
    public string $description;
    public array $files;  // Array per a arxius

    public function __construct(string $title, string $description, array $files)
    {
        $this->title = $title;
        $this->description = $description;
        $this->files = $files;
    }
}
