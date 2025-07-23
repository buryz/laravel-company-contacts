<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contact extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'position',
        'created_by',
    ];

    /**
     * Get the user who created this contact.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The tags that belong to the contact.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withPivot('created_at');
    }

    /**
     * Get the contact's full name.
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Generate vCard format for the contact.
     */
    public function toVCard(): string
    {
        $vcard = "BEGIN:VCARD\r\n";
        $vcard .= "VERSION:3.0\r\n";
        $vcard .= "FN:{$this->full_name}\r\n";
        $vcard .= "N:{$this->last_name};{$this->first_name};;;\r\n";
        $vcard .= "EMAIL:{$this->email}\r\n";
        
        if ($this->phone) {
            $vcard .= "TEL:{$this->phone}\r\n";
        }
        
        $vcard .= "ORG:{$this->company}\r\n";
        $vcard .= "TITLE:{$this->position}\r\n";
        $vcard .= "END:VCARD\r\n";
        
        return $vcard;
    }
}
