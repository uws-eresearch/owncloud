<?php

namespace OCA\crate_it\Service;


class LoggingService {
    
    /**
     * @var CrateManager
     */
    private $crateManager;
    
    private $logfile;
    
    public function __construct($api, $crateManager) {
        $this->crateManager = $crateManager;
        $userId = $api->getUserId();
        $user_dir = $baseDir = \OC::$SERVERROOT.'/data/'.$userId;
        $this->logfile = $user_dir.'/publish.log';
    }
    
    public function log($text) {
       file_put_contents($this->logfile, $this->timestamp().$text."\n", FILE_APPEND);
    }
    
    public function logManifest($crateName) {        
        $manifest = $this->crateManager->getManifestFileContent($crateName);
        $text = $this->prettyPrint($manifest);
        $this->log("Manifest JSON for crate '$crateName':");
        $this->log($text);
    }
    
    public function logPublishedDetails($zip, $crateName) {
        $zipname = basename($zip);
        $this->log("Package content for '$zipname':");
        $this->log("----start content-----");
        $za = new \ZipArchive(); 

        $za->open($zip); 
    
        for( $i = 0; $i < $za->numFiles; $i++ ){ 
            $stat = $za->statIndex( $i );
            if ($stat['size']!=0) {
                $this->log($stat['name']);
            }
            if ($stat['name'] == '/manifest-sha1.txt') {
                $sha_content = $za->getFromIndex($i);
            }        
        }
        $this->log("----end content-----");
        $checksum = sha1_file($zip);
        $this->log("Checksum (SHA) for $zipname: $checksum");
        $this->log("Content of $crateName's manifest-sha1.txt:");
        $this->log("----start file manifest-sha1.txt-----");
        $this->log("\n".$sha_content);
        $this->log("----end file-----");
    }
    
    private function timestamp() {
        date_default_timezone_set('Australia/Sydney');  
        $format="[Y-m-d H:i:s P] ";
        //$offset=timezone_offset_get(new \DateTimeZone('Australia/Sydney'), new \DateTime());    
        $timestamp = date($format);  
        return $timestamp;
    }

    private function prettyPrint($json)
    {
        $result = '';
        $level = 0;
        $in_quotes = false;
        $in_escape = false;
        $ends_line_level = NULL;
        $json_length = strlen( $json );
    
        for( $i = 0; $i < $json_length; $i++ ) {
            $char = $json[$i];
            $new_line_level = NULL;
            $post = "";
            if( $ends_line_level !== NULL ) {
                $new_line_level = $ends_line_level;
                $ends_line_level = NULL;
            }
            if ( $in_escape ) {
                $in_escape = false;
            } else if( $char === '"' ) {
                $in_quotes = !$in_quotes;
            } else if( ! $in_quotes ) {
                switch( $char ) {
                    case '}': case ']':
                        $level--;
                        $ends_line_level = NULL;
                        $new_line_level = $level;
                        break;
    
                    case '{': case '[':
                        $level++;
                    case ',':
                        $ends_line_level = $level;
                        break;
    
                    case ':':
                        $post = " ";
                        break;
    
                    case " ": case "\t": case "\n": case "\r":
                        $char = "";
                        $ends_line_level = $new_line_level;
                        $new_line_level = NULL;
                        break;
                }
            } else if ( $char === '\\' ) {
                $in_escape = true;
            }
            if( $new_line_level !== NULL ) {
                $result .= "\n".str_repeat( "  ", $new_line_level );
            }
            $result .= $char.$post;
        }
    
        return $result;
    }
}
