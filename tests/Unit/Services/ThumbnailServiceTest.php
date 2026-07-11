<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services;

use App\Services\ThumbnailService;
use PHPUnit\Framework\TestCase;

final class ThumbnailServiceTest extends TestCase
{
    private ThumbnailService $service;

    protected function setUp(): void
    {
        $this->service = new ThumbnailService();
    }

    public function testForListImage(): void
    {
        $file = (object) ['type_file' => 'image/png', 'link_file' => 'https://x/i.png', 'id_folder_file' => 1];

        $html = $this->service->forList($file);

        $this->assertStringContainsString('<img src="https://x/i.png"', $html);
        $this->assertStringContainsString('width:100px', $html);
    }

    public function testForListVideoMp4(): void
    {
        $file = (object) ['type_file' => 'video/mp4', 'link_file' => 'https://x/v.mp4', 'id_folder_file' => 1];

        $html = $this->service->forList($file);

        $this->assertStringContainsString('<video', $html);
        $this->assertStringContainsString('https://x/v.mp4', $html);
    }

    public function testForListVideoVimeoFolder(): void
    {
        $file = (object) [
            'type_file' => 'video/vimeo',
            'link_file' => 'https://vimeo.com/123',
            'id_folder_file' => 4,
            'thumbnail_vimeo_file' => 'https://x/thumb.jpg',
        ];

        $html = $this->service->forList($file);

        $this->assertStringContainsString('<img src="https://x/thumb.jpg"', $html);
    }

    public function testForListAudioFallsBackToMultimedia(): void
    {
        $file = (object) ['type_file' => 'audio/mpeg', 'link_file' => 'x.mp3', 'id_folder_file' => 1];

        $html = $this->service->forList($file);

        $this->assertStringContainsString('multimedia.png', $html);
    }

    public function testForListPdfExtension(): void
    {
        $file = (object) ['type_file' => 'application/pdf', 'link_file' => 'x.pdf', 'id_folder_file' => 1];

        $html = $this->service->forList($file);

        $this->assertStringContainsString('pdf.jpeg', $html);
    }

    public function testForListZipExtension(): void
    {
        $file = (object) ['type_file' => 'application/zip', 'link_file' => 'x.zip', 'id_folder_file' => 1];

        $html = $this->service->forList($file);

        $this->assertStringContainsString('zip.jpg', $html);
    }

    public function testForGridUsesCardClasses(): void
    {
        $file = (object) ['type_file' => 'image/png', 'link_file' => 'https://x/i.png', 'id_folder_file' => 1];

        $html = $this->service->forGrid($file);

        $this->assertStringContainsString('card-img-top w-100', $html);
        $this->assertStringNotContainsString('width:100px', $html);
    }
}
