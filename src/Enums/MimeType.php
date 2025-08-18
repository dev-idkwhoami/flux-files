<?php

namespace Idkwhoami\FluxFiles\Enums;

enum MimeType: string
{
    case IMAGE_JPEG = 'image/jpeg';
    case IMAGE_PNG = 'image/png';
    case IMAGE_GIF = 'image/gif';
    case IMAGE_WEBP = 'image/webp';
    case IMAGE_SVG = 'image/svg+xml';

    case APPLICATION_PDF = 'application/pdf';
    case APPLICATION_MSWORD = 'application/msword';
    case APPLICATION_DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    case APPLICATION_EXCEL = 'application/vnd.ms-excel';
    case APPLICATION_XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    case APPLICATION_POWERPOINT = 'application/vnd.ms-powerpoint';
    case APPLICATION_PPTX = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';

    case TEXT_PLAIN = 'text/plain';
    case TEXT_RTF = 'text/rtf';
    case TEXT_CSV = 'text/csv';

    case AUDIO_MPEG = 'audio/mpeg';
    case AUDIO_WAV = 'audio/wav';
    case AUDIO_OGG = 'audio/ogg';
    case AUDIO_FLAC = 'audio/flac';

    case VIDEO_MP4 = 'video/mp4';
    case VIDEO_AVI = 'video/x-msvideo';
    case VIDEO_QUICKTIME = 'video/quicktime';
    case VIDEO_WMV = 'video/x-ms-wmv';
    case VIDEO_FLV = 'video/x-flv';

    case APPLICATION_ZIP = 'application/zip';
    case APPLICATION_RAR = 'application/x-rar-compressed';
    case APPLICATION_7Z = 'application/x-7z-compressed';
    case APPLICATION_TAR = 'application/x-tar';
    case APPLICATION_GZIP = 'application/gzip';

    case APPLICATION_MSDOWNLOAD = 'application/x-msdownload';
    case APPLICATION_EXECUTABLE = 'application/x-executable';
    case APPLICATION_DOSEXEC = 'application/x-dosexec';
    case APPLICATION_WINEXE = 'application/x-winexe';
    case TEXT_PHP = 'text/x-php';
    case APPLICATION_PHP = 'application/x-php';
    case APPLICATION_HTTPD_PHP = 'application/x-httpd-php';
    case APPLICATION_JAVASCRIPT = 'application/javascript';
    case TEXT_JAVASCRIPT = 'text/javascript';

    public static function imageMimeTypes(): array
    {
        return [
            self::IMAGE_JPEG->value,
            self::IMAGE_PNG->value,
            self::IMAGE_GIF->value,
            self::IMAGE_WEBP->value,
            self::IMAGE_SVG->value,
        ];
    }

    public static function documentMimeTypes(): array
    {
        return [
            self::APPLICATION_PDF->value,
            self::APPLICATION_MSWORD->value,
            self::APPLICATION_DOCX->value,
            self::APPLICATION_EXCEL->value,
            self::APPLICATION_XLSX->value,
            self::APPLICATION_POWERPOINT->value,
            self::APPLICATION_PPTX->value,
            self::TEXT_PLAIN->value,
            self::TEXT_RTF->value,
            self::TEXT_CSV->value,
        ];
    }

    public static function audioMimeTypes(): array
    {
        return [
            self::AUDIO_MPEG->value,
            self::AUDIO_WAV->value,
            self::AUDIO_OGG->value,
            self::AUDIO_FLAC->value,
        ];
    }

    public static function videoMimeTypes(): array
    {
        return [
            self::VIDEO_MP4->value,
            self::VIDEO_AVI->value,
            self::VIDEO_QUICKTIME->value,
            self::VIDEO_WMV->value,
            self::VIDEO_FLV->value,
        ];
    }

    public static function archiveMimeTypes(): array
    {
        return [
            self::APPLICATION_ZIP->value,
            self::APPLICATION_RAR->value,
            self::APPLICATION_7Z->value,
            self::APPLICATION_TAR->value,
            self::APPLICATION_GZIP->value,
        ];
    }

    public static function allowedMimeTypes(): array
    {
        return [
            ...self::imageMimeTypes(),
            ...self::documentMimeTypes(),
            ...self::audioMimeTypes(),
            ...self::videoMimeTypes(),
            ...self::archiveMimeTypes(),
        ];
    }

    public static function blockedMimeTypes(): array
    {
        return [
            self::APPLICATION_MSDOWNLOAD->value,
            self::APPLICATION_EXECUTABLE->value,
            self::APPLICATION_DOSEXEC->value,
            self::APPLICATION_WINEXE->value,
            self::TEXT_PHP->value,
            self::APPLICATION_PHP->value,
            self::APPLICATION_HTTPD_PHP->value,
            self::APPLICATION_JAVASCRIPT->value,
            self::TEXT_JAVASCRIPT->value,
        ];
    }
}
