<?php

namespace Modules\Core\Models\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait TreeStructure
{
    protected static function bootTreeStructure()
    {
        static::creating(function ($data) {
            static::creatingTreeStructure($data);
        });

        static::updating(function ($data) {
            static::updatingTreeStructure($data);
        });

        static::deleted(function ($data) {
            static::deleteNode($data);
        });

        static::saved(function ($data) {
            $clientField = static::defineClientField();

            // set id ke client
            if ($clientField && empty($data->$clientField)) {
                $data->$clientField = $data->id;
                $data->save();
            }
        });
    }

    protected static function creatingTreeStructure($data)
    {
        $clientField = static::defineClientField();
        $parentField = static::defineParentField();
        $depthField = static::defineDepthField();

        // cek parent
        $depth = 0;
        if (!empty($data->$parentField)) {
            $parent = static::find($data->$parentField);

            $clientId = $parent->$clientField ?? null;
            $depth = isset($parent->$depthField) ? ($parent->$depthField + 1) : null;
            $infoLeft = $parent->info_right;

            // buka tree
            static::insertNode($clientId, $infoLeft, $infoLeft + 1);
        } else if ($clientField && empty($data->$clientField)) {
            $infoLeft = 1;
        } else {
            $infoLeft = static::selectRaw('max(info_right) as info_right')
                ->when($clientField, function ($query) use ($clientField, $data) {
                    $query->where($clientField, $data->$clientField);
                })
                ->value('info_right') + 1;
        }

        // set info_left info_right
        $data->info_left = $infoLeft;
        $data->info_right = $infoLeft + 1;

        // set depth
        if ($depthField) {
            $data->$depthField = $depth;
        }

        // set client
        if ($clientField && !empty($clientId)) {
            $data->$clientField = $clientId;
        }
    }

    protected static function updatingTreeStructure($data)
    {
        $clientField = static::defineClientField();
        $parentField = static::defineParentField();
        $depthField = static::defineDepthField();

        // cek pergantian parent
        $old = static::find($data->id);
        if (!empty($data->$parentField)) {
            $newParent = static::find($data->$parentField);
        }

        // parent baru tidak boleh child dari parent lama
        if (
            !empty($newParent) && $old && (empty($newParent->$clientField) || $newParent->$clientField == $old->$clientField)
            && $newParent->info_left >= $old->info_left && $newParent->info_right <= $old->info_right
        ) {
            $key = Str::snake(basename(static::class));

            throw ValidationException::withMessages([
                $parentField => __('validation.in', ['attribute' => __($key . '.' . $parentField)])
            ]);
        }

        $depth = 0;
        if (!empty($newParent)) {
            $clientId = $newParent->$clientField ?? null;
            $depth = isset($newParent->$depthField) ? ($newParent->$depthField + 1) : null;
            $infoLeft = $newParent->info_right;
        } elseif ($clientField && empty($data->$clientField)) {
            $infoLeft = 1;
        }

        // set left right jika perlu
        static::updatingTreeStructurePosition($old, $data, $clientId ?? $data->$clientField ?? null, $infoLeft ?? null);

        // set depth
        if ($depthField) {
            $data->$depthField = $depth;
        }

        // set client
        if ($clientField && !empty($clientId)) {
            $data->$clientField = $clientId;
        }
    }

    protected static function updatingTreeStructurePosition($old, $data, $clientId, $infoLeft)
    {
        $clientField = static::defineClientField();
        $parentField = static::defineParentField();

        $updatePos = false;
        $noParent = (empty($data->$parentField) && empty($old->$parentField));

        if (
            ($noParent && $clientField && !empty($old->$clientField) && $data->$clientField != $old->$clientField)
            || (empty($noParent) && $data->$parentField != $old->$parentField)
        ) {
            $updatePos = true;
        }

        if (empty($updatePos)) {
            return false;
        }

        $clientField = static::defineClientField();

        // lepaskan item dari tree dengan menegatifkan
        static::when($clientField, function ($query) use ($clientField, $old) {
            $query->where($clientField, $old->$clientField);
        })
            ->where('info_left', '>=', $old->info_left)
            ->where('info_right', '<=', $old->info_right)
            ->update([
                'info_left' => DB::raw('info_left * -1'),
                'info_right' => DB::raw('info_right * -1')
            ]);

        // tutup tree
        static::deleteNode($old);

        // buka tree
        if (empty($infoLeft)) {
            $infoLeft = static::selectRaw('max(info_right) as info_right')
                ->when($clientField, function ($query) use ($clientField, $old) {
                    $query->where($clientField, $old->$clientField);
                })
                ->where('info_left', '>', 0)
                ->value('info_right') + 1;
        } elseif ($infoLeft > $old->info_right) {
            $infoLeft -= 2;
        }

        static::insertNode($clientId, $infoLeft, $infoLeft + ($old->info_right - $old->info_left));

        // positifkan item
        $gap = $infoLeft - $old->info_left;

        static::when($clientField, function ($query) use ($clientField, $old) {
            $query->where($clientField, $old->$clientField);
        })
            ->where('info_left', '<', 0)
            ->update([
                'info_left' => DB::raw('(info_left * -1) + ' . $gap),
                'info_right' => DB::raw('(info_right * -1) + ' . $gap)
            ]);

        // set info_left info_right
        $data->info_left = $infoLeft;
        $data->info_right = $infoLeft + ($old->info_right - $old->info_left);
    }

    protected static function insertNode($clientId, $infoLeft, $infoRight)
    {
        $clientField = static::defineClientField();
        $length = $infoRight - $infoLeft + 1;

        static::when($clientField, function ($query) use ($clientField, $clientId) {
            $query->where($clientField, $clientId);
        })
            ->where('info_left', '>=', $infoLeft)
            ->update([
                'info_left' => DB::raw('info_left + ' . $length)
            ]);

        static::when($clientField, function ($query) use ($clientField, $clientId) {
            $query->where($clientField, $clientId);
        })
            ->where('info_right', '>=', $infoLeft)
            ->update([
                'info_right' => DB::raw('info_right + ' . $length)
            ]);
    }

    protected static function deleteNode($node)
    {
        $clientField = static::defineClientField();
        $length = $node->info_right - $node->info_left + 1;

        static::when($clientField, function ($query) use ($clientField, $node) {
            $query->where($clientField, $node->$clientField);
        })
            ->where('info_left', '>', $node->info_left)
            ->update([
                'info_left' => DB::raw('info_left - ' . $length)
            ]);

        static::when($clientField, function ($query) use ($clientField, $node) {
            $query->where($clientField, $node->$clientField);
        })
            ->where('info_right', '>', $node->info_left)
            ->update([
                'info_right' => DB::raw('info_right - ' . $length)
            ]);
    }

    protected static function defineParentField()
    {
        return 'parent_id';
    }

    protected static function defineClientField()
    {
        // jika diisi, nilainya tidak boleh null (ketika null harus diupdate)
        return null;
    }

    protected static function defineDepthField()
    {
        // opsional, tapi biasa digunakan
        return null;
    }
}
