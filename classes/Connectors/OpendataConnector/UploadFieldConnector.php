<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector;

use eZClusterFileHandler;
use eZDebug;
use eZDFSFileHandler;
use eZDir;
use eZExecution;
use eZFSFileHandler;
use eZINI;
use eZMimeType;
use eZSys;
use eZUser;
use SplFileObject;
use UploadHandler;

abstract class UploadFieldConnector extends FieldConnector implements CleanableFieldConnectorInterface
{
    abstract protected function getUploadParamNameSuffix();

    public function __construct($attribute, $class, $helper)
    {
        parent::__construct($attribute, $class, $helper);
    }

    public function handleUpload($paramNamePrefix = null)
    {
        if ($this->getHelper()->hasParameter('preview')) {
            return $this->doPreview();

        } elseif ($this->getHelper()->hasParameter('delete')) {
            return $this->doDelete();

        } else {
            return $this->doUpload($paramNamePrefix);
        }
    }

    /**
     * @param SplFileObject[] $fileObjectList
     */
    public function insertFiles($fileObjectList)
    {
        $files = [];
        foreach ($fileObjectList as $file){

            $filePath = $this->getUploadDir() . $file->getBasename();
            (new eZFSFileHandler($filePath))->storeContents(file_get_contents($file->getRealPath()));

            $tempFileCheck = file_exists($this->getUploadDir() . $file->getBasename());

            if ($this->isImage($this->getUploadDir() . $file->getBasename())) {
                if (!is_dir($this->getUploadDir() . 'thumbnail')){
                    eZDir::mkdir($this->getUploadDir() . 'thumbnail');
                }
                $thumbnailPath = $this->getUploadDir() . 'thumbnail/' . $file->getBasename();
                $cmd = 'convert ' . escapeshellarg($filePath) . ' -auto-orient -coalesce  -resize ' . escapeshellarg('80X80^') . ' -gravity center  -crop ' . escapeshellarg('80X80+0+0') . ' +repage ' . escapeshellarg($thumbnailPath);
                exec($cmd, $output, $error);
                if ($error) {
                    eZDebug::writeError(implode('\n', $output), __METHOD__);
                }
            }

            $files[] = array(
                'id' => uniqid($file->getBasename()),
                'name' => $file->getBasename(),
                'size' => $file->getSize(),
                'url' => $this->getServiceUrl('upload', array('preview' => $file->getBasename())),
                'thumbnailUrl' => $this->getThumbnailUrl($file->getBasename()),
                'deleteUrl' => $this->getServiceUrl('upload', array('delete' => $file->getBasename())),
                'deleteType' => "GET",
                'tempFileCheck' => $tempFileCheck,
            );
        }

        return array('files' => $files);
    }

    protected function getThumbnailUrl($filename)
    {
        $thumbnailUrl = $this->getServiceUrl('upload', ['preview' => $filename]);
        $filePath = $this->getUploadDir() . $filename;
        if (!$this->isImage($filePath)) {
            $thumbnailUrl = $this->getIconImageBase64Data($filePath);
        }

        return $thumbnailUrl;
    }

    protected function calculateUploadParamName($paramNamePrefix)
    {
        $paramName = $this->getIdentifier() . $this->getUploadParamNameSuffix();
        if ($paramNamePrefix) {
            $fileNames = array_keys($_FILES);
            foreach ($fileNames as $fileName) {
                if (strpos($fileName, $paramNamePrefix) === 0 && strpos($fileName, $paramName) !== false) {
                    $paramName = $fileName;
                }
            }
        }

        return $paramName;
    }

    protected function doUpload($paramNamePrefix)
    {
        $paramName = $this->calculateUploadParamName($paramNamePrefix);
        $options = array();
        $options['upload_dir'] = $this->getUploadDir();
        $options['download_via_php'] = true;
        $options['param_name'] = $paramName;

        $uploadHandler = new UploadHandler($options, false);
        $data = $uploadHandler->post(false);
        $files = array();
        foreach ($data[$options['param_name']] as $file) {

            $thumbnailUrl = $this->getThumbnailUrl($file->name);

            $tempFileCheck = file_exists($this->getUploadDir() . $file->name);

            $files[] = array(
                'id' => uniqid($file->name),
                'name' => $file->name,
                'size' => $file->size,
                'url' => $this->getServiceUrl('upload', array('preview' => $file->name)),
                'thumbnailUrl' => $thumbnailUrl,
                'deleteUrl' => $this->getServiceUrl('upload', array('delete' => $file->name)),
                'deleteType' => "GET",
                'tempFileCheck' => $tempFileCheck,
            );
        }

        return array('files' => $files);
    }

