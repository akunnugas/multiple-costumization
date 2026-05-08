<?php

namespace Modules\Core\Models\SiakadV1;

class SettingSimV1Model extends SiakadV1Model
{
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'keysetting';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'master.settingsim';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        "keysetting",
        "namasetting",
        "valuesetting",
        "tipeinput",
        "t_updateuser",
        "t_updatetime",
        "t_updateip",
        "t_updateact",
        "softdelete",
        "idjenissetting",
        "optionsetting",
        "isedit",
        "keterangan",
        "idsatker",
        "idsetting",
        "t_inserttime",
    ];
}
