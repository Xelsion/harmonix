<?php

namespace lib\core\enums;

enum MimeType {

	case _7Z;
	case ABW;
	case ARC;
	case AVI;
	case AVIF;
	case BMP;
	case BZ;
	case BZ2;
	case CDA;
	case CSH;
	case CSS;
	case CSV;
	case DOC;
	case DOCX;
	case EOT;
	case EXE;
	case GIF;
	case GZ;
	case HTM;
	case HTML;
	case ICO;
	case ICS;
	case JAR;
	case JS;
	case JPEG;
	case JPG;
	case JSON;
	case JSONLD;
	case MID;
	case MIDI;
	case MP3;
	case MP4;
	case MPEG;
	case MPKG;
	case ODP;
	case ODS;
	case ODT;
	case OGA;
	case OGV;
	case OGX;
	case OPUS;
	case OTF;
	case PDF;
	case PHP;
	case PNG;
	case PPT;
	case PPTX;
	case RAR;
	case RSS;
	case RTF;
	case SH;
	case SVG;
	case TAR;
	case TIF;
	case TIFF;
	case TS;
	case TTF;
	case TXT;
	case VSD;
	case WAV;
	case WEBA;
	case WEBM;
	case WEBP;
	case WOFF;
	case WOFF2;
	case XHTML;
	case XLS;
	case XSLT;
	case XML;
	case XUL;
	case ZIP;
	case BIN;
	case UNKNOWN;

	/**
	 * Returns the MimeType from a file extension
	 *
	 * @param string $extension
	 * @return MimeType
	 */
	public static function fromExtension(string $extension): MimeType {
		return match (strtolower($extension)) {
			"7z" => self::_7Z,
			"abw" => self::ABW,
			"arc" => self::ARC,
			"avi" => self::AVI,
			"avif" => self::AVIF,
			"bin" => self::BIN,
			"bmp" => self::BMP,
			"bz" => self::BZ,
			"bz2" => self::BZ2,
			"cda" => self::CDA,
			"csh" => self::CSH,
			"css" => self::CSS,
			"csv" => self::CSV,
			"doc" => self::DOC,
			"docx" => self::DOCX,
			"eot" => self::EOT,
			"exe" => self::EXE,
			"gif" => self::GIF,
			"gz" => self::GZ,
			"htm" => self::HTM,
			"html" => self::HTML,
			"ico" => self::ICO,
			"ics" => self::ICS,
			"jar" => self::JAR,
			"js" => self::JS,
			"jpg" => self::JPG,
			"jpeg" => self::JPEG,
			"json" => self::JSON,
			"jsonld" => self::JSONLD,
			"mid" => self::MID,
			"midi" => self::MIDI,
			"mp3" => self::MP3,
			"mp4" => self::MP4,
			"mpeg" => self::MPEG,
			"mpkg" => self::MPKG,
			"odp" => self::ODP,
			"ods" => self::ODS,
			"odt" => self::ODT,
			"oga" => self::OGA,
			"ogv" => self::OGV,
			"ogx" => self::OGX,
			"opus" => self::OPUS,
			"otf" => self::OTF,
			"pdf" => self::PDF,
			"php" => self::PHP,
			"png" => self::PNG,
			"ppt" => self::PPT,
			"pptx" => self::PPTX,
			"rar" => self::RAR,
			"rss" => self::RSS,
			"rtf" => self::RTF,
			"sh" => self::SH,
			"svg" => self::SVG,
			"tar" => self::TAR,
			"tif" => self::TIF,
			"tiff" => self::TIFF,
			"ts" => self::TS,
			"ttf" => self::TTF,
			"txt" => self::TXT,
			"vsd" => self::VSD,
			"wav" => self::WAV,
			"weba" => self::WEBA,
			"webm" => self::WEBM,
			"webp" => self::WEBP,
			"woff" => self::WOFF,
			"woff2" => self::WOFF2,
			"xhtml" => self::XHTML,
			"xls" => self::XLS,
			"xslt" => self::XSLT,
			"xml" => self::XML,
			"xul" => self::XUL,
			"zip" => self::ZIP,
			default => self::UNKNOWN
		};
	}

	/**
	 * Returns the MimeType as string
	 *
	 * @return string
	 */
	public function toString(): string {
		return match ($this) {
			self::_7Z => "application/x-7z-compressed",
			self::ABW => "application/x-abiword",
			self::ARC => "application/x-freearc",
			self::AVI => "video/x-msvideo",
			self::AVIF => "image/avif",
			self::BIN => "application/octet-stream",
			self::BMP => "image/bmp",
			self::BZ => "application/x-bzip",
			self::BZ2 => "application/x-bzip2",
			self::CDA => "application/x-cdf",
			self::CSH => "application/x-csh",
			self::CSS => "text/css",
			self::CSV => "text/csv",
			self::DOC => "application/msword",
			self::DOCX => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
			self::EOT => "application/vnd.ms-fontobject",
			self::EXE => "application/vnd.microsoft.portable-executable",
			self::GIF => "image/gif",
			self::GZ => "application/gzip",
			self::HTM, self::HTML => "text/html",
			self::ICO => "image/vnd.microsoft.icon",
			self::ICS => "text/calendar",
			self::JAR => "application/java-archive",
			self::JS => "application/javascript",
			self::JPG, self::JPEG => "image/jpg",
			self::JSON => "application/json",
			self::JSONLD => "application/ld+json",
			self::MID => "audio/midi",
			self::MIDI => "audio/x-midi",
			self::MP3 => "audio/mpeg",
			self::MP4 => "video/mp4",
			self::MPEG => "video/mpeg",
			self::MPKG => "application/vnd.apple.installer+xml",
			self::ODP => "application/vnd.oasis.opendocument.presentation",
			self::ODS => "application/vnd.oasis.opendocument.spreadsheet",
			self::ODT => "application/vnd.oasis.opendocument.text",
			self::OGA => "audio/ogg",
			self::OGV => "video/ogg",
			self::OGX => "application/ogg",
			self::OPUS => "audio/opus",
			self::OTF => "font/otf",
			self::PDF => "application/pdf",
			self::PHP => "application/x-httpd-php",
			self::PNG => "image/png",
			self::PPT => "application/vnd.ms-powerpoint",
			self::PPTX => "application/vnd.openxmlformats-officedocument.presentationml.presentation",
			self::RAR => "application/vnd.rar",
			self::RSS => "application/rss+xml",
			self::RTF => "application/rtf",
			self::SH => "application/x-sh",
			self::SVG => "image/svg+xml",
			self::TAR => "application/x-tar",
			self::TIF, self::TIFF => "image/tiff",
			self::TS => "video/mp2t",
			self::TTF => "font/ttf",
			self::TXT => "text/plain",
			self::VSD => "application/vnd.visio",
			self::WAV => "audio/wav",
			self::WEBA => "audio/webm",
			self::WEBM => "video/webm",
			self::WEBP => "image/webp",
			self::WOFF => "font/woff",
			self::WOFF2 => "font/woff2",
			self::XHTML => "application/xhtml+xml",
			self::XLS => "application/vnd.ms-excel",
			self::XSLT => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
			self::XML => "application/xml",
			self::XUL => "application/vnd.mozilla.xul+xml",
			self::ZIP => "application/zip",
			self::UNKNOWN => "unknown"
		};
	}

}