<?php

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
     * @var PharData
     */
    private $tar;

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

        $this->tar = new PharData($filename);

        return true;
    }

    public function create($archivePath)
    {
        $this->tar = new PharData($archivePath);
        $this->tar->compress(Phar::GZ);
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
     * Saves tar archive to a different file than the current file.
     *
     * @return bool|string
     */
    public function close()
    {
        $this->tar = null;

        return true;
    }

    /**
     * @param string $outdir the target directory for extracting
     *
     * @return bool
     */
    public function extractTar($outdir)
    {
        $this->tar->extractTo ($outdir, null, true);

        return true;
    }
}
