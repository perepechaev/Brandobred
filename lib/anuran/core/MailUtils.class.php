<?php
/***************************************************************************
*   Copyright (C) 2004 by Sergeev S. Sergey                               *
*   webs.support@gmail.com                                                *
*                                                                         *
*   This program is free software; you can redistribute it and/or modify  *
*   it under the terms of the GNU General Public License as published by  *
*   the Free Software Foundation; either version 2 of the License, or     *
*   (at your option) any later version.                                   *
*                                                                         *
***************************************************************************/

	class MailUtilsException extends Exception{};

	class MailUtils
	{
		private $subject	  = null; // тема сообщения
		private $to			  = null; // адресаты
		private $templateFile = null; // файл - активный шаблон
		private $templateDir  = null; // папка, хранящая шаблоны
		private $activeVars	  = array(); // ассоциативный массив переменных
		private $header		  = array(); // заголовки сообщения = additional_headers
		private $body		  = null; // тело сообщения
		private $encoding     = null; // кодировка отправляемого письма
		private $isComposed	  = false; // письмо подготовлено?
		private $attachments  = array(); // вложения

		public function __construct($dir='default')
		{
			$this->setTemplateDir($dir);

			if (!defined('MAIL_ENCODE')){
				throw new MailUtilsException('Константа "MAIL_ENCODE" не определена');
			}
		}

		/**
		 * Файл с активным шаблоном
		 *
		 * @param string $file
		 * @return MailUtils
		 */
		public function setTemplate($file)
		{
			if (!is_readable($this->templateDir.$file))
				throw new MailUtilsException('Не могу прочитать файл "'.$file.'"');

			$this->templateFile = $file;

			return $this;
		}

		/**
		 * Определяем ассоциатиныей массив переменных PHP, ключи которого
		 * содержатся в почтовом шаблоне.
		 *
		 * @param array $array
		 * @return MailUtils
		 */
		public function setActiveVars($array)
		{
			if (!is_array($array))
				throw new MailUtilsException('activeVars - это массив');

			$this->activeVars = $array;

			return $this;
		}

		/**
		 * @return void
		 */
		public function send()
		{
			$this->composeMail();

			$this->sendMail();
		}

		/**
		 * @return string
		 */
		public function getComposedBody()
		{
			$this->composeMail();

			return $this->encodingContext($this->body);
		}

		public static function checkMail($mail) // TODO: move to onPHP
		{
			return preg_match(self::getMailPattern(), $mail);
		}

		public static function getMailPattern()
		{
			return '/^[a-z0-9\._-]+@[a-z0-9\._-]+\.[a-z]{2,4}$/i';
		}

		public function addAttachment($file)
		{
			$this->attachments[] = $file;
		}

		/*********************** Private Methods *****************************/

		private function composeMail()
		{
			if ($this->isComposed === true)
				return $this;

			$this->prepareContext();

			$this->parseContentType();//обязательно первым
			$this->parseTo();
			$this->parseSubject();

			return $this;
		}

		/**
		 * Директория с почтовыми шаблонами
		 *
		 * @param string $dir
		 * @return MailUtils
		 */
		private function setTemplateDir($dir = 'default')
		{
			if ($dir == 'default'){
				if (!defined('PATH_MAIL_TEMPLATES')){
					throw new MailUtilsException('Константа PATH_MAIL_TEMPLATES, определяющая папку с почтовыми шаблонами, не существует');
				}
				$this->templateDir = PATH_MAIL_TEMPLATES;
			} else
				$this->templateDir = $dir;

			if (!is_readable($this->templateDir))
				throw new MailUtilsException('Не могу прочитать директорию "'.$this->templateDir.'"');

			return $this;
		}

		/**
		 * Парсит активный шаблон
		 *
		 * @return string
		 */
		private function parseTemplate()
		{
			ob_start();
			extract($this->activeVars, EXTR_OVERWRITE);
			include($this->templateDir.$this->templateFile);
			$template = ob_get_contents();
			ob_end_clean();

			return $template;
		}

		/**
		 * Подготавливает контекст: заголовок, тело
		 *
		 * @return MailUtils
		 */
		private function prepareContext()
		{
			list ($header, $body) = preg_split("/\r?\n\r?\n/s", $this->parseTemplate(), 2);
			$this->body = trim($body);

			foreach (preg_split('/\r?\n/s', $header) as $line){
				$pos = strpos($line, ':');
				$name = ucfirst(strtolower(substr($line, 0, $pos)));
				$value = trim(substr($line, $pos+1));
				$this->header[$name] = $value;
			}

			return $this;
		}

		/**
		 * Кодирование контекста
		 *
		 * @return string
		 */
		private function encodingContext($context)
		{
			if (empty($this->encoding))
				throw new MailUtilsException('Не определена кодировка отправляемого письма');

			if (constant('MAIL_ENCODE') == $this->encoding)
				return $context;

			/*try {
				$encodeStr = iconv(MAIL_ENCODE, $this->encoding.'//TRANSLIT', $context);
			} catch (BaseException $e) {*/
				//throw new MailUtilsException('Ошибка при перекодировке (iconv)');
				$this->encoding = MAIL_ENCODE;
				return $context;
			//}

			return $encodeStr;
		}

		/**
		 * Корректно кодирует все заголовки в письме с использованием
		 * метода base64
		 *
		 * @return string
		 */
		private function encodingHeader()
		{
			$encodeHeader = "";
			foreach ($this->header as $key=>$line)
				$encodeHeader.= $key.': '.$this->encodingLine($this->encodingContext($line))."\r\n";

			return $encodeHeader;
		}

		/**
		 * Кодирует в строке максимально возможную последовательность
		 * символов, начинающуюся с недопустимого символа и НЕ
		 * включающую E-mail (адреса E-mail обрамляют символами < и >).
		 *
		 * @see encodingLineCallback()
		 * @param string
		 */
		private function encodingLine($line)
		{
			return preg_replace_callback(
				'/([\x7F-\xFF][^<>\r\n]*)/s',
				array($this, 'encodingLineCallback'),
				$line
			);
		}

		/**
		 * Служебная функция для использования в encodingLine()
		 *
		 * @return string
		 */
		private function encodingLineCallback($p)
		{
			preg_match('/^(.*?)(\s*)$/s', $p[1], $sp);

			return "=?$this->encoding?B?".base64_encode($sp[1])."?=".$sp[2];
		}

		/**
		 * Разбирает заголовок To
		 * @return MailUtils
		 */
		private function parseTo()
		{
			if (isset($this->header['To'])){
				if (preg_match('/^\s*([^\r\n]*)[\r\n]*/m', $this->header['To'], $p)) {
					$this->to = @$p[1];
					unset($this->header['To']);
				}
			} else
				throw new MailUtilsException('В шаблоне "'.$this->templateFile.'" не определен получатель письма');

			return $this;
		}

		/**
		 * Разбирает заголовок Subject
		 * @return MailUtils
		 */
		private function parseSubject()
		{
			if (isset($this->header['Subject'])){
				if (preg_match('/^\s*([^\r\n]*)[\r\n]*/m', $this->header['Subject'], $p)) {
					$this->subject = @$p[1];
					unset($this->header['Subject']);
				}
			} else
				throw new MailUtilsException('В шаблоне "'.$this->templateFile.'" не определена тема письма Subject');

			return $this;
		}

		/**
		 * Кодировка отправляемого письма будет определяется
		 * автоматически на основе заголовка Content-type
		 * @return MailUtils
		 */
		private function parseContentType()
		{
			if (isset($this->header['Content-type'])){
				if (preg_match('/^\s*\S+\s*;\s*charset\s*=\s*(\S+)/mi', $this->header['Content-type'], $p))
					$this->encoding = $p[1];
				else
					throw new MailUtilsException('В шаблоне "'.$this->templateFile.'" не определена кодировка charset');
			}
			else
				throw new MailUtilsException('В шаблоне "'.$this->templateFile.'" не определен заголовок Content-type');

			return $this;
		}

		/**
		 * Кодируем контекст и отправляем
		 * @return void
		 */
		private function sendMail()
		{   
			$isSend = mail(
				$this->encodingLine($this->encodingContext($this->to)),
				$this->encodingLine($this->encodingContext($this->subject)),
				$this->encodeAttachments().$this->encodingContext($this->body),
				trim($this->encodingHeader())
			);
			if (!$isSend){
			     throw new MailUtilsException('Почтовое сообщение не было отправлено!');
			}
		}

		/**
		 * формирует строку с аттачами
		 *
		 * @return string
		 */
		private function encodeAttachments()
		{
			$attachStr = '';
			if (count($this->attachments)) {
				$eol="\n";
				$mimeBoundary = md5(time());

				$bodyContentType = $this->header['Content-type'];
				$this->header['Content-type'] = 'multipart/related; boundary="'.$mimeBoundary.'"';
				for ($i=0; $i < count($this->attachments); $i++) {
					if (is_file($this->attachments[$i])) {
						// File for Attachment
						$fileName = substr($this->attachments[$i], (strrpos($this->attachments[$i], "/")+1));
						$fileInfo = getimagesize($this->attachments[$i]);

						$handle = fopen($this->attachments[$i], 'rb');
						$fContents = fread($handle, filesize($this->attachments[$i]));
						$fContents = chunk_split(base64_encode($fContents));    //Encode The Data For Transition using base64_encode();
						fclose($handle);

						// Attachment
						$attachStr .= "--".$mimeBoundary.$eol;
						$attachStr .= "Content-type: ".$fileInfo['mime']."; name=\"".$fileName."\"".$eol;
						$attachStr .= "Content-Transfer-Encoding: base64".$eol;
						$attachStr .= "Content-Disposition: attachment; filename=\"".$fileName."\"".$eol.$eol; // !! This line needs TWO end of lines !! IMPORTANT !!
						$attachStr .= $fContents.$eol.$eol;
					}
				}
				$attachStr .= "--".$mimeBoundary.$eol;
				$attachStr .= "Content-type: ".$bodyContentType.$eol;
				$attachStr .= "Content-Transfer-Encoding: 8bit".$eol;
			}
			return $attachStr;
		}
	}
?>
