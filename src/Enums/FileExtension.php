<?php

namespace Idkwhoami\FluxFiles\Enums;

enum FileExtension: string
{
    case JPG = 'jpg';
    case JPEG = 'jpeg';
    case PNG = 'png';
    case GIF = 'gif';
    case WEBP = 'webp';
    case SVG = 'svg';
    case PDF = 'pdf';
    case DOC = 'doc';
    case DOCX = 'docx';
    case XLS = 'xls';
    case XLSX = 'xlsx';
    case PPT = 'ppt';
    case PPTX = 'pptx';
    case TXT = 'txt';
    case RTF = 'rtf';
    case CSV = 'csv';
    case MP3 = 'mp3';
    case WAV = 'wav';
    case OGG = 'ogg';
    case FLAC = 'flac';
    case MP4 = 'mp4';
    case AVI = 'avi';
    case MOV = 'mov';
    case WMV = 'wmv';
    case FLV = 'flv';
    case ZIP = 'zip';
    case RAR = 'rar';
    case SEVEN_ZIP = '7z';
    case TAR = 'tar';
    case GZ = 'gz';

    public static function imageExtensions(): array
    {
        return [
            self::JPG->value,
            self::JPEG->value,
            self::PNG->value,
            self::GIF->value,
            self::WEBP->value,
            self::SVG->value,
        ];
    }

    public static function documentExtensions(): array
    {
        return [
            self::PDF->value,
            self::DOC->value,
            self::DOCX->value,
            self::XLS->value,
            self::XLSX->value,
            self::PPT->value,
            self::PPTX->value,
            self::TXT->value,
            self::RTF->value,
            self::CSV->value,
        ];
    }

    public static function audioExtensions(): array
    {
        return [
            self::MP3->value,
            self::WAV->value,
            self::OGG->value,
            self::FLAC->value,
        ];
    }

    public static function videoExtensions(): array
    {
        return [
            self::MP4->value,
            self::AVI->value,
            self::MOV->value,
            self::WMV->value,
            self::FLV->value,
        ];
    }

    public static function archiveExtensions(): array
    {
        return [
            self::ZIP->value,
            self::RAR->value,
            self::SEVEN_ZIP->value,
            self::TAR->value,
            self::GZ->value,
        ];
    }

    public static function allExtensions(): array
    {
        return [
            ...self::imageExtensions(),
            ...self::documentExtensions(),
            ...self::audioExtensions(),
            ...self::videoExtensions(),
            ...self::archiveExtensions(),
        ];
    }

    public static function executableExtensions(): array
    {
        return [
            'exe', 'bat', 'cmd', 'com', 'scr', 'pif', 'msi', 'php', 'asp', 'jsp'
        ];
    }
}
