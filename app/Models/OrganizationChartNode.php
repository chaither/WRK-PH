<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationChartNode extends Model
{
    protected $fillable = ['name', 'position', 'parent_id', 'image_path'];

    public function children()
    {
        return $this->hasMany(OrganizationChartNode::class, 'parent_id');
    }
}
