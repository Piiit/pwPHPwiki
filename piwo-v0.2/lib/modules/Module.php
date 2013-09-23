<?php

class Module {
	
	const NOTIFICATION_INFO = 0;
	const NOTIFICATION_ERROR = 1;
	
	private $dialog = null;
	private $notification = null;
	private $notificationType = self::NOTIFICATION_INFO;
	
	protected function setDialog($dialog) {
		$this->dialog = $dialog;
	}
	
	public function getDialog() {
		return $this->dialog;
	}
	
	public function getNotification() {
		return $this->notification;
	}
	
	public function getNotificationType() {
		return $this->notificationType;
	}

	protected function setNotification($notification, $type = self::NOTIFICATION_INFO) {
		$this->notification = $notification;
		$this->notificationType = $type;
	}
	
}

?>