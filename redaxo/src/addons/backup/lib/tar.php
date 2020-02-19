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
class rex_backup_tar extends tar
{
    /** @var string[] */
    private $messages = [];

    // constructor to omit warnings
    public function __construct()
    {
        parent::__construct();
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
        // call constructor to omit warnings instead of unset vars..

        $this->__construct();
        // Clear any values from previous tar archives
        unset($this->filename);
        unset($this->isGzipped);
        unset($this->tar_file);
        unset($this->directories);

        // If the tar file doesn't exist...
        if (!file_exists($filename)) {
            return false;
        }

        $this->filename = $filename;

        // Parse this file
        $this->__readTar();

        return true;
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
        if (!file_exists($filename)) {
            return false;
        }

        // Make sure there are no other files in the archive that have this same filename
        if ($this->containsFile($filename)) {
            return false;
        }

        // Get file information
        $file_information = stat($filename);

        // Read in the file's contents
        //    $fp = fopen($filename,"rb");
        //    $file_contents = fread($fp,filesize($filename));
        //    fclose($fp);
        // STM: hier mit get_file_contents ist viel schneller
        $file_contents = rex_file::get($filename);

        // Add file to processed data
        ++$this->numFiles;
        $activeFile = &$this->files[];
        $activeFile['name'] = $filename;
        $activeFile['mode'] = $file_information['mode'];
        $activeFile['user_id'] = $file_information['uid'];
        $activeFile['group_id'] = $file_information['gid'];
        $activeFile['size'] = $file_information['size'];
        $activeFile['time'] = $file_information['mtime'];
        // STM: Warnung gefixed
        //    $activeFile["checksum"]   = $checksum;
        $activeFile['user_name'] = '';
        $activeFile['group_name'] = '';
        $activeFile['file'] = $file_contents;

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
        if (!file_exists($dirname)) {
            return false;
        }

        // Get directory information
        $file_information = stat($dirname);

        // Add directory to processed data
        ++$this->numDirectories;
        $activeDir = &$this->directories[];
        $activeDir['name'] = $dirname;
        $activeDir['mode'] = $file_information['mode'];
        $activeDir['time'] = $file_information['mtime'];
        $activeDir['user_id'] = $file_information['uid'];
        $activeDir['group_id'] = $file_information['gid'];
        // STM: Warnung gefixed
        //    $activeDir["checksum"]  = $checksum;

        return true;
    }

    /**
     * Read a non gzipped tar file in for processing.
     *
     * @param string $filename
     *
     * @return bool
     */
    protected function __readTar($filename = '')
    {
        // Set the filename to load
        if (!$filename) {
            $filename = $this->filename;
        }

        // Read in the TAR file
        //    $fp = fopen($filename,"rb");
        //    $this->tar_file = fread($fp,filesize($filename));
        //    fclose($fp);
        // STM: hier mit get_file_contents ist viel schneller
        $this->tar_file = rex_file::get($filename);

        if ($this->tar_file[0] == chr(31) && $this->tar_file[1] == chr(139) && $this->tar_file[2] == chr(8)) {
            if (!function_exists('gzinflate')) {
                return false;
            }

            $this->isGzipped = true;

            $this->tar_file = gzinflate(substr($this->tar_file, 10, -4));
        }

        // Parse the TAR file
        $this->__parseTar();

        return true;
    }

    /**
     * Saves tar archive to a different file than the current file.
     *
     * @param string $filename
     * @param bool   $useGzip
     *
     * @return bool|string
     */
    public function toTar($filename, $useGzip)
    {
        // Encode processed files into TAR file format
        $this->__generateTar();

        // GZ Compress the data if we need to
        if ($useGzip) {
            // Make sure we have gzip support
            if (!function_exists('gzencode')) {
                return false;
            }

            $file = gzencode($this->tar_file);
        } else {
            $file = $this->tar_file;
        }

        // Write the TAR file
        //    $fp = fopen($filename,"wb");
        //    fwrite($fp,$file);
        //    fclose($fp);

        // kein Filename gegeben => Inhalt zurueckgeben
        if (!$filename) {
            return $file;
        }

        // STM: hier mit put_file_contents ist viel schneller
        return false !== rex_file::put($filename, $file);
    }

