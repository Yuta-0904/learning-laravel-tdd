<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VacancyLevel extends Model
{
    use HasFactory;
    private $remainingCount;

    public function __construct(int $remainingCount)
    {
        $this->remainingCount = $remainingCount;
    }

    public function mark(): string
    {
        $marks = ['empty' => '×', 'few' => '△', 'enough' => '◎'];
        $slug = $this->slug();
        assert(isset($marks[$slug]), new \DomainException('invalid slug value.'));

        return $marks[$slug];
    }

    public function slug():string
    {
        switch(true)
        {
            case $this->remainingCount === 0:
                $judge = 'empty';
                break;
            case $this->remainingCount < 5:
                $judge = 'few';
                break;
            default:
                $judge = 'enough';
                break;
        }
        return $judge;
    }

    public function __toString()
    {
        return $this->mark();
    }
}
