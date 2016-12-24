<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Config;
use Piwik\Filesystem;
use Piwik\SettingsServer;
use Piwik\Translation\Translator;

/**
 * Check that no piwik was left uninstalled in a any sub-directory recursively
 */
class NoPiwikLeftUninstalledInSubDirectories implements Diagnostic
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function execute()
    {
        $label = $this->translator->translate('Installation_SystemCheckFilesystemSecurityChecks');

        $status = DiagnosticResult::STATUS_OK;
        $comment = '';

        $piwikFolders = $this->findPiwikInSubFolders();
        if(count($piwikFolders) > 0) {
            $status = DiagnosticResult::STATUS_ERROR;
            $comment = sprintf(
                '<br />%s<br />%s',
                $this->translator->translate('Installation_SystemCheckSomePiwikFoldersFoundInSubDirectories'),
                implode("<br/>", $piwikFolders)
            );
        }
        return array(DiagnosticResult::singleResult($label, $status, $comment));
    }

    private function isDirectoryAnUninstalledPiwik($directory)
    {
        $expectedFiles = array(
            '/config/',
            '/index.php',
            '/piwik.php',
            '/plugins/',
        );
        foreach($expectedFiles as $expectedFile) {
            if(!file_exists($directory . $expectedFile)) {
                return false;
            }
        }
        return true;
    }

    private function findPiwikInSubFolders()
    {
        $piwikInSubFolders = array();
        foreach(Filesystem::globr(PIWIK_DOCUMENT_ROOT, '*') as $directory) {
            if(self::isDirectoryAnUninstalledPiwik($directory)) {
                $piwikInSubFolders[] = $directory;
            }
        }
        return $piwikInSubFolders;
    }
}
