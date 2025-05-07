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
        $thumbnailUrl = $this->getServiceUrl('upload', array('preview' => $filename));
        if (!$this->isImage($this->getUploadDir() . $filename)) {
            $thumbnailUrl = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAF4AAABeCAYAAACq0qNuAAAKpGlDQ1BJQ0MgUHJvZmlsZQAASImVlwdQFFkax1/35EQaGIKEISfJGSTHIQiSQVSGGRiGMAwDQzIjiwquARURMKErIAqugbQGRBQDi4AC5gVZFJR1MQAqKtvAMdze1d3V/au+6l99/fr/vn79XtXXAJBvMfn8JFgCgGReuiDQ04UeHhFJxw0CCOABCWgDDJOVxncOCPAFiBauf9dkHzIa0X2DWa9/v/9fJcmOTWMBAAUgHMNOYyUjfAGJsyy+IB0AFBvJq2em82d5G8LSAqRAhMtnmTPPZ2c5Zp7b5sYEB7oi/BAAPJnJFHAAIP2O5OkZLA7iQ0YjbMxjc3kImyPswIpnIvOQkXtgaXJyyiwfRVgn5p98OH/zjBF5MpkcEc+/y5zwbtw0fhIz+/9cjv+t5CThwhxqSJDjBV6Bs/Mha1aVmOIjYl7Mcv8F5rLna5rleKFXyAKz0lwjF5jNdPNZYGFiiPMCMwWLz3LTGcELLEgJFPnzkpb7ivxjGSKOTXMPWuA4rgdjgXPig8MWOIMbunyB0xKDfBbHuIryAmGgqOY4gYfoHZPTFmtjMRfnSo8P9lqsIVxUDzvWzV2U54WIxvPTXUSe/KSAxfqTPEX5tIwg0bPpyAZb4ASmd8CiT4BofQAX+AEmYKXHZs3uK+Caws8WcDnx6XRn5JTE0hk8luFSuqmxiRUAs2du/pN+oM2dJYh2ZzGX2gKATQGS5CzmmOoANL0EgDq5mFN/j2yH3QBc7mIJBRnzudmtDjCACMSBNJAHykAd6AADYAosgR1wAu7AG/iDYBABVgMWiAfJQAAywTqwGeSDQrAb7Ael4Ag4DqrAGXAONIBL4Bq4Ce6CLtALnoABMAzegHEwCaYhCMJBFIgKyUMqkCakD5lC1pAD5A75QoFQBBQNcSAeJITWQVugQqgIKoWOQdXQz1ATdA26DXVDj6BBaBR6D32BUTAZloaVYC3YCLaGnWEfOBheBXPgVDgHzoN3wiVwBXwaroevwXfhXngAfgNPoACKhKKhVFEGKGuUK8ofFYmKQwlQG1AFqGJUBaoW1YxqR91HDaDGUJ/RWDQVTUcboO3QXugQNAudit6A3oEuRVeh69Ft6PvoQfQ4+juGglHE6GNsMQxMOIaDycTkY4oxJzEXMTcwvZhhzCQWi6VhtbFWWC9sBDYBuxa7A3sIW4dtwXZjh7ATOBxOHqePs8f545i4dFw+7iDuNO4qrgc3jPuEJ+FV8KZ4D3wknofPxRfjT+Gv4Hvwr/DTBAmCJsGW4E9gE7IJuwgnCM2Ee4RhwjRRkqhNtCcGExOIm4klxFriDeJT4gcSiaRGsiGtIHFJm0glpLOkW6RB0meyFFmP7EqOIgvJO8mV5BbyI/IHCoWiRXGiRFLSKTsp1ZTrlOeUT2JUMUMxhhhbbKNYmVi9WI/YW3GCuKa4s/hq8RzxYvHz4vfExyQIEloSrhJMiQ0SZRJNEv0SE5JUSRNJf8lkyR2SpyRvS45I4aS0pNyl2FJ5UselrksNUVFUdaorlUXdQj1BvUEdlsZKa0szpBOkC6XPSHdKj8tIyZjLhMpkyZTJXJYZoKFoWjQGLYm2i3aO1kf7Iqsk6ywbK7tdtla2R3ZKbomck1ysXIFcnVyv3Bd5ury7fKL8HvkG+WcKaAU9hRUKmQqHFW4ojC2RXmK3hLWkYMm5JY8VYUU9xUDFtYrHFTsUJ5SUlTyV+EoHla4rjSnTlJ2UE5T3KV9RHlWhqjiocFX2qVxVeU2XoTvTk+gl9Db6uKqiqpeqUPWYaqfqtJq2Woharlqd2jN1orq1epz6PvVW9XENFQ0/jXUaNRqPNQma1prxmgc02zWntLS1wrS2ajVojWjLaTO0c7RrtJ/qUHQcdVJ1KnQe6GJ1rXUTdQ/pdunBehZ68Xplevf0YX1Lfa7+If3upZilNkt5SyuW9huQDZwNMgxqDAYNaYa+hrmGDYZvjTSMIo32GLUbfTe2ME4yPmH8xETKxNsk16TZ5L2pninLtMz0gRnFzMNso1mj2TtzffNY88PmDy2oFn4WWy1aLb5ZWlkKLGstR600rKKtyq36raWtA6x3WN+ywdi42Gy0uWTz2dbSNt32nO2fdgZ2iXan7EaWaS+LXXZi2ZC9mj3T/pj9gAPdIdrhqMOAo6oj07HC8YWTuhPb6aTTK2dd5wTn085vXYxdBC4XXaZcbV3Xu7a4odw83QrcOt2l3EPcS92fe6h5cDxqPMY9LTzXerZ4Ybx8vPZ49TOUGCxGNWPc28p7vXebD9knyKfU54Wvnq/At9kP9vP22+v3dLnmct7yBn/gz/Df6/8sQDsgNeCXFdgVASvKVrwMNAlcF9geRA1aE3QqaDLYJXhX8JMQnRBhSGuoeGhUaHXoVJhbWFHYQLhR+PrwuxEKEdyIxkhcZGjkyciJle4r968cjrKIyo/qW6W9KmvV7dUKq5NWX14jvoa55nw0Jjos+lT0V6Y/s4I5EcOIKY8ZZ7myDrDesJ3Y+9ijsfaxRbGv4uzjiuJGOPacvZzReMf44vgxriu3lPsuwSvhSMJUon9iZeJMUlhSXTI+OTq5iSfFS+S1pSinZKV08/X5+fyBVNvU/anjAh/ByTQobVVaY7o00tx0CHWEPwgHMxwyyjI+ZYZmns+SzOJldWTrZW/PfpXjkfPTWvRa1trWdarrNq8bXO+8/tgGaEPMhtaN6hvzNg5v8txUtZm4OXHzr7nGuUW5H7eEbWnOU8rblDf0g+cPNfli+YL8/q12W49sQ2/jbuvcbrb94PbvBeyCO4XGhcWFX3ewdtz50eTHkh9ndsbt7Nxluevwbuxu3u6+PY57qooki3KKhvb67a3fR99XsO/j/jX7bxebFx85QDwgPDBQ4lvSeFDj4O6DX0vjS3vLXMrqyhXLt5dPHWIf6jnsdLj2iNKRwiNfjnKPPjzmeay+Qqui+Dj2eMbxlydCT7T/ZP1T9UmFk4Unv1XyKgeqAqvaqq2qq08pntpVA9cIa0ZPR53uOuN2prHWoPZYHa2u8Cw4Kzz7+ufon/vO+ZxrPW99vvaC5oXyi9SLBfVQfXb9eEN8w0BjRGN3k3dTa7Nd88VfDH+pvKR6qeyyzOVdV4hX8q7MXM25OtHCbxm7xrk21Lqm9cn18OsP2la0dd7wuXHrpsfN6+3O7Vdv2d+6dNv2dtMd6zsNdy3v1ndYdFz81eLXi52WnfX3rO41dtl0NXcv677S49hz7b7b/ZsPGA/u9i7v7e4L6XvYH9U/8JD9cORR0qN3jzMeTz/Z9BTztOCZxLPi54rPK37T/a1uwHLg8qDbYMeLoBdPhlhDb35P+/3rcN5LysviVyqvqkdMRy6Neox2vV75evgN/830WP4fkn+Uv9V5e+FPpz87xsPHh98J3s283/FB/kPlR/OPrRMBE88nkyenpwo+yX+q+mz9uf1L2JdX05lfcV9Lvul+a/7u8/3pTPLMDJ8pYM61Aigk4Lg4AN5XAkCJQHqHLgCIYvM98Zyg+T5+jsB/4vm+eU6WAFQ6ARCyCQBfpEc5jIQmwmTkOtsSBTsB2MxMFP9QWpyZ6bwXGeksMZ9mZj4oAYBrBuCbYGZm+tDMzLcTSLGPAGhJne/FZ4VF/lDOYmapQ3kD+Ff9BQ+CATyu4C0KAAABm2lUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iWE1QIENvcmUgNS40LjAiPgogICA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPgogICAgICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgICAgICAgICB4bWxuczpleGlmPSJodHRwOi8vbnMuYWRvYmUuY29tL2V4aWYvMS4wLyI+CiAgICAgICAgIDxleGlmOlBpeGVsWERpbWVuc2lvbj45NDwvZXhpZjpQaXhlbFhEaW1lbnNpb24+CiAgICAgICAgIDxleGlmOlBpeGVsWURpbWVuc2lvbj45NDwvZXhpZjpQaXhlbFlEaW1lbnNpb24+CiAgICAgIDwvcmRmOkRlc2NyaXB0aW9uPgogICA8L3JkZjpSREY+CjwveDp4bXBtZXRhPgrP6XTpAAAFQ0lEQVR4Ae2czSt1XxTH10XeTbymvI0kZCSvZSgklJG8ZSCJyExeMpLIWxkYSDJR4j9gqIiBMkZEiiIpBpLf86xTdpfnuvfcvffZ65xfa0/su89e67v25yz7nr3Pucf3398CXIwTiDCuyIIWAQZPlAgMnsETESCS5Yxn8EQEiGQ54xk8EQEiWc54IvBRRLohZXFBvbGxAbu7u/Dw8BCy/1cHn88HWVlZ0NvbCw0NDV/Nrvvrc+uWwc7ODszOzkoDwxOwuroKpaWl0j6cNHTtVLO3t6c0bvyP2d/fV/LhpLFrwT8/PyuPW4cP5SB+ceBa8L/E+79pZvBEp5LBM3giAkSynPEMnogAkSxnPIMnIkAkyxnP4IkIEMlyxjN4IgJEspzxDJ6IAJEsZzyDJyJAJMsZz+CJCBDJcsYzeCICRLKc8QyeiACRLGc8gyciQCTLGU8E3sizk+fn57CwsACPj4+2h3l7ewvv7++2+wfqmJCQABkZGYEOBWzLzs6G8fFxSE5ODnhcZ6ORp4WPjo7g5OREZ9y2fL2+vsLl5aWtvtgJ+7a2tkJVVZVtG9mORqaa5ubmsDJPdjCqdiUlJVBRUaHqxpa9EfBJSUnWVBMTE2MrKIpO6enpMDc3BxERRpCAGZW/JAsKCmBqaoqCaUjN6OhomJ+fh9TU1JB9dXUwBh4Drq2thZ6eHl2xa/MzOTkJhYWF2vzZcWQUPAbU398PNTU1dmIz0qerqwvq6+uNaPmLGLmc9BfEOl5tYOaHc8Xx04eOz9XV1bC0tGRsXvePmQQ8BnBzcwPd3d3w8vLiH4+xem5uLmxubkJiYqIxTX8h41PNlzguVmZmZkiyDa+yMNOpoCMDMvAoXl5eDiMjI1g1VvBycXp6GnJycoxpBhIiBY8BtbW1QVNTU6DYHGkbGhoysjINFTw5eAxwdHQUcNXodMEfHHd0dDgtY8s/2Zfrz+hwA62zszOsX3H/9BHsc1FREaytrQEultxQXJHxCCIlJcVaPTqxrZCWlmb5dgt0HK9rwGMwuHrEVaTO8rUdgPDdVFwFHsHU1dVZ1/e6IE1MTABOM24rSvvxb29vcHx8DB8fH9a48K0ZuBmmWgYGBuDi4gIODg6UXOEXqY43eOB7EU5PT+Hp6cmKJz4+HsrKyiAqSh6f9Jfr5+cntLS0wN3d3Tc4OFXg/rtqwW0FXNleXV1JucKbGcvLy1oWaIuLi7C1tfUtjsrKSlhZWfnWFs4H6anm+vr6H+gojHebdBS8bYcDxlVmuAUXR7hI0rW3HmhMh4eH4Yb1rb80ePz3C1R+aw/UN1QbAgx3WwG3AWRPWKh4dB6XBq8ziGC+8Fbc8PBwsC7i2Nd2QF5enmhza8X14BFce3s7NDY2hmQ4ODgIuNXrheIJ8AhybGwMiouLf2WKNzPwpoZXimfAB1sIObHwcvoEegY8gsCb0fhgVFxcnOCCTwfgjWo3bQeI4IJU5FcAQZw6eQize319Hba3tyE2Nta61kf4XiueA4+A8/Pzte/pmD5xnppqTMNxUk97xp+dnVk3NpwM2rTv+/t77ZLawePraN38ok3tBCUd8lQjCU7VjMGrEpS0Z/CS4FTNGLwqQUl7Bi8JTtWMwasSlLRn8JLgVM0YvCpBSXtp8P47hJLanjZTHb80+MzMTOuniZGRkZ4GKBM8Qu/r65MxFTbSj3cID1yRIiCd8VJqbCQIMHiBwmyFwZvlLdQYvEBhtsLgzfIWagxeoDBbYfBmeQs1Bi9QmK0weLO8hRqDFyjMVhi8Wd5CjcELFGYrDN4sb6H2BxIHG0qZpcxfAAAAAElFTkSuQmCC';
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
}
