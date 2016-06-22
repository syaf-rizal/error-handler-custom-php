<?php

	require 'PHPMailer/PHPMailerAutoload.php';
	class error_handler
	{

		var $sSmtp_Host 		= "";
		var $sSmtp_Port 		= "";
		var $sSmtp_Username 	= "";
		var $sSmtp_Password		= "";
		var $sSmtp_From			= "";
		var $sSmtp_Address		= "";

		function smtp_host($sSmtp_Host)
		{
			$this->sSmtp_Host = $sSmtp_Host;
			return;
		}

		function smtp_port($sSmtp_Port)
		{
			$this->sSmtp_Port = $sSmtp_Port;
			return;
		}

		function smtp_username($sSmtp_Username)
		{
			$this->sSmtp_Username = $sSmtp_Username;
			return;
		}

		function smtp_password($sSmtp_Password)
		{
			$this->sSmtp_Password = $sSmtp_Password;
			return;
		}

		function smtp_from($sSmtp_From)
		{
			$this->sSmtp_From = $sSmtp_From;
			return;
		}

		function smtp_address($sSmtp_Address)
		{
			$this->sSmtp_Address = $sSmtp_Address;
			return;
		}

		
		function error_handler($ip=0, $show_user=0, $show_developer=1, $email = TRUE, $log_file=NULL)
		{
			$this->ip = $ip;
			$this->show_user = $show_user;
			$this->show_developer = $show_developer;
			$this->email = $email;
			$this->log_file = $log_file;
			$this->log_message = NULL;
			$this->email_sent = false;
			$this->error_codes =  E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR;
			$this->warning_codes =  E_WARNING | E_CORE_WARNING | E_COMPILE_WARNING | E_USER_WARNING;
			$this->warning_codes =  E_WARNING | E_CORE_WARNING | E_COMPILE_WARNING | E_USER_WARNING;
			$this->error_names = array('E_ERROR','E_WARNING','E_PARSE','E_NOTICE','E_CORE_ERROR','E_CORE_WARNING',
									   'E_COMPILE_ERROR','E_COMPILE_WARNING','E_USER_ERROR','E_USER_WARNING',
									   'E_USER_NOTICE','E_STRICT','E_RECOVERABLE_ERROR');
									   
			for($i=0,$j=1,$num=count($this->error_names); $i<$num; $i++,$j=$j*2)
				$this->error_numbers[$j] = $this->error_names[$i];
		}

		function handler($errno, $errstr, $errfile, $errline, $errcontext)
		{
			$this->errno = $errno;
			$this->errstr = $errstr;
			$this->errfile = $errfile;
			$this->errline = $errline;
			$this->errcontext = $errcontext;
			if($this->log_file) $this->log_error_msg();
			if($this->email) $this->send_error_msg();
			$redirect = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
			$redirect .= "://".$_SERVER['HTTP_HOST'];
			$redirect .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);
			header ("Location: " . $redirect . "404.php");
			return true;
		}

		function error_msg_basic()
		{
			$message = NULL;
			if($this->errno & $this->error_codes) $message .= "<b>ERROR:</b> There has been an error in the code.";
			if($this->errno & $this->warning_codes) $message .= "<b>WARNING:</b> There has been an error in the code.";
				
			if($message) $message .= ($this->email_sent)?" The developer has been notified.<br />\n":"<br />\n";
			echo $message;	
		}
		
		function error_msg_detailed()
		{
			$silent = (2 & $this->show_developer)?true:false;
			$context = (4 & $this->show_developer)?true:false;
			$backtrace = (8 & $this->show_developer)?true:false;
			
			switch(true)
			{
				case (16 & $this->show_developer): $color='white'; break;
				case (32 & $this->show_developer): $color='black'; break;
				default: $color='red';
			}
		
			$message =  ($silent)?"<!--\n":'';
			$message .= "<pre style='color:$color;'>\n\n";
			$message .= "file: ".print_r( $this->errfile, true)."\n";
			$message .= "line: ".print_r( $this->errline, true)."\n\n";
			$message .= "code: ".print_r( $this->error_numbers[$this->errno], true)."\n";
			$message .= "message: ".print_r( $this->errstr, true)."\n\n";
			$message .= ($context)?"context: ".print_r( $this->errcontext, true)."\n\n":'';
			$message .= ($backtrace)?"backtrace: ".print_r( debug_backtrace(), true)."\n\n":'';
			$message .= "</pre>\n";
			$message .= ($silent)?"-->\n\n":'';
			
			echo $message;
		}
		
		function send_error_msg()
		{	
			$sSubject = 'Error: '.$this->errcontext['_SERVER']['SERVER_NAME'].$this->errcontext['_SERVER']['REQUEST_URI'];

			$sMessage = "<h3 style=\"padding-bottom:5px;\">Error Reporting</h3>";
			$sMessage .= "<div style=\"margin-bottom:5px;\"> time: ".date("j M y - g:i:s A (T)", mktime())."</div>";
			$sMessage .= "<div style=\"margin-bottom:5px;\"> file: ".print_r( $this->errfile, true)."</div>";
			$sMessage .= "<div style=\"margin-bottom:5px;\">line: ".print_r( $this->errline, true)."</div>";
			$sMessage .= "<div style=\"margin-bottom:5px;\">code: ".print_r( $this->error_numbers[$this->errno], true)."</div>";
			$sMessage .= "<div style=\"margin-bottom:5px;\">Message: ".print_r( $this->errstr, true)."</div>";
			$sMessage .= "<div style=\"margin:10px;\"></div>";
			$sMessage .= "##################################################";
			
			$this->email_sent = false;

			if($this->sSmtp_Host == "smtp.gmail.com")
			{
				if($this->is_obj_mail_gmail($sSubject, $sMessage))
				{
					$this->email_sent = true;
				}
			}
			else
			{
				if($this->is_obj_mail_host($sSubject, $sMessage))
				{
					$this->email_sent = true;
				}
			}
		}
		
		function log_error_msg()
		{
			$message =  "time: ".date("j M y - g:i:s A (T)", mktime())."\n";
			$message .= "file: ".print_r( $this->errfile, true)."\n";
			$message .= "line: ".print_r( $this->errline, true)."\n\n";
			$message .= "code: ".print_r( $this->error_numbers[$this->errno], true)."\n";
			$message .= "message: ".print_r( $this->errstr, true)."\n";
			$message .= "##################################################\n\n";

			if (!$fp = fopen($this->log_file, 'a+')) 
				$this->log_message = "Could not open/create file: $this->log_file to log error."; $log_error = true;
			
			if (!fwrite($fp, $message)) 
				$this->log_message = "Could not log error to file: $this->log_file. Write Error."; $log_error = true;
			
			if(!$this->log_message)
				$this->log_message = "Error was logged to file: $this->log_file.";
				
			fclose($fp); 
		}


		function is_obj_mail_host($sMail_Subject, $sMail_Message)
		{
			$obj_mail = new PHPMailer;
			$obj_mail->isSendmail();
			$obj_mail->isHTML(true);
			$obj_mail->setFrom($this->sSmtp_From, '');
			$obj_mail->addReplyTo($this->sSmtp_From, '');
			$obj_mail->addAddress($this->sSmtp_Address, '');
			$obj_mail->Subject = $sMail_Subject;
			$obj_mail->msgHTML($sMail_Message);
			if (!$obj_mail->send()) {
				echo "Mailer Error: " . $obj_mail->ErrorInfo;
			} else {
				return true;
			}
		}

		function is_obj_mail_gmail($sMail_Subject, $sMail_Message)
		{
			$obj_mail = new PHPMailer;
			$obj_mail->isSMTP();
			$obj_mail->SMTPDebug = 1;
			$obj_mail->Debugoutput = 'html';
			$obj_mail->Host = $this->sSmtp_Host;
			$obj_mail->Port = $this->sSmtp_Port;
			$obj_mail->SMTPSecure = 'tls';
			$obj_mail->SMTPAuth = true;
			$obj_mail->Username = $this->sSmtp_Username;
			$obj_mail->Password = $this->sSmtp_Password;
			$obj_mail->isHTML(true);
			$obj_mail->setFrom($this->sSmtp_From, '');
			$obj_mail->addReplyTo($this->sSmtp_From, '');
			$obj_mail->addAddress($this->sSmtp_Address, '');
			$obj_mail->Subject = $sMail_Subject;
			$obj_mail->msgHTML($sMail_Message);
			if (!$obj_mail->send()) {
				echo "Mailer Error: " . $obj_mail->ErrorInfo;
			} else {
				return true;
			}
		}

	
	}
?>