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
class CommandChain implements EditorCommand
{
    private $chain = array();

    /**
     * @return CommandChain
     **/
    public static function create()
    {
        return new self;
    }

    /**
     * @return CommandChain
     **/
    public function add(EditorCommand $command)
    {
        $this->chain[] = $command;

        return $this;
    }

    /**
     * @throws BaseException
     * @return ModelAndView
     **/
    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        Assert::isTrue(
            ($size = count($this->chain)) > 0,

            'command chain is empty'
        );

        for ($i = 0; $i < $size; ++$i) {
            $command = &$this->chain[$i];

            try {
                $mav = $command->run($subject, $form, $request);

                if ($mav->getView() == EditorController::COMMAND_FAILED) {
                    $this->rollback($i);
                    return $mav;
                }
            } catch (BaseException $e) {
                $this->rollback($i);
                throw $e;
            }
        }

        return $mav;
    }

    /**
     * @return CommandChain
     **/
    protected function rollback($position)
    {
        for ($i = $position; $i > -1; --$i) {
            if ($this->chain[$i] instanceof CarefulCommand) {
                try {
                    $this->chain[$i]->rollback();
                } catch (BaseException $e) {
                    // silently ignore, since no one
                    // allowed to interrupt this proccess
                }
            }
        }

        return $this;
    }

    /**
     * @return CommandChain
     **/
    protected function commit()
    {
        for ($size = count($this->chain), $i = 0; $i < $size; --$i) {
            if ($this->chain[$i] instanceof CarefulCommand)
                $this->chain[$i]->commit();
        }

        return $this;
    }
}