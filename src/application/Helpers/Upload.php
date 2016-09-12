<?php
namespace App\Helpers;

class Upload
{

    protected $file = [];
    protected $fileName = '';
    protected $allowedExtensions = [];
    protected $maximumSize = 0;
    protected $uploadDirectory = '';
    protected $errors = [];

    public function __construct($file = null)
    {
        $this->file = $file;
    }

    public function setFile($file = [])
    {
        $this->file = $file;
    }

    public function getFileName()
    {
        return $this->file['name'];
    }

    public function getNewFileName()
    {
        return $this->fileName . '.' . $this->getExtension();
    }

    public function setFileName($fileName = '')
    {
        $this->fileName = $fileName;
    }

    public function getExtension()
    {
        return pathInfo($this->getFileName(), PATHINFO_EXTENSION);
    }

    public function setAllowedExtensions($ext = [])
    {
        $this->allowedExtensions = $ext;
    }

    public function getTmpLocation()
    {
        return $this->file['tmp_name'];
    }

    public function getFileSize()
    {
        return $this->file['size'];
    }

    public function setMaximumFileSize($maxSize)
    {
        $this->maximumSize = $maxSize;
    }

    public function setUploadDir($path)
    {
        $this->uploadDirectory = $path;
    }

    protected function checkUploadErrors()
    {
        if ($this->file['error'] > 0) {
            $this->errors[] = 'The file could not be uploaded, please try again!';
        }
    }

    protected function checkExtension()
    {
        if (!in_array($this->getExtension(), $this->allowedExtensions)) {
            $this->errors[] = 'The file format is not compatible';
        }
    }

    protected function checkFileSize()
    {
        if ($this->getFileSize() > $this->maximumSize) {
            $this->errors[] = 'The uploaded file is to big!';
        }
    }

    protected function checkIfUploaded()
    {
        $fileName = $this->getNewFileName();

        if (empty($this->fileName)) {
            $fileName = $this->getFileName();
        }

        $path = $this->uploadDirectory . $fileName;

        if (!move_uploaded_file($this->getTmpLocation(), $path)) {
            $this->errors[] = 'The file could not be uploaded, please try again!';

            return false;
        }

        return true;
    }

    public function isUploaded()
    {
        $this->checkUploadErrors();
        $this->checkExtension();
        $this->checkFileSize();

        if (!count($this->getErrors())) {
            return $this->checkIfUploaded();
        }

        return false;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getFirstError()
    {
        if (isset($this->getErrors()[0])) {
            return $this->getErrors()[0];
        }
    }
}