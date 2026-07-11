<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Renderiza miniaturas de archivos para listas y cuadrículas.
 */
final class ThumbnailService
{
    public const VARIANT_LIST = 'list';
    public const VARIANT_GRID = 'grid';

    private const LIST_ATTRS = 'class="rounded" style="width:100px; height:100px; object-fit: cover; object-position: center;"';
    private const GRID_ATTRS = 'class="rounded card-img-top w-100"';

    public function forList(object $file): string
    {
        return $this->render($file, self::VARIANT_LIST);
    }

    public function forGrid(object $file): string
    {
        return $this->render($file, self::VARIANT_GRID);
    }

    private function render(object $file, string $variant): string
    {
        $attrs = $variant === self::VARIANT_GRID ? self::GRID_ATTRS : self::LIST_ATTRS;
        [$category, $extension] = explode('/', $file->type_file);

        return match ($category) {
            'image' => '<img src="' . $file->link_file . '" ' . $attrs . '>',

            'audio' => '<img src="/views/assets/img/multimedia.png" ' . $attrs . '>',

            'video' => $file->id_folder_file == 4
                ? '<img src="' . $file->thumbnail_vimeo_file . '" ' . $attrs . '>'
                : ($extension === 'mp4'
                    ? '<video ' . $attrs . '><source src="' . $file->link_file . '" type="' . $file->type_file . '"></video>'
                    : '<img src="/views/assets/img/multimedia.png" ' . $attrs . '>'),

            default => match ($extension) {
                'pdf' => '<img src="/views/assets/img/pdf.jpeg" ' . $attrs . '>',
                'zip' => '<img src="/views/assets/img/zip.jpg" ' . $attrs . '>',
                default => '<img src="/views/assets/img/multimedia.png" ' . $attrs . '>',
            },
        };
    }
}
