<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property string $id 
 * @property string $closedat 
 * @property string $transationat 
 * @property string $ticketcode 
 * @property float $price 
 * @property integer $quantity 
 */
class Negotiation extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'negotiations';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [];

    public function toArray(): array
    {
        return [
            'closedat' => $this->closedat,
            'transationat' => $this->transationat,
            'ticketcode' => $this->ticketcode,
            'price' => $this->price,
            'quantity' => $this->quantity,
        ];
    }
}
