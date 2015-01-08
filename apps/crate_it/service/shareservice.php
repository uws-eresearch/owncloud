<?php
namespace OCA\crate_it\Service;

use \OCP\Share;

class ShareService {
	
	/**
	 * This needs a lot of arguments
	 */
	public function share($itemType, $itemSource, $shareType, $shareWith, $permissions, $itemSourceName = null, \DateTime $expirationDate = null) {
		
		try {
			
			$token = Share::shareItem($itemType, $itemSource, $shareType, $shareWith, $permissions, $itemSourceName);
			
		} catch (Exception $exception) {
			
			throw $exception;
		}
		return $token;	
		
	}
	
}