<?php
class FileDownloadPlugin extends Omeka_Plugin_Abstract
{
    protected $_hooks = array('initialize');
    
    public function hookInitialize()
    {
        Zend_Controller_Front::getInstance()->registerPlugin(new FileDownloadControllerPlugin);
    }
}

class FileDownloadControllerPlugin extends Zend_Controller_Plugin_Abstract
{
    /**
     * Before dispatch, catch requests to the nonexistent files/download action 
     * and initiate a file download using the file's original name.
     * 
     * FilesController::checkUserPermissions() automatically validates the file 
     * ID during controller initialization, which happens before preDispatch() 
     * is called. We let that do the validation work for us.
     */
    public function preDispatch($request)
    {
        if ('files' == $request->getControllerName() && 'download' == $request->getActionName()) {
            $response = $this->getResponse();
            $db = Omeka_Context::getInstance()->getDb();
            $file = $db->getTable('File')->find($request->getParam('id'));
            if ($file) {
                $response->setHeader('Content-Type', $file->mime_browser)
                         ->setHeader('Content-Disposition', 'attachment; filename="' . $file->original_filename . '"')
                         ->setBody(file_get_contents(WEB_FILES . '/' . $file->archive_filename));
            }
            $response->sendResponse();
        }
    }
}

$plugin = new FileDownloadPlugin;
$plugin->setUp();

/**
 * Return the URL to the file download endpoint.
 * 
 * @param File $file
 * @return string
 */
function file_download_url(File $file)
{
    return __v()->url('files/download/' . $file->id);
}
