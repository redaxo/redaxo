<?php

use splitbrain\PHPArchive\Tar;
use splitbrain\PHPArchive\Archive;

/**
 * REDAXO Tar Klasse.
 *
 * Diese Subklasse fixed ein paar Bugs gegenüber der
 * original Implementierung und erhoeht die Performanz
 *
 * @author  Markus Staab
 *
 * @package redaxo\backup
 *
 * @see     http://www.mkssoftware.com/docs/man4/tar.4.asp
 *
 * @internal
 */
class rex_backup_tar
{
    /**
     * @var Tar
     */
    private $tar;

    // constructor to omit warnings
    public function __construct()
    {
        $this->tar = new Tar();
    }

    /**
     * Open a TAR file.
     *
     * @param string $filename
     *
     * @return bool
     */
    public function openTAR($filename)
    {
        // If the tar file doesn't exist...
        if (!is_file($filename)) {
            return false;
        }

        $this->tar->open($filename);

        return true;
    }

    public function create($archivePath) {
        $this->tar->create($archivePath);
        $this->tar->setCompression(9, Archive::COMPRESS_GZIP);
    }

    /**
     * Add a file to the tar archive.
     *
     * @param string $filename
     *
     * @return bool
     */
    public function addFile($filename)
    {
        // Make sure the file we are adding exists!
        if (!is_file($filename)) {
            return false;
        }

        $this->tar->addFile($filename);

        return true;
    }

    /**
     * Add a directory to this tar archive.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function addDirectory($dirname)
    {
        if (!is_dir($dirname)) {
            return false;
        }

        $it = new DirectoryIterator($dirname);
        foreach($it as $fileInfo) {
            $this->tar->addData($fileInfo);
        }

        return true;
    }

    /**
     * Saves tar archive to a different file than the current file.
     *
     * @return bool|string
     */
    public function close()
    {
        $this->tar->close();

        return true;
    }

    /**
     * @param string $outdir the target directory for extracting
     *
     * @return bool
     */
    public function extractTar($outdir)
    {
        $this->tar->extract($outdir);

        return true;
    }
}
