<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Utils;

class MimeTypes
{
    /** @var array<string, string> */
    private static $descriptions = [
        'image/png' => 'Obrázek PNG',
        'image/bmp' => 'Obrázek BMP',
        'image/gif' => 'Obrázek GIF',
        'image/jpeg' => 'Obrázek JPG',
        'image/svg+xml' => 'Obrázek SVG',
        'text/html' => 'Stránka HTML',
        'text/plain' => 'Soubor TXT',
        'application/pdf' => 'Dokument PDF',
        'application/php' => 'Skript PHP',
        'application/zip' => 'Archiv ZIP',
        'application/rar' => 'Archiv RAR',
        'video/x-msvideo' => 'Video AVI',
        'video/mpeg' => 'Video MPEG',
        'video/ogg' => 'Video OGG',
        'audio/mpeg' => 'Audio MP3',
        'audio/ogg' => 'Audio OGG',
        'audio/wav' => 'Audio WAV',
        'application/msword' => 'Dokument DOC',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Dokument DOCX',
        'application/vnd.oasis.opendocument.text' => 'Dokument ODT',
        'application/vnd.ms-excel' => 'Dokument XLS',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Dokument XLSX',
        'application/vnd.oasis.opendocument.spreadsheet' => 'Dokument ODS',
        'application/vnd.ms-powerpoint' => 'Dokument PPT',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'Dokument PPTX',
        'application/vnd.oasis.opendocument.presentation' => 'Dokument ODP',
    ];
    public static function getName(string $mimeType): string
    {
        if (array_key_exists($mimeType, self::$descriptions)) {
            return self::$descriptions[$mimeType];
        }
        return 'Neznámý';
    }
}
