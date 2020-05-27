<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

use Illuminate\Support\Carbon;

/**
 * Base model
 *
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 *
 * @method static static find(int number)
 * @method static static create(array data)
 * @method static static where(...$data)
 * @method static static whereIn(string $column, array $data)
 * @method static static whereDate(string $column, ...$data)
 * @method static static orderBy(string $column, string $mode)
 * @method static where(...$data)
 * @method static whereIn(string $column, array $data)
 * @method static whereDate(string $column, ...$data)
 * @method static orderBy(string $column, string $mode)
 * @method static limit(int $rows)
 * @method static first()
 * @method static latest()
 * @method static with(...$data)
 * @method Collection|static[] all()
 * @method Collection|static[] get()
 */
class Base extends Model
{
    use SoftDeletes,RevisionableTrait;
    /**
     * We are using code fields for identifications
     *
     * @var string
     */
    protected $codeName;
    /**
     * Returning the code name
     *
     * @return string
     */
    public function getCodeName(){
        return $this->codeName;
    }
    /**
     * Returning the code
     *
     * @return mixed
     */
    public function getCode(){
        $codeName = $this->getCodeName();

        return $this->{$codeName};
    }

}
