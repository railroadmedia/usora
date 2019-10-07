<?php

namespace Railroad\Usora\Entities\Traits;

trait SupportNoteProperties
{
    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $supportNote;

    /**
     * @return string
     */
    public function getSupportNote()
    {
        return $this->supportNote;
    }

    /**
     * @param string $supportNote
     */
    public function setSupportNote($supportNote)
    {
        $this->supportNote = $supportNote;
    }
}