    // Generates a TAR file from the processed data
    // PRIVATE ACCESS FUNCTION
    protected function __generateTAR()
    {
        // Clear any data currently in $this->tar_file
        //    unset($this->tar_file);
        // STM: Warnung gefixed
        $this->tar_file = '';

        // Generate Records for each directory, if we have directories
        if ($this->numDirectories > 0) {
            foreach ($this->directories as $key => $information) {
                //        unset($header);
                // STM: Warnung gefixed
                $header = '';

                // Generate tar header for this directory
                // Filename, Permissions, UID, GID, size, Time, checksum, typeflag, linkname, magic, version, user name, group name, devmajor, devminor, prefix, end
                $header .= str_pad($information['name'], 100, chr(0));
                $header .= str_pad(decoct($information['mode']), 7, '0', STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct($information['user_id']), 7, '0', STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct($information['group_id']), 7, '0', STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct(0), 11, '0', STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct($information['time']), 11, '0', STR_PAD_LEFT) . chr(0);
                $header .= str_repeat(' ', 8);
                $header .= '5';
                $header .= str_repeat(chr(0), 100);
                $header .= str_pad('ustar', 6, chr(32));
                $header .= chr(32) . chr(0);
                $header .= str_pad('', 32, chr(0));
                $header .= str_pad('', 32, chr(0));
                $header .= str_repeat(chr(0), 8);
                $header .= str_repeat(chr(0), 8);
                $header .= str_repeat(chr(0), 155);
                $header .= str_repeat(chr(0), 12);

                // Compute header checksum
                $checksum = str_pad(decoct($this->__computeUnsignedChecksum($header)), 6, '0', STR_PAD_LEFT);
                for ($i = 0; $i < 6; ++$i) {
                    $header[(148 + $i)] = substr($checksum, $i, 1);
                }
                $header[154] = chr(0);
                $header[155] = chr(32);

                // Add new tar formatted data to tar file contents
                $this->tar_file .= $header;
            }
        }

        // Generate Records for each file, if we have files (We should...)
        if ($this->numFiles > 0) {
            foreach ($this->files as $key => $information) {
                //        unset($header);
                // STM: Warnung gefixed
                $header = '';

                // Generate the TAR header for this file
                // Filename, Permissions, UID, GID, size, Time, checksum, typeflag, linkname, magic, version, user name, group name, devmajor, devminor, prefix, end
                $header .= str_pad($information['name'], 100, chr(0));
                $header .= str_pad(decoct($information['mode']), 7, '0', STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct($information['user_id']), 7, '0', STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct($information['group_id']), 7, '0', STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct($information['size']), 11, '0', STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct($information['time']), 11, '0', STR_PAD_LEFT) . chr(0);
                $header .= str_repeat(' ', 8);
                $header .= '0';
                $header .= str_repeat(chr(0), 100);
                $header .= str_pad('ustar', 6, chr(32));
                $header .= chr(32) . chr(0);
                $header .= str_pad($information['user_name'], 32, chr(0));  // How do I get a file's user name from PHP?
                $header .= str_pad($information['group_name'], 32, chr(0)); // How do I get a file's group name from PHP?
                $header .= str_repeat(chr(0), 8);
                $header .= str_repeat(chr(0), 8);
                $header .= str_repeat(chr(0), 155);
                $header .= str_repeat(chr(0), 12);

                // Compute header checksum
                $checksum = str_pad(decoct($this->__computeUnsignedChecksum($header)), 6, '0', STR_PAD_LEFT);
                for ($i = 0; $i < 6; ++$i) {
                    $header[(148 + $i)] = substr($checksum, $i, 1);
                }
                $header[154] = chr(0);
                $header[155] = chr(32);

                // Pad file contents to byte count divisible by 512
                $file_contents = str_pad($information['file'], (ceil($information['size'] / 512) * 512), chr(0));

                // Add new tar formatted data to tar file contents
                $this->tar_file .= $header . $file_contents;
            }
        }

        // Add 512 bytes of NULLs to designate EOF
        $this->tar_file .= str_repeat(chr(0), 512);

        return true;
    }

    /**
     * @return bool
     */
    public function extractTar()
    {
        // kills: Warnung verhindern
        if (is_array($this->files)) {
            foreach ($this->files as $item) {
                // jan: wenn probleme mit der ordnergenerierung -> ordner manuell einstellen

                if (!file_exists(dirname($item['name']))) {
                    rex_dir::create(dirname($item['name']));
                }
                if ($h = @fopen($item['name'], 'w+')) {
                    fwrite($h, $item['file'], $item['size']);
                    fclose($h);
                } else {
                    $this->messages[] = dirname($item['name']);
                    return false;
                }
            }
        }
        if (count($this->messages) > 0) {
            return false;
        }
        return true;
    }

    /**
     * @return string[]
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
