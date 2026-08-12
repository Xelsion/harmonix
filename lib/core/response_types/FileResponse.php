<?php

namespace lib\core\response_types;

use lib\core\abstracts\AResponse;
use lib\core\enums\HttpResponseCode;

/**
 * The FileResponse class
 * This class will handle responses of type file
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class FileResponse extends AResponse {

	// the default status for html status headers
	public HttpResponseCode $status_code = HttpResponseCode::Ok;

	// the full path of the download file
	public string $file_path = "";

	/**
	 * @inherite
	 */
	public function setHeaders(): void {
		// required for IE, otherwise Content-disposition is ignored
		if( ini_get('zlib.output_compression') ) {
			ini_set('zlib.output_compression', 'Off');
		}
		if( file_exists($this->file_path) ) {
			$file_extension = strtolower(substr(strrchr($this->file_path, "."), 1));
			$file_type = match ($file_extension) {
				"pdf" => "application/pdf",
				"zip" => "application/zip",
				"doc" => "application/msword",
				"xls" => "application/vnd.ms-excel",
				"ppt" => "application/vnd.ms-powerpoint",
				"gif" => "image/gif",
				"png" => "image/png",
				"jpeg", "jpg" => "image/jpg",
				default => "application/octet-stream",
			};
			header($this->status_code->toString());
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private", false);
			header("Content-Disposition: attachment; filename=\"" . basename($this->file_path) . "\";");
			header("Content-Transfer-Encoding: binary");
			header("Content-Type: $file_type");
			header("Content-Length: " . filesize($this->file_path));
		} else {
			header(HttpResponseCode::NotFound->toString());
		}
	}

}
