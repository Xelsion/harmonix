<?php

namespace system\classes\responses;

use system\abstracts\AResponse;

class FileResponse extends AResponse {

    // the default status for html status headers
    public int $status_code = 200;

    // the full path of the download file
    public string $file_path = "";

    /**
     * @inherite
     */
    public function setHeaders(): void {
        // required for IE, otherwise Content-disposition is ignored
        if(ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }

        $file_extension = strtolower(substr(strrchr($this->file_path,"."),1));
        $file_type = match ( $file_extension ) {
            "pdf" => "application/pdf",
            "exe" => "application/octet-stream",
            "zip" => "application/zip",
            "doc" => "application/msword",
            "xls" => "application/vnd.ms-excel",
            "ppt" => "application/vnd.ms-powerpoint",
            "gif" => "image/gif",
            "png" => "image/png",
            "jpeg", "jpg" => "image/jpg",
            default => "application/octet-stream",
        };
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        header("Content-Type: $file_type");
        header("Content-Disposition: attachment; filename=\"".basename($this->file_path)."\";" );
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".filesize($this->file_path));

        switch( $this->status_code ) {
            case 400:
                header("HTTP/1.1 400 Bad Request");
                break;
            case 401:
                header("HTTP/1.1 401 Unauthorized");
                break;
            case 403:
                header("HTTP/1.1 403 Forbidden");
                break;
            case 404:
                header("HTTP/1.1 404 Not Found");
                break;
            case 500:
                header("HTTP/1.1 500 Internal Server Error");
                break;
            default:
                header("HTTP/1.1 200 OK");
        }
    }

}
