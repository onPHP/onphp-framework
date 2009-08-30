<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Flow
	**/
	final class CarefulDatabaseRunner implements CarefulCommand
	{
		private $command	= null;
		private $db			= null;
		
		private $running = false;
		
		final public function __construct(EditorCommand $command)
		{
			$this->command = $command;
		}
		
		/**
		 * @throws BaseException
		 * @return ModelAndView
		**/
		public function run(Prototyped $subject, Form $form, HttpRequest $request)
		{
			Assert::isFalse($this->running, 'command already running');
			Assert::isTrue($subject instanceof DAOConnected);
			
			$this->db = DBPool::getByDao($subject->dao());
			
			$this->db->begin();
			
			try {
				$mav = $this->command->run($subject, $form, $request);
				
				$this->running = true;
				
				return $mav;
			} catch (BaseException $e) {
				$this->db->rollback();
				
				throw $e;
			}
			
			Assert::isUnreachable();
		}
		
		/**
		 * @return CarefulDatabaseRunner
		**/
		public function commit()
		{
			if ($this->running) {
				$this->db->commit();
				$this->running = false;
			}
			
			return $this;
		}
		
		/**
		 * @return CarefulDatabaseRunner
		**/
		public function rollback()
		{
			if ($this->running) {
				try {
					$this->db->rollback();
				} catch (DatabaseException $e) {
					// keep silence
				}
				
				$this->running = false;
			}
			
			return $this;
		}
		
		public function __destruct()
		{
			if ($this->running) {
				try {
					$this->db->rollback();
				} catch (BaseException $e) {
					// fear of fatal error's
				}
			}
		}
	}
?>