    protected function doDelete()
    {
        $fileName = $this->getHelper()->getParameter('delete');

        $filePath = $this->getUploadDir() . $fileName;
        $file = new eZFSFileHandler($filePath);
        if ($file->exists()) {
            $file->delete();
        }

        $filePath = $this->getUploadDir() . 'thumbnail';
        $file = new eZFSFileHandler($filePath);
        if ($file->exists()) {
            $file->delete();
        }

        return array(
            'files' => array(
                array(
                    $fileName => true,
                ),
            ),
        );
    }

    protected function doPreview()
    {
        $fileName = $this->getHelper()->getParameter('preview');
        $filePath = $this->getUploadDir() . $fileName;

        if ($this->isImage($filePath)) {
            $filePath = $this->getUploadDir() . 'thumbnail/' . $fileName;
        }

        $file = new eZFSFileHandler($filePath);

        if ($file->exists()) {
            $mime = eZMimeType::findByURL($filePath);

            header('X-Content-Type-Options: nosniff');
            header('Content-Disposition: inline; filename="' . $fileName . '"');
            header('Content-Length: ' . $file->size());
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s T', $file->mtime()));
            if ($mime['name']) {
                header('Content-Type: ' . $mime['name']);
            }

            echo $file->fetchContents();
            eZExecution::cleanExit();
        }

        return false;
    }

    protected function getUploadDir()
    {
        $directory = md5(
            eZUser::currentUserID() . $this->class->attribute('identifier') . $this->getIdentifier() . $this->getUploadParamNameSuffix());

        $uploadDir = eZSys::cacheDirectory() . '/fileupload/' . $directory . '/';
        $fileHandler = eZClusterFileHandler::instance();
        if ($fileHandler instanceof eZDFSFileHandler) {
            $mountPointPath = eZINI::instance('file.ini')->variable('eZDFSClusteringSettings', 'MountPointPath');
            $uploadDir = rtrim($mountPointPath, '/') . '/' . $uploadDir;
        }

        eZDir::mkdir($uploadDir, false, true);

        return $uploadDir;
    }

    protected function isImage($filePath)
    {
        $mime = eZMimeType::findByURL($filePath);
        [$group, ] = explode('/', $mime['name']);

        return $group == 'image';
    }

    public function cleanup()
    {
        $filePath = $this->getUploadDir();
        $file = new eZFSFileHandler($filePath);
        if ($file->exists()) {
            $file->delete();
        }
    }

    protected function getIconImageBase64Data($filePath)
    {
        $mime = \eZMimeType::findByURL($filePath);
        return 'data:image/png;base64,' . base64_encode(file_get_contents($this->getIconByMimeType($mime['name'])));
    }

    private function getIconByMimeType($mimeName, $useFullPath = true, $size = '32x32')
    {
        $wtiOperator = new \eZWordToImageOperator();
        $ini = \eZINI::instance('icon.ini');
        $repository = $ini->variable('IconSettings', 'Repository');
        $theme = $ini->variable('IconSettings', 'Theme');
        $themeINI = \eZINI::instance('icon.ini', $repository . '/' . $theme);
        $icon = $wtiOperator->iconGroupMapping(
            $ini,
            $themeINI,
            'MimeIcons',
            'MimeMap',
            strtolower($mimeName)
        );
        $iconPath = '/' . $repository . '/' . $theme;
        $iconPath .= '/' . $size;
        $iconPath .= '/' . $icon;
        $siteDir = '';
        if ($useFullPath) {
            $siteDir = rtrim(str_replace('index.php', '', \eZSys::siteDir()), '\/');
        }
        return $siteDir . $iconPath;
    }
}
