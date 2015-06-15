<?php

//TODO setNextID, setNextMode to direct module execution.
class Module {
	
	const NOTIFICATION_INFO = 0;
	const NOTIFICATION_ERROR = 1;
	
	private $dialog = null;
	private $notification = null;
	private $notificationType = self::NOTIFICATION_INFO;
	
	private static $moduleList = null;
	
	protected function __construct($name, $obj) {
		if(self::$moduleList == null) {
			self::$moduleList = new Collection();
		}
		self::$moduleList->add($name, $obj);
	}
	
	public static function getModuleList() {
		return self::$moduleList;
	}
	